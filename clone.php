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
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../config.php');

// Libraries imports.
require_once(dirname(__FILE__) .'/lib.php');
require_once(dirname(__FILE__) .'/clone_analyse_form.php');

$filename = required_param('filename', PARAM_RAW);
$suffix = required_param('suffix', PARAM_TEXT);

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
global $USER;
$thisurl = new moodle_url(dirname(__FILE__) . '/clone.php', ['filename' => $filename, 'suffix' => $suffix]);
$url = new \moodle_url('/admin/tool/save_attestoodle/clone.php', ['filename' => $filename, 'suffix' => $suffix]);
$PAGE->set_url($thisurl);

$title = get_string('titleclonevalide', 'tool_save_attestoodle');
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
    $training->name = $training->name . $suffix;
    checktraining($training, $state);
    $milestones = $obj->milestones;
    checkcourses($milestones, $state, $suffix);
    checkactivities($milestones, $state);

    $template = $obj->template;
    checktemplate($template, $state);

    $templatedetail = $obj->templatedetails;
    $templaterel = $obj->relationtemplate;

    $mform = new clone_analyse_form($url,
                                    array(
                                            'training' => $training,
                                            'template' => $template,
                                            'state' => $state
                                          ) );

    if ($mform->get_data()) {
        global $DB;
        // Process validated data.
        $datas = $mform->get_data();
        if ($datas->cancel || $state->trainingexist) {
            $redirecturl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);
            $message = get_string('clonecancel', 'tool_save_attestoodle');
            redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
            return;
        }
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

        $idtraining = $DB->insert_record('tool_attestoodle_training', $dataobject);

        foreach ($milestones as $milestone) {
            $moduleid = $state->tabactivities[$milestone->moduleid];
            if ($moduleid != -1) {
                $dataobject = new \stdClass();
                $dataobject->creditedtime = $milestone->creditedtime;
                $dataobject->moduleid = $moduleid;
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

        $redirecturl = new \moodle_url('/admin/tool/attestoodle/index.php',
                ['typepage' => 'trainingmanagement', 'categoryid' => $categ, 'trainingid' => $idtraining]);

        $message = get_string('restore_success', 'tool_save_attestoodle');
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
        return;
    }
    echo $OUTPUT->header();
    $mform->display();
    var_dump($state->erractivit);
} else {

    $redirecturl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);
    $message = get_string('errreadfile', 'tool_save_attestoodle', $filename);
    redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
    return;
}

echo $OUTPUT->footer();

/**
 * Analyzes the training, test if the formation already exists under its new name (trainingexist 0/1).
 * If the training already exists, the processing will be in error.
 * Test if the category exists otherwise offers the first category found.
 * (categoryid = id of first categ)
 *
 * @param int $training the training to be restored.
 * @param stdClass $state state structure to be filled in.
 */
function checktraining($training, &$state) {
    global $DB;
    $state->trainingexist = false;
    if ($DB->record_exists('tool_attestoodle_training', array('name' => $training->name))) {
        $state->trainingexist = true;
    }
    $category = $DB->get_record('course_categories', array('id' => $training->categoryid));
    if (isset($category->name)) {
        $state->category = $category->name;
    } else {
        $req = "select min(id) as id from {course_categories}";
        $categoryid = $DB->get_record_sql($req, array());
        $category = $DB->get_record('course_categories', array('id' => $categoryid->id));
        $state->category = $category->name;
        $state->categoryid = $categoryid->id;
    }
}

/**
 * Analyzes the courses with milestones.
 * Fill in the state with the correspondence table of the courses.
 * Indicates $state->errcourse with the number of courses in error and
 * $state->nbcourse with the total number of courses.
 * $state->courses[ancid] = newCourseID
 *
 * @param stdClass $milestones All milestones read.
 * @param stdClass $state state structure to be filled in.
 * @param string $suffix suffix to be added to the name of the cloned formation.
 */
function checkcourses($milestones, &$state, $suffix) {
    global $DB;
    $tabcourse = array();
    $nbcourse = 0;
    $nberrcourse = 0;
    foreach ($milestones as $milestone) {
        if (!isset($tabcourse[$milestone->course])) {
            $rec = $DB->get_record('course',
                array('shortname' => $milestone->course_shortname . $suffix, 'enablecompletion' => 1));
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
 * For each activity of the training courses, find the id of the activity corresponding to (section,posR).
 * Fill in $state->erractiv with the number of activities in error (no correspondence).
 * Fill in the activity mapping table $state->tabactivities[oldID] = newID.
 *
 * @param stdClass $milestones All the milestones to be tested.
 * @param stdClass $state state structure to be filled in.
 */
function checkactivities($milestones, &$state) {
    global $DB;
    $newtab = array();
    $tabcourse = $state->courses;
    $nberractiv = 0;
    foreach ($milestones as $milestone) {
        $idcourse = $tabcourse[$milestone->course];
        $ok = false;
        if ($idcourse != -1) {
            $rec = $DB->get_record('course_sections', array('course' => $idcourse, 'section' => $milestone->section));
            if (isset($rec->sequence)) {
                $elements = explode(",", $rec->sequence);
                if (isset($elements[$milestone->posr]) && !empty($elements[$milestone->posr])) {
                    $activ = $DB->get_record('course_modules', array('id' => $elements[$milestone->posr]));
                    if ($activ->completion > 0 && $activ->deletioninprogress == 0) {
                        $newtab[$milestone->moduleid] = $elements[$milestone->posr];
                        $ok = true;
                    } else {
                        $state->erractivit = $activ;
                    }
                }
            }
        }
        if (!$ok) {
            $newtab[$milestone->moduleid] = -1;
            $nberractiv ++;
        }
    }
    $state->erractiv = $nberractiv;
    $state->tabactivities = $newtab;
}

/**
 * Test if the attestation model exists (based on its name) and establish the
 * correspondence of the identifier $state->templateid = newID or = -1.
 * @param stdClass $template Model whose correspondence is being sought.
 * @param stdClass $state state structure to be filled in.
 */
function checktemplate($template, &$state) {
    global $DB;
    $templatedb = $DB->get_record('tool_attestoodle_template', array('name' => $template->name));
    if (isset($templatedb->id)) {
        $state->templateid = $templatedb->id;
    } else {
        $state->templateid = -1;
    }
}