# Outil de sauvegarde/restauration clonage d'Attestoodle #

## Analyse préalable ##

L'objectif est de permettre la restauration des formations dans leur contexte, la sauvegarde ici ne traite pas le contexte mais uniquement la définition des formations.

On ne sauvegarde pas :
  * les cours
  * les élèves inscrits aux cours
  * les activités des élèves
  
Toutes ces informations sont sous la responsabilité de la sauvegarde des cours.
 
La définition de la formation devra s'appuyer sur des identifiants naturels. 


|  Concept     |    Identifiant naturel |
|--------------|------------------------|
| Cours        | Nom abrégé             |
| Élèves       | Adresse mail           |
| Formation    | Nom de la formation    |
| Modèle d'attestation | Nom du modèle  |
| Module (activité ou ressource) | aucun |

Dans le  cas ou le contexte est préalablement restauré, les modules n'auront pas les mêmes identifiants techniques, d'où la nécessité de déterminer un identifiant naturel.

On ne peut rien ajouter aux modules, une solution consiste à identifier les modules de façon relative au cours qui les portes.

![idmodule](https://user-images.githubusercontent.com/26385729/59425798-eff77300-8dd6-11e9-851c-82468b7543e0.png)

Chaque cours est constitué de sections, et chaque section porte un ensemble de modules dans leur ordre d'affichage, ainsi le module en vert sur l'illustration, peut être identifié comme étant le 3ième module de la section C dans le cours utilisé ici.

Donc on pourra identifier les modules de façon relative à leur position dans un cours avec :
 * le nom abrégé du cours (_course_shortname_)
 * le numéro de la section au sein du cours (_section_)
 * la position du module au sein de la section (_posr_).
