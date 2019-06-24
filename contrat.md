[Retour](index.md)


# Attestoodle et Save_attestoodle #  

Le plugin de sauvegarde d'attestoodle doit être conforme au trois points suivant pour être pris en compte par Attestoodle :
  * être nommer tool_save_attestoodle
  * son fichier lib.php doit proposer la méthode : btn_save($trainingid)
  * son fichier lib.php doit proposer la méthode : lnk_load()
 
La méthode btn_save, fournit le code html d'un bouton qui va effectuer la sauvegarde de la formation.  

La méthode  lnk_load fournit le code html d'un lien vers le formulaire de restauration/clonage d'une formation.

## Sécurité des restaurations ##
Pour assurer l'adéquation du fichier de sauvegarde avec la version logiciel, les sauvegardes disposent d'un numéro de version.  
Pour limiter toutes modification manuelle, les fichiers de sauvegarde intègrent  une signature numérique, une erreur de code ERROR_INTEGRITY sera levée dans le cas ou la signature n'est pas bonne.  

## Qualité du code ##

|  Modules Travis  moodle-plugin-ci | Résultat              |
|-----------------------------------|-----------------------|
| phplint                           | 13 files. No syntax error found |
| phpcpd                            | 0.00% duplicated lines out of 1634 total lines of code. |
| phpmd  | (OK) exited with 0. |
| codechecker | (OK) exited with 0.  |
| validate | (OK) exited with 0.  |
| savepoints | (OK) exited with 0.  |
| mustache | No relevant files found to process, free pass! |
| grunt | exited with 0 |

Cf [Travis](https://travis-ci.org/grp-attestoodle/moodle-tool_save_attestoodle/jobs/549619241)

La documentation _phpdoc_ est complete.
