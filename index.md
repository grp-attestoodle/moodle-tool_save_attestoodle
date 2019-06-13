# Outil de sauvegarde/restauration clonage d'Attestoodle #

## Sauvegarde ##

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
