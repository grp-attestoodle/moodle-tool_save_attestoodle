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
        $record = new \stdClass();
        $record->name = $training->name;
        $categ = $training->categoryid;

        if (isset($state->categoryid)) {
            $record->categoryid = $state->categoryid;
            $categ = $state->categoryid;
        } else {
            $record->categoryid = $training->categoryid;
        }

        $record->startdate = $training->startdate;
        $record->enddate = $training->enddate;
        $record->duration = $training->duration;
        $record->nbautolaunch = $training->nbautolaunch;
        $record->nextlaunch = $training->nextlaunch;
        $record->checklearner = $training->checklearner;

        $idtraining = $DB->insert_record('tool_attestoodle_training', $record);

        foreach ($milestones as $milestone) {
            $moduleid = $state->tabactivities[$milestone->moduleid];
            if ($moduleid != -1) {
                $record = new \stdClass();
                $record->creditedtime = $milestone->creditedtime;
                $record->moduleid = $moduleid;
                $record->trainingid = $idtraining;
                $record->timemodified = $milestone->timemodified;
                $courseid = $state->courses[$milestone->course];
                $record->course = $courseid;
                $record->name = $milestone->name;
                $DB->insert_record('tool_attestoodle_milestone', $record);
            }
        }

        // Template exist ?
        if ($state->templateid != -1) {
            $record = new \stdClass();
            $record->trainingid = $idtraining;
            $record->templateid = $state->templateid;
            $idtemplate = $state->templateid;
            $record->grpcriteria1 = $templaterel->grpcriteria1;
            $record->grpcriteria2 = $templaterel->grpcriteria2;
            $DB->insert_record('tool_attestoodle_train_style', $record);
            // Replace the model ?
            if (isset($datas->settemplate)) {
                $req = "delete from {tool_attestoodle_tpl_detail} where templateid = :templateid and type != 'background'";
                $DB->execute($req, array('templateid' => $state->templateid));
                foreach ($templatedetail as $detail) {
                    if ($detail->type != 'background') {
                        $record = new \stdClass();
                        $record->templateid = $state->templateid;
                        $record->type = $detail->type;
                        $record->data = $detail->data;
                        $DB->insert_record('tool_attestoodle_tpl_detail', $record);
                    }
                }
            }
        } else {
            // Create template.
            $record = new \stdClass();
            $record->name = $template->name;
            $record->timecreated = \time();
            $record->userid = $USER->id;
            $record->timemodified = \time();
            $idtemplate = $DB->insert_record('tool_attestoodle_template', $record);
            // Create template's details, except the background image.
            foreach ($templatedetail as $detail) {
                if ($detail->type != 'background') {
                    $record = new \stdClass();
                    $record->templateid = $idtemplate;
                    $record->type = $detail->type;
                    $record->data = $detail->data;
                    $DB->insert_record('tool_attestoodle_tpl_detail', $record);
                }
            }
            $record = new \stdClass();
            $record->trainingid = $idtraining;
            $record->templateid = $idtemplate;
            $record->grpcriteria1 = $templaterel->grpcriteria1;
            $record->grpcriteria2 = $templaterel->grpcriteria2;
            $DB->insert_record('tool_attestoodle_train_style', $record);
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
