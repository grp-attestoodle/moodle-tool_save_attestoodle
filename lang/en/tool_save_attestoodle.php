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
 * Plugin strings are defined here.
 *
 * @package     tool_save_attestoodle
 * @category    string
 * @copyright   2019 Universite du Mans
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['addlearner'] = 'Add learners ({$a->learnerok} / {$a->totlearner})';
$string['allcourseok'] = 'All courses have a correspondence ({$a->nbcourseok} / {$a->nbcourse})';
$string['allmilestoneok'] = 'All milestones have a match ({$a->nbmilestoneok} / {$a->totmilestone})';
$string['categnotfound'] = 'Original category not found';
$string['checktraining'] = 'Check to restore the training in the category {$a}';
$string['clone'] = 'Clone';
$string['clonecancel'] = 'Training cloning cancelled';
$string['clonecoursebefore'] = 'All courses must be cloned beforehand';
$string['courseerror'] = '{$a->errcourse} courses in errors and {$a->erractiv} milestones not renewed';
$string['error_integrity'] = 'Corrupted backup file. Stop recovery !';
$string['error_suffix'] = 'To clone the training, you must enter a suffix';
$string['error_version'] = 'Backup version not available !';
$string['errcloneexist'] = 'The training {$a} already exists !';
$string['errreadfile'] = 'Error when reading the file: {$a}';
$string['errmilestone'] = '{$a} milestone(s) not renewed';
$string['file_require'] = 'You must upload a backup file !';
$string['learner'] = 'Learner';
$string['load'] = 'Restore training';
$string['milestone'] = 'Milestones';
$string['newtraining'] = 'New training';
$string['nolearner'] = 'No learners in backup';
$string['nolearnercorr'] = 'No learners correspond to those of this training';
$string['pluginname'] = 'backup attestoodle';
$string['privacy:metadata'] = 'The Attestoodle backup tool does not record any personal data.';
$string['processerror'] = 'Unable to process';
$string['putfile'] = 'Put the backup to be restored';
$string['replacetemplate'] = '(tick to replace the template {$a})';
$string['replacetraining'] = 'Replace existing training';
$string['restore'] = 'Restore';
$string['restorecancel'] = 'Cancelled restoration';
$string['restorecateg'] = 'Restore in the category {$a}';
$string['restorecateg2'] = 'Restore in the category';
$string['restore_success'] = 'Restore completed';
$string['save'] = 'Save';
$string['save_attestoodle:save'] = 'Can perform a backup of a training';
$string['suffix'] = 'Suffix to be used for cloning';
$string['template'] = 'Certificate template';
$string['ticktoadd'] = ' Tick to confirm the addition';
$string['ticktovalid'] = ' (You must tick to validate)';
$string['tickorcancel'] = 'Please tick or cancel';
$string['titleclonevalide'] = 'Clone a training - Validation';
$string['titlerestore'] = 'Restore a formation - Reading the file';
$string['titlerestorevalide'] = 'Restore a training - Validation';
$string['tobecreate'] = 'To be created';
$string['trainingname'] = 'Name of the training course';
