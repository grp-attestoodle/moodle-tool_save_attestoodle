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
 * Display the analysis of the restoration to be done.
 *
 * @package    tool_save_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Part of Moodle.
defined('MOODLE_INTERNAL') || die();

// Load repository lib, will load filelib and formslib !
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Form presenting the analysis of the restoration to be done, has for controller 'restored php'.
 *
 * @copyright  2019 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class load_analyse_form extends moodleform {
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
            $mform->addElement('static', 'idcateg', get_string('restorecateg', 'tool_save_attestoodle', $state->category));
        }

        if ($state->trainingexist) {
            $mform->addElement('advcheckbox', 'okreplacetraining', get_string('replacetraining', 'tool_save_attestoodle'),
                get_string('ticktovalid', 'tool_save_attestoodle'));
        } else {
            $mform->addElement('static', 'idtraining',
                get_string('newtraining', 'tool_save_attestoodle'),
                get_string('tobecreate', 'tool_save_attestoodle'));
        }

        $state->nbcourseok = $state->nbcourse - $state->errcourse;
        if ($state->errcourse > 0) {
            $mform->addElement('advcheckbox', 'continuewitherror',
                get_string('courseerror', 'tool_save_attestoodle', $state),
                get_string('ticktovalid', 'tool_save_attestoodle'));
        } else {
            $msg = get_string('allcourseok', 'tool_save_attestoodle', $state);
            $mform->addElement('static', 'idcourse', 'Cours', $msg);
        }

        $state->totmilestone = count($state->tabactivities);
        $state->nbmilestoneok = count($state->tabactivities) - $state->erractiv;

        if ($state->erractiv > 0) {
            $mform->addElement('advcheckbox', 'continuewithactivitieserror',
                get_string('errmilestone', 'tool_save_attestoodle', $state->erractiv),
                get_string ('ticktovalid', 'tool_save_attestoodle'));
        } else {
            $msg = get_string('allmilestoneok', 'tool_save_attestoodle', $state);
            $mform->addElement('static', 'idactivities', get_string('milestone', 'tool_save_attestoodle'), $msg);
        }

        if ($state->templateid > 0) {
            $mform->addElement('advcheckbox', 'settemplate',
                get_string('template', 'tool_save_attestoodle'),
                get_string('replacetemplate', 'tool_save_attestoodle', $template->name));
        }

        $state->totlearner = count($state->corrlearner);
        $state->learnerok = $state->totlearner - $state->errlearner;
        if ($state->learnerok > 0) {
            $mform->addElement('advcheckbox', 'addlearner',
                get_string('addlearner', 'tool_save_attestoodle', $state),
                get_string('ticktoadd', 'tool_save_attestoodle'));
        } else {
            if ($state->totlearner > 0) {
                $mform->addElement('static', 'idlearner', get_string('learner', 'tool_save_attestoodle'),
                    get_string('nolearnercorr', 'tool_save_attestoodle'));
            } else {
                $mform->addElement('static', 'idlearner', get_string('learner', 'tool_save_attestoodle'),
                    get_string('nolearner', 'tool_save_attestoodle'));
            }
        }
        $this->add_action_buttons(true);
    }

    /**
     * Custom validation function automagically called when the form
     * is submitted. The standard validations, such as required inputs or
     * value type check, are done by the parent validation() method.
     * See validation() method in moodleform class for more details.
     * @param stdClass $data of form
     * @param string $files list of the form files
     * @return array of error.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $tick = get_string('tickorcancel', 'tool_save_attestoodle');
        if (isset($data['okreplacetraining']) && $data['okreplacetraining'] == 0) {
            $errors['okreplacetraining'] = $tick;
        }
        if (isset($data['continuewitherror']) && $data['continuewitherror'] == 0) {
            $errors['continuewitherror'] = $tick;
        }
        if (isset($data['continuewithactivitieserror']) && $data['continuewithactivitieserror'] == 0) {
            $errors['continuewithactivitieserror'] = $tick;
        }
        return $errors;
    }
}
