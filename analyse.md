<p align="right"><a href="./index.md"> Retour </a></p>
# Analyse
## Objectif ##
L'objectif est de permettre la restauration des formations dans leur contexte, la sauvegarde ici ne traite pas le contexte mais uniquement la définition des formations.

On ne sauvegarde pas :
  * les cours
  * les élèves inscrits aux cours
  * les activités des élèves
  
Toutes ces informations sont sous la responsabilité de la sauvegarde des cours.
 
## Identifiants naturels ##
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
 

## Format de la sauvegarde ##
La sauvegarde consistera en un fichier JSON non compressé.  
Le stockage sur le serveur ne sera pas pris en compte, le fichier de sauvegarde sera sous la responsabilité de son détenteur, pour cela le fichier sera directement téléchargé sur le poste de l'opérateur, et lors de la restauration le fichier devra être téléversé.  
L'intégrité de la sauvegarde sera assurée par une signature électronique contenue dans le fichier.  
Les sauvegardes seront munies d'un numéro de version, de sorte à pouvoir faire évoluer le système de sauvegarde sans perdre les anciennes versions.  
