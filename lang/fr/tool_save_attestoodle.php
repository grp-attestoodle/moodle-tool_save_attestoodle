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
$string['addlearner'] = 'Ajouter les apprenants ({$a->learnerok} / {$a->totlearner})';
$string['allcourseok'] = 'Tous les cours ont une correspondance ({$a->nbcourseok} / {$a->nbcourse})';
$string['allmilestoneok'] = 'Tous les jalons ont une correspondance ({$a->nbmilestoneok} / {$a->totmilestone})';
$string['categnotfound'] = 'Catégorie d\'origine inexistante';
$string['checktraining'] = 'Cocher pour restaurer la formation dans la categorie {$a}';
$string['clone'] = 'Cloner';
$string['clonecancel'] = 'Clonage de formation annulé';
$string['clonecoursebefore'] = 'Tous les cours doivent être clonés au préalable';
$string['courseerror'] = '{$a->errcourse} cour(s) en erreurs et {$a->erractiv} jalon(s) non reconduit';
$string['error_integrity'] = 'Fichier de sauvegarde corrompu, arrêt de la restauration !';
$string['error_suffix'] = 'Pour cloner la formation, vous devez saisir un suffixe';
$string['error_version'] = 'Version de sauvegarde non prise en compte, arrêt de la restauration !';
$string['errcloneexist'] = 'La formation {$a} existe déjà !';
$string['errreadfile'] = 'Erreur à la lecture du fichier : {$a}';
$string['errmilestone'] = '{$a} jalon(s) non reconduit';
$string['file_require'] = 'Vous devez déposer un fichier de sauvegarde !';
$string['learner'] = 'Apprenant';
$string['load'] = 'Restaurer une formation';
$string['milestone'] = 'Jalons';
$string['newtraining'] = 'Nouvelle formation ';
$string['nolearner'] = 'Aucun apprenant dans la restauration';
$string['nolearnercorr'] = 'Aucun apprenant ne correspond à ceux de cette formation';
$string['pluginname'] = 'backup attestoodle';
$string['privacy:metadata'] = 'L\'outil de sauvegarde d\'Attestoodle n\'enregistre aucune donnée personnelle.';
$string['processerror'] = 'Traitement impossible ';
$string['putfile'] = 'Déposer la sauvegarde à restaurer';
$string['replacetemplate'] = '(cocher pour remplacer le modèle {$a})';
$string['replacetraining'] = 'Remplacer la formation existante';
$string['restore'] = 'Restaurer';
$string['restorecancel'] = 'Restauration annulée';
$string['restorecateg'] = 'Restaurer dans la catégorie {$a}';
$string['restorecateg2'] = 'Restaurer dans la catégorie';
$string['restore_success'] = 'Restauration terminée';
$string['save'] = 'Sauvegarder';
$string['save_attestoodle:save'] = 'Peux réaliser une sauvegarde d\'une formation';
$string['suffix'] = 'Suffixe à utiliser pour le clonage';
$string['template'] = 'Modèle d\'attestation';
$string['ticktoadd'] = ' (Cocher pour confirmer l\'ajout)';
$string['ticktovalid'] = ' (Vous devez cocher pour valider)';
$string['tickorcancel'] = 'Veuillez cocher ou annuler';
$string['titleclonevalide'] = 'Cloner une formation - Validation';
$string['titlerestore'] = 'Restaurer une formation - Lecture du fichier';
$string['titlerestorevalide'] = 'Restaurer une formation - Validation';
$string['tobecreate'] = 'A créer';
$string['trainingname'] = 'Nom de la formation';
