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
 * Uploading the backup file of a Attestoodle training course.
 *
 * @package    tool_save_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Form for uploading the backup file in order to choose a restoration or cloning.
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class loadform extends moodleform {
    /**
     * Method automatically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    protected function definition() {
        $mform    = $this->_form;

        $mform->addElement('filemanager', 'fichier', get_string('putfile', 'tool_save_attestoodle'),
            null,
            array(
                'subdirs' => 0,
                'maxbytes' => 10485760,
                'areamaxbytes' => 10485760,
                'maxfiles' => 1,
                'accepted_types' => array('.txt', '.json'),
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL));

        $actionbuttongroup1 = array();
        $actionbuttongroup1[] =& $mform->createElement('submit', 'restore', get_string('restore', 'tool_save_attestoodle'),
                                                        array('class' => 'send-button'));
        $actionbuttongroup1[] =& $mform->createElement('submit', 'cancelbtn', get_string('cancel'),
                                                        array('class' => 'send-button'));
        $mform->addGroup($actionbuttongroup1, 'actionbuttongroup1', '', ' ', false);

        $mform->addElement('text', 'suffix', get_string('suffix', 'tool_save_attestoodle'), array("size" => 10));
        $mform->setType('suffix', PARAM_RAW);

        $actionbuttongroup2 = array();
        $actionbuttongroup2[] =& $mform->createElement('submit', 'cloner', get_string('clone', 'tool_save_attestoodle'),
                                                        array('class' => 'send-button'));
        $actionbuttongroup2[] =& $mform->createElement('submit', 'cancel2', get_string('cancel'));
        $mform->addGroup($actionbuttongroup2, 'actionbuttongroup2', '', ' ', false);
    }
}
