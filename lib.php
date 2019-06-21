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
 * Useful global functions for Save Attestoodle.
 *
 * @package    tool_save_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Generates the backup file and stores it in the moodle files.
 *
 * @param int $trainingid ID of the training to store.
 */
function store_training($trainingid) {
    global $USER;
    $fs = get_file_storage();
    $saveser = training_to_json($trainingid);
    $obj = json_decode($saveser);

    if (isset($saveser->error)) {
        return $saveser;
    }
    $usercontext = \context_user::instance($USER->id);
    $filename = "formation_" . $obj->training->id . ".json";
    $fileinfos = array(
                'contextid' => $usercontext->id,
                'component' => 'tool_save_attestoodle',
                'filearea' => 'savetraining',
                'filepath' => '/',
                'itemid' => 0,
                'filename' => $filename );

    $file = $fs->get_file(
                $fileinfos['contextid'],
                $fileinfos['component'],
                $fileinfos['filearea'],
                $fileinfos['itemid'],
                $fileinfos['filepath'],
                $fileinfos['filename']
            );
    if ($file) {
        $file->delete();
    }
    $file = $fs->create_file_from_string($fileinfos, $saveser);
}

/**
 * Forces the download of the backup file from the formation ID.
 *
 * @param int $id ID of the training to be download.
 */
function get_file($id) {
    global $USER, $CFG;

    $fs = get_file_storage();
    $usercontext = \context_user::instance($USER->id);
    $filename = "formation_" . $id . ".json";
    $fileinfos = array(
                'contextid' => $usercontext->id,
                'component' => 'tool_save_attestoodle',
                'filearea' => 'savetraining',
                'filepath' => '/',
                'itemid' => 0,
                'filename' => $filename );

    $file = $fs->get_file(
                $fileinfos['contextid'],
                $fileinfos['component'],
                $fileinfos['filearea'],
                $fileinfos['itemid'],
                $fileinfos['filepath'],
                $fileinfos['filename']
            );
    if (!$file) {
        return;
    }

    \make_temp_directory('attestoodleexport');
    $tempfilename = $CFG->tempdir .'/attestoodleexport/'. $filename;
    $file->copy_content_to($tempfilename);
    @header("Content-type: text/xml; charset=UTF-8");
    send_temp_file($tempfilename, $filename, false);
}

/**
 * Retrieves all the elements of the training definition whose technical identifier is received as a parameter.
 * All these elements are placed in a structure that will be returned in json format.
 *
 * @param int $trainingid ID of the training to be transformed into json.
 * @return training in json format.
 */
function training_to_json($trainingid) {
    global $DB;
    $ret = new stdClass();
    $ret->version = '2019061667';
    $training = $DB->get_record('tool_attestoodle_training', array('id' => $trainingid));
    if (!isset($training->id)) {
        $ret->error = "training not exist !";
        return json_encode($ret);
    }
    $ret->training = $training;

    $milestones = $DB->get_records('tool_attestoodle_milestone', array('trainingid' => $trainingid));
    $ret->milestones = add_relative_pos($milestones);

    $templatetraining = $DB->get_record('tool_attestoodle_train_style', array('trainingid' => $trainingid));
    $ret->relationtemplate = $templatetraining;

    $template = $DB->get_record('tool_attestoodle_template', array('id' => $templatetraining->templateid));
    $ret->template = $template;

    $templatedetails = $DB->get_records('tool_attestoodle_tpl_detail', array('templateid' => $templatetraining->templateid));
    $tabdetails = array();
    foreach ($templatedetails as $templatedetail) {
        $tabdetails[] = $templatedetail;
    }
    $ret->templatedetails = $tabdetails;

    $learners = $DB->get_records('tool_attestoodle_learner', array('trainingid' => $trainingid));
    $ret->learners = add_natural_learnerid($learners);

    // We record the setting for each student, but we don't take their personal model !
    $settingusers = $DB->get_records('tool_attestoodle_user_style', array('trainingid' => $trainingid));
    $templateusers = array();
    foreach ($settingusers as $setting) {
        $templateusers[] = $setting;
    }
    $ret->templateusers = $templateusers;
    $ret->integrity = '';
    $txt = json_encode($ret);
    $sha = sha1($txt);
    $ret->integrity = $sha;
    return json_encode($ret);
}

/**
 * Adds the email address to learners as a natural identifier.
 *
 * @param array $learners the learners' table to be completed with their email address.
 */
function add_natural_learnerid($learners) {
    global $DB;
    $ret = array();
    foreach ($learners as $learner) {
        $user = $DB->get_record('user', array('id' => $learner->userid));
        $learner->email = $user->email;
        $ret[] = $learner;
    }
    return $ret;
}

/**
 * Adds the relative position of each activity within their course.
 * So that it can be transposed to a copy of the course.
 * Each course is reinforced by its court name.
 *
 * @param array $milestones the milestone table to be identified with their relative position within their course.
 */
function add_relative_pos($milestones) {
    global $DB;
    $ret = array();
    foreach ($milestones as $milestone) {
        $req = "select * from {course_sections} where id in (select section from {course_modules} where id = ?)";
        $section = $DB->get_record_sql($req, array($milestone->moduleid));
        $milestone->section = $section->section;
        $milestone->posr = search($milestone->moduleid, $section->sequence);
        $cours = $DB->get_record('course', array('id' => $milestone->course));
        $milestone->course_shortname = $cours->shortname;
        $ret[] = $milestone;
    }
    return $ret;
}

/**
 * Provides the position of $num in $serie, knowing that $serie contains a comma-separated number series.
 *
 * @param int $num the value sought.
 * @param string $serie the value series separated by comma.
 * @return the position (from 0) of $num in series or -1 if num is not present.
 */
function search($num, $serie) {
    $valsearch = (string) $num;
    $elements = explode(",", $serie);
    $i = 0;
    while (strcmp($elements[$i], $valsearch) != 0) {
        $i++;
        if ($i > count($elements)) {
            $i = -1;
            break;
        }
    }
    return $i;
}

/**
 * Function automagically called by moodle to retrieve a file on the server that
 * the plug-in can interact with.
 * @param object $course course allow to acces filemanager
 * @param object $cm course module allow to access filemanager
 * @param object $context where we can access filemanager
 * @param object $filearea where filemanager stock file.
 * @param object $args arguments of path
 * @param bool $forcedownload if force donwload or not.
 * @param array $options optional parameter for form's component.
 * @link See doc at https://docs.moodle.org/dev/File_API#Serving_files_to_users
 */
function tool_save_attestoodle_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($course && $cm) {
        $cm = $cm;
        $course = $course;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'savetraining' && $filearea !== 'fichier') {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = 0;

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // If $args is empty => the path is '/'.
    } else {
        $filepath = '/'.implode('/', $args).'/'; // Var $args contains elements of the filepath.
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'tool_save_attestoodle', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // Force non image formats to be downloaded.
    if ($file->is_valid_image()) {
        $forcedownload = false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 1, 0, $forcedownload, $options);
}

/**
 * Generates the html code corresponding to the save training button.
 *
 * @param int $trainingid ID of training to save.
 * @return string the html code of the save button.
 */
function btn_save($trainingid) {
    $url = new moodle_url('/admin/tool/save_attestoodle/save.php', ['trainingid' => $trainingid]);
    $ret = '<a href="' . $url . '" class= "btn-create">' . get_string('save', 'tool_save_attestoodle') . '</a>';
    return $ret;
}

/**
 * Generates the link code to the restoration form.
 *
 * @return string the link code to the restoration form.
 */
function lnk_load() {
    global $CFG;
    $url = new \moodle_url("$CFG->wwwroot/$CFG->admin/tool/save_attestoodle/load.php");
    $content = \html_writer::link($url, get_string('load', 'tool_save_attestoodle'), array());
    return $content;
}

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
