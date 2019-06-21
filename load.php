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
 * Takes the uploaded backup file and restores it.
 *
 * @package    tool_save_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../config.php');

// Libraries imports.
require_once(dirname(__FILE__) .'/lib.php');
require_once(dirname(__FILE__) .'/loadform.php');

$context = context_system::instance();

$PAGE->set_context($context);
require_login();
global $USER;
$PAGE->set_url(new moodle_url(dirname(__FILE__) . '/load.php', [] ));

$title = get_string('titlerestore', 'tool_save_attestoodle');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$mform = new loadform();
$filename = "none";
if ($fromform = $mform->get_data()) {
    if ($fromform->cancelbtn || $fromform->cancel2) {
        $redirecturl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);
        $message = get_string('restorecancel', 'tool_save_attestoodle');
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_INFO);
        return;
    }
    if ($fromform->fichier) {
        $idtemplate = 12;
        file_save_draft_area_files($fromform->fichier, $context->id, 'tool_save_attestoodle', 'savetraining',
            $idtemplate,
            array('subdirs' => 0, 'maxbytes' => 10485760, 'maxfiles' => 1));

        // Get and save file name.
        $fs = get_file_storage();
        $arrayfile = $fs->get_directory_files($context->id, 'tool_save_attestoodle', 'savetraining',
                      $idtemplate, '/');
        $thefile = reset($arrayfile);
        if ($thefile !== false) {
            $filename = $thefile->get_filename();
            $str = $thefile->get_content();
            $obj = json_decode($str);
            $stop = false;
            if ($obj->version != '2019061667') {
                \core\notification::error(get_string('error_version', 'tool_save_attestoodle'));
                $stop = true;
            }

            if (!$stop) {
                $sha = $obj->integrity;
                $obj->integrity = '';
                $txt = json_encode($obj);
                if (sha1($txt) != $sha) {
                    $stop = true;
                    \core\notification::error(get_string('error_integrity', 'tool_save_attestoodle'));
                }
            }

            if (!$stop && $fromform->restore) {
                $redirecturl = new \moodle_url('/admin/tool/save_attestoodle/restored.php',
                    array("filename" => $filename));
                redirect($redirecturl);
                return;
            }
            if (!$stop && $fromform->cloner && empty($fromform->suffix)) {
                $stop = true;
                \core\notification::error(get_string('error_suffix', 'tool_save_attestoodle'));
            }

            if (!$stop && $fromform->cloner) {
                $redirecturl = new \moodle_url('/admin/tool/save_attestoodle/clone.php',
                    array("filename" => $filename, "suffix" => $fromform->suffix));
                redirect($redirecturl);
                return;
            }
        } else {
            \core\notification::error(get_string('file_require', 'tool_save_attestoodle'));
        }
    }
}

// Display page.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();