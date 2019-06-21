[Retour](index.md)

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

### Pour les éléments du modèle d'attestation ###
![detail](https://user-images.githubusercontent.com/26385729/59908411-9f52cc00-940d-11e9-84bc-5b570e759569.png)

Les mêmes données que celles de la table tool_attestoodle_tpl_detail.  
__L'enregistrement de type 'background' sera ignoré, puisque l'image n'est pas transportée ici.__

### Pour les apprenants ###

![apprenant](https://user-images.githubusercontent.com/26385729/59908679-3f105a00-940e-11e9-9a69-5d5ba9e8cf7f.png)

On dispose des informations de la table tool_attestoodle_learner plus l'identification naturelle des apprenants (leur email).  
Évidemment le _trainingid_ sera remplacé par celui de la formation créée.


### Pour les personnalisations des modèles d'attestation par apprenant ###

![person](https://user-images.githubusercontent.com/26385729/59908878-b3e39400-940e-11e9-96e8-4ecce881af6c.png)

Informations identiques à celles de la table tool_attestoodle_user_style, le modèle n'est pas identifié de façon naturel ici, on ne prendra donc en considération uniquement les exclusions de la génération d'attestation (enablecertificat == 0).

[Retour](index.md)
