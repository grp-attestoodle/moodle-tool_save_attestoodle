## Le fichier de sauvegarde ##
Le fichier de sauvegarde se nomme formation_{$trainingid}.json  
**Exemple**  
![fichier1](https://user-images.githubusercontent.com/26385729/59779686-03b04700-92b9-11e9-9c4b-a0d67fbd7bc4.png)

A chaque table concernée par la sauvegarde correspond une entrée dans la structure json.

|  Entrée JSON     |    Table correspondante     |
|------------------|-----------------------------|
| training         | tool_attestoodle_training   |
| milestones       | tool_attestoodle_milestone  |
| relationtemplate | tool_attestoodle_train_style |
| template         | tool_attestoodle_template   |
| templatedetails  | tool_attestoodle_tpl_detail |
| learners         | tool_attestoodle_learner    |
| templateusers    | tool_attestoodle_user_style |

### Pour la formation : ###

![forma](https://user-images.githubusercontent.com/26385729/59906543-41bc8080-9409-11e9-938b-fb2780e5943a.png)

La catégorie de rattachement de la formation reste identifiée avec un identifiant technique, si celui-ci n'existe pas lors de la restauration, on placera la formation dans la catégorie par défaut.

### Pour les Jalons ###
![jalon](https://user-images.githubusercontent.com/26385729/59907600-bf818b80-940b-11e9-819c-0c39ef862db7.png)

Chaque enregistrement est enrichit de course_shortname, section et posr qui constituent l'identification relative du module jalon.

### Pour la relation Formation - Modèle d'attestation ###

![relatmodform](https://user-images.githubusercontent.com/26385729/59907915-75e57080-940c-11e9-906b-2f3d93412832.png)

On retrouve exactement les mêmes informations qu'en table tool_attestoodle_train_style.

### Pour la définition du modèle d'attestation ###
![model](https://user-images.githubusercontent.com/26385729/59908079-d96f9e00-940c-11e9-91cf-aac6db1c8472.png)

Les modèles disposent de 'name' comme identifiant naturel, lorsqu'un modèle n'existe pas il sera créé, sinon l'opérateur aura la possibilité de remplacer le modèle par celui de la sauvegarde.  
Le userid sera remplacé par celui de l'opérateur de la sauvegarde, et les dates correspondront à la date de création.

