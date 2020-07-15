<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Analyzes the restoration file.
 *
 * @package    tool_save_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../config.php');

// Libraries imports.
require_once(dirname(__FILE__) .'/lib.php');
require_once(dirname(__FILE__) .'/load_analyse_form.php');

$filename = required_param('filename', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
global $USER;
$thisurl = new moodle_url(dirname(__FILE__) . '/restored.php', ['filename' => $filename] );
$url = new \moodle_url('/admin/tool/save_attestoodle/restored.php', ['filename' => $filename]);
$PAGE->set_url($thisurl);

$title = get_string('titlerestorevalide', 'tool_save_attestoodle');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$fs = get_file_storage();
$fileinfo = array(
    'component' => 'tool_save_attestoodle',
    'filearea' => 'savetraining',
    'itemid' => 12,
    'contextid' => $context->id,
    'filepath' => '/',
    'filename' => $filename);

// Get file.
$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

// Display page.
if ($file) {
    $content = $file->get_content();
    $obj = json_decode($content);

    $state = new stdClass();
    $training = $obj->training;
    checktraining($training, $state);

    $milestones = $obj->milestones;
    checkcourses($milestones, $state);
    checkactivities($milestones, $state);

    $template = $obj->template;
    checktemplate($template, $state);

    $templatedetail = $obj->templatedetails;
    $templaterel = $obj->relationtemplate;

    $learners = $obj->learners;
    checklearner($learners, $state);
    $templateusers = $obj->templateusers;

    $mform = new load_analyse_form($url,
                                    array(
                                            'training' => $training,
                                            'milestones' => $milestones,
                                            'template' => $template,
                                            'templatedetail' => $templatedetail,
                                            'templaterel' => $templaterel,
                                            'learners' => $learners,
                                            'templateusers' => $templateusers,
                                            'state' => $state
                                          ) );

    if ($mform->is_cancelled()) {
        $redirecturl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);
        $message = get_string('restorecancel', 'tool_save_attestoodle');
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
        return;
    }
    if ($mform->get_data()) {
        global $DB;
        // Process validated data.
        $datas = $mform->get_data();

        // Create training.
        $dataobject = new \stdClass();
        $dataobject->name = $training->name;
        $categ = $training->categoryid;
        if (isset($state->categoryid)) {
            $dataobject->categoryid = $state->categoryid;
            $categ = $state->categoryid;
        } else {
            $dataobject->categoryid = $training->categoryid;
        }
        $dataobject->startdate = $training->startdate;
        $dataobject->enddate = $training->enddate;
        $dataobject->duration = $training->duration;
        $dataobject->nbautolaunch = $training->nbautolaunch;
        $dataobject->nextlaunch = $training->nextlaunch;
        $dataobject->checklearner = $training->checklearner;

        if ($state->trainingexist) {
            $rec = $DB->get_record('tool_attestoodle_training', array('name' => $training->name));
            $dataobject->id = $rec->id;
            $idtraining = $rec->id;
            $DB->update_record('tool_attestoodle_training', $dataobject);
            // Delete all milestones.
            $DB->delete_records('tool_attestoodle_milestone', array('trainingid' => $idtraining));
            // Delete all learners.
            $DB->delete_records('tool_attestoodle_learner', array('trainingid' => $idtraining));
            $DB->delete_records('tool_attestoodle_user_style', array('trainingid' => $idtraining));
            //Delete model
            $DB->delete_record('tool_attestoodle_train_style', array('trainingid' => $idtraining));
        } else {
            $idtraining = $DB->insert_record('tool_attestoodle_training', $dataobject);
        }

        // Milestones.
        foreach ($milestones as $milestone) {
            $cmid = $state->tabactivities[$milestone->moduleid];
            if ($cmid != -1) {
                $dataobject = new \stdClass();
                $dataobject->creditedtime = $milestone->creditedtime;
                $dataobject->moduleid = $cmid;
                $dataobject->trainingid = $idtraining;
                $dataobject->timemodified = $milestone->timemodified;
                $courseid = $state->courses[$milestone->course];
                $dataobject->course = $courseid;
                $dataobject->name = $milestone->name;
                $DB->insert_record('tool_attestoodle_milestone', $dataobject);
            }
        }

        // Template exist ?
        if ($state->templateid != -1) {
            $dataobject = new \stdClass();
            $dataobject->trainingid = $idtraining;
            $dataobject->templateid = $state->templateid;
            $idtemplate = $state->templateid;
            $dataobject->grpcriteria1 = $templaterel->grpcriteria1;
            $dataobject->grpcriteria2 = $templaterel->grpcriteria2;
            $DB->insert_record('tool_attestoodle_train_style', $dataobject);
            // Replace the model ?
            if (isset($datas->settemplate)) {
                $req = "delete from {tool_attestoodle_tpl_detail} where templateid = :templateid and type != 'background'";
                $DB->execute($req, array('templateid' => $state->templateid));
                foreach ($templatedetail as $detail) {
                    if ($detail->type != 'background') {
                        $dataobject = new \stdClass();
                        $dataobject->templateid = $state->templateid;
                        $dataobject->type = $detail->type;
                        $dataobject->data = $detail->data;
                        $DB->insert_record('tool_attestoodle_tpl_detail', $dataobject);
                    }
                }
            }
        } else {
            // Create template.
            $dataobject = new \stdClass();
            $dataobject->name = $template->name;
            $dataobject->timecreated = \time();
            $dataobject->userid = $USER->id;
            $dataobject->timemodified = \time();
            $idtemplate = $DB->insert_record('tool_attestoodle_template', $dataobject);
            // Create template's details, except the background image.
            foreach ($templatedetail as $detail) {
                if ($detail->type != 'background') {
                    $dataobject = new \stdClass();
                    $dataobject->templateid = $idtemplate;
                    $dataobject->type = $detail->type;
                    $dataobject->data = $detail->data;
                    $DB->insert_record('tool_attestoodle_tpl_detail', $dataobject);
                }
            }
            $dataobject = new \stdClass();
            $dataobject->trainingid = $idtraining;
            $dataobject->templateid = $idtemplate;
            $dataobject->grpcriteria1 = $templaterel->grpcriteria1;
            $dataobject->grpcriteria2 = $templaterel->grpcriteria2;
            $DB->insert_record('tool_attestoodle_train_style', $dataobject);
        }
        // Learners.
        if ($state->learnerok > 0 && isset($datas->addlearner)) {
            foreach ($learners as $learner) {
                $newid = $state->corrlearner[$learner->userid];
                if ($newid != -1) {
                    $dataobject = new \stdClass();
                    $dataobject->userid = $newid;
                    $dataobject->trainingid = $idtraining;
                    if (isset($state->categoryid)) {
                        $dataobject->categoryid = $state->categoryid;
                    } else {
                        $dataobject->categoryid = $training->categoryid;
                    }
                    $dataobject->selected = $learner->selected;
                    $dataobject->predelete = $learner->predelete;
                    $dataobject->resultcriteria = $learner->resultcriteria;
                    $dataobject->email = $learner->email;
                    $DB->insert_record('tool_attestoodle_learner', $dataobject);
                }
            }

            foreach ($templateusers as $templateuser) {
                $newid = $state->corrlearner[$templateuser->userid];
                if ($templateuser->enablecertificate == 0 && $newid != -1) {
                    $dataobject = new \stdClass();
                    $dataobject->userid = $newid;
                    $dataobject->trainingid = $idtraining;
                    $dataobject->templateid = $idtemplate;
                    $dataobject->grpcriteria1 = $templateuser->grpcriteria1;
                    $dataobject->grpcriteria2 = $templateuser->grpcriteria2;
                    $dataobject->enablecertificate = 0;
                    $DB->insert_record('tool_attestoodle_user_style', $dataobject);
                }
            }
        }
        $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php',
                ['typepage' => 'trainingmanagement', 'categoryid' => $categ, 'trainingid' => $idtraining]);

        $message = get_string('restore_success', 'tool_save_attestoodle');
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
        return;
    }
    echo $OUTPUT->header();
    $mform->display();
} else {
    $redirecturl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);
    $message = get_string('errreadfile', 'tool_save_attestoodle', $filename);
    redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
    return;
}
echo $OUTPUT->footer();

/**
 * Analyzes the courses with milestones.
 * Fill in the state with the correspondence table of the courses.
 * Indicates $state->errcourse with the number of courses in error and
 * $state->nbcourse with the total number of courses.
 * $state->courses[ancid] = newCourseID
 *
 * @param stdClass $milestones All milestones read.
 * @param stdClass $state state structure to be filled in.
 */
function checkcourses($milestones, &$state) {
    global $DB;

    $tabcourse = array();
    $nbcourse = 0;
    $nberrcourse = 0;
    foreach ($milestones as $milestone) {
        if (!isset($tabcourse[$milestone->course])) {
            $rec = $DB->get_record('course', array('shortname' => $milestone->course_shortname, 'enablecompletion' => 1));
            $nbcourse ++;
            if (isset($rec->id)) {
                $tabcourse[$milestone->course] = $rec->id;
            } else {
                $nberrcourse ++;
                $tabcourse[$milestone->course] = -1;
            }
        }
    }
    $state->courses = $tabcourse;
    $state->errcourse = $nberrcourse;
    $state->nbcourse = $nbcourse;
}

/**
 * Checks the learners' registrations.
 * Learners must be found through their email address and must not be 'deleted'.
 * If they are not enrolled in one of the courses, that is not a problem.
 * This method will inform $state with:
 *  errlearner = number of errors encountered
 *  corrlearner the correspondence table elv[ancid] = newid
 * newid is -1 when no match.
 * @param stdClass $learners All learners to be checked.
 * @param stdClass $state state structure to be filled in.
 */
function checklearner($learners, &$state) {
    global $DB;
    $newtab = array();
    $errlearner = 0;
    foreach ($learners as $learner) {
        $rec = $DB->get_record('user', array('deleted' => 0, 'email' => $learner->email));
        if (isset($rec->id)) {
            $newtab[$learner->userid] = $rec->id;
        } else {
            $errlearner++;
            $newtab[$learner->userid] = -1;
        }
    }
    $state->errlearner = $errlearner;
    $state->corrlearner = $newtab;
}
