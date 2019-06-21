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
 * Result of the analysis of the cloning to be done.
 *
 * @package    tool_save_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

define("ERREUR_START_SPAN", '<span style="color:#ff4136;">');
define("ERREUR_END_SPAN",   '</span>');

// Load repository lib, will load filelib and formslib !
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Form that presents the result of the data analysis and allows you to continue or not.
 *
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class clone_analyse_form extends moodleform {
    /**
     * Method automatically called when the form is instanciated. It defines
     * all the elements (inputs, titles, buttons, ...) in the form.
     */
    protected function definition() {
        $mform    = $this->_form;

        $training = $this->_customdata['training'];
        $template = $this->_customdata['template'];
        $state = $this->_customdata['state'];

        $mform->addElement('static', 'iddescription', get_string('trainingname', 'tool_save_attestoodle'), $training->name);
        if (isset($state->categoryid)) {
            $mform->addElement('advcheckbox', 'okchgcateg', get_string('categnotfound', 'tool_save_attestoodle'),
            get_string('checktraining', 'tool_save_attestoodle', $state->category));
        } else {
            $mform->addElement('static', 'idcateg', get_string('restorecateg2', 'tool_save_attestoodle'), $state->category);
        }

        $err = false;
        if ($state->trainingexist) {
            $err = true;
            $mform->addElement('static', 'idcateg', get_string('processerror', 'tool_save_attestoodle'),
                ERREUR_START_SPAN .
                get_string('errcloneexist', 'tool_save_attestoodle', $training->name) .
                ERREUR_END_SPAN);
        } else {
            $mform->addElement('static', 'idtraining',
                get_string('newtraining', 'tool_save_attestoodle'),
                get_string('tobecreate', 'tool_save_attestoodle'));
        }

        $state->nbcourseok = $state->nbcourse - $state->errcourse;
        if (!$err) {
            if ($state->errcourse > 0) {
                $err = true;
                $mform->addElement('static', 'idcateg', get_string('processerror', 'tool_save_attestoodle'),
                    ERREUR_START_SPAN .
                    get_string('courseerror', 'tool_save_attestoodle', $state) .
                    '<br>' . get_string('clonecoursebefore', 'tool_save_attestoodle') .
                    ERREUR_END_SPAN);
            } else {
                $msg = get_string('allcourseok', 'tool_save_attestoodle', $state);
                $mform->addElement('static', 'idcourse', get_string('course'), $msg);
            }
        }

        $state->totmilestone = count($state->tabactivities);
        $state->nbmilestoneok = count($state->tabactivities) - $state->erractiv;
        if (!$err) {
            if ($state->erractiv > 0) {
                $err = true;
                $mform->addElement('static', 'idactivities', get_string('processerror', 'tool_save_attestoodle'),
                    ERREUR_START_SPAN .
                    get_string('errmilestone', 'tool_save_attestoodle', $state->erractiv) .
                    ERREUR_END_SPAN);
            } else {
                $msg = get_string('allmilestoneok', 'tool_save_attestoodle', $state);
                $mform->addElement('static', 'idactivities', get_string('milestone', 'tool_save_attestoodle'), $msg);
                if ($state->templateid > 0) {
                    $mform->addElement('advcheckbox', 'settemplate',
                        get_string('template', 'tool_save_attestoodle'),
                        get_string('replacetemplate', 'tool_save_attestoodle', $template->name));
                }
            }
        }

        $actionbuttongroup = array();
        if (!$err) {
            $actionbuttongroup[] =& $mform->createElement('submit', 'save', get_string('savechanges'),
                                        array('class' => 'send-button'));
        }
        $actionbuttongroup[] =& $mform->createElement('submit', 'cancel', get_string('cancel'),
                                        array('class' => 'cancel-button'));
        $mform->addGroup($actionbuttongroup, 'actionbuttongroup', '', ' ', false);
    }
}