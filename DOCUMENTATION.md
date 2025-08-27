# Documentation du projet Pharmacie Planning Easy

Ce projet est une application web légère permettant de gérer les horaires d'ouverture d'une pharmacie ainsi que le planning de deux pharmaciens sur deux semaines.

## Fonctionnement général
- **Création de projet** : l'utilisateur génère un code unique permettant d'identifier un planning.
- **Chargement de projet** : le code permet de recharger un planning précédemment sauvegardé.
- **Planification** : pour chaque jour, les horaires du matin et de l'après-midi peuvent être saisis, en attribuant un pharmacien (A ou B) à chaque plage horaire.
- **Calcul automatique** : le script JavaScript calcule en temps réel le nombre d'heures effectuées par chaque pharmacien sur l'ensemble des deux semaines et désactive l'enregistrement si l'un d'eux dépasse 70 heures.
- **Sauvegarde** : les données sont enregistrées côté serveur dans un fichier `.save` nommé d'après le code du projet.
- **Nettoyage** : à chaque chargement de la page, les fichiers `.save` plus anciens que 15 jours sont automatiquement supprimés.

## Structure des fichiers
- **`index.php`** : point d'entrée de l'application. Gère la création ou le chargement d'un projet, lit et écrit les données dans les fichiers `.save` et génère le formulaire HTML du planning.
- **`script.js`** : met à jour dynamiquement le nombre d'heures attribuées à chaque pharmacien et empêche la sauvegarde si le seuil de 70 heures est dépassé.
- **`style.css`** : fournit le style visuel de l'application (mise en page, couleurs, responsivité, etc.).
- **`README.md`** : brève présentation du projet.
- **`AGENTS.md`** : instructions pour les contributeurs et suivi des bugs.

## Notes
- Les fichiers `.save` contiennent les données JSON des projets et ne sont pas versionnés.
- Un suivi de bugs séparé doit être maintenu pour chaque fonctionnalité.

