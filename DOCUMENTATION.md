# Documentation du projet Pharmacie Planning Easy

Ce projet est une application web légère permettant de gérer les horaires d'ouverture d'une pharmacie ainsi que le planning de deux pharmaciens sur deux semaines.

L'interface utilise Tailwind CSS et présente les différentes sections sous forme de cartes pour une meilleure lisibilité (Sections 0 et 3 côte à côte, puis les sections 1 et 2 en pleine largeur).

## Fonctionnement général
- **Création de projet** : l'utilisateur génère un code unique permettant d'identifier un planning.
- **Chargement de projet** : le code permet de recharger un planning précédemment sauvegardé.
- **Section 0 – Options** : permet de définir un nom et une couleur pour chacun des deux pharmaciens.
- **Section 1 – Horaires d'ouverture** : pour chaque jour de la semaine, l'utilisateur peut ajouter autant de tranches d'ouverture qu'il le souhaite.
- **Section 2 – Planning des pharmaciens** : des tranches indépendantes peuvent être ajoutées pour chaque jour afin d'assigner un pharmacien pour la semaine 1 et un autre pour la semaine 2, mais elles doivent obligatoirement rester dans les horaires d'ouverture définis à la section 1.
- **Section 3 – Récapitulatif** : affichage du total d'heures par pharmacien et du nombre d'heures d'ouverture (lundi-samedi).
- **Calcul automatique** : le script JavaScript calcule en temps réel le nombre d'heures effectuées par chaque pharmacien pour chaque semaine, le total sur deux semaines ainsi que le nombre d'heures d'ouverture. L'enregistrement est désactivé si un pharmacien dépasse 70 heures.
 - **Sauvegarde** : les données sont enregistrées côté serveur dans un fichier `.save` nommé d'après le code du projet. Un message toast confirme la réussite (ou signale une erreur) et rappelle de noter le code.
- **Nettoyage** : à chaque chargement de la page, les fichiers `.save` plus anciens que 15 jours sont automatiquement supprimés.
- **Export PDF** : un bouton « Planning imprimable (PDF) » ouvre une vue A4 paysage (semaine 1 puis semaine 2) prête à être imprimée ou enregistrée en PDF.
- **Aide intégrée** : un bouton « Aide » affiche une barre latérale expliquant les étapes d'utilisation de l'outil.

## Structure des fichiers
- **`index.php`** : point d'entrée de l'application. Gère la création ou le chargement d'un projet, lit et écrit les données dans les fichiers `.save` et génère le formulaire HTML du planning.
- **`script.js`** : gère l'ajout des tranches d'ouverture et des tranches de planning, met à jour dynamiquement le nombre d'heures attribuées à chaque pharmacien, calcule les heures d'ouverture du lundi au samedi et empêche la sauvegarde si le seuil de 70 heures est dépassé.
- **`style.css`** : fournit le style visuel de l'application (mise en page, couleurs, responsivité, etc.).
- **`README.md`** : brève présentation du projet.
- **`AGENTS.md`** : instructions pour les contributeurs et suivi des bugs.

## Notes
- Les fichiers `.save` contiennent les données JSON des projets et ne sont pas versionnés.
- Un suivi de bugs séparé doit être maintenu pour chaque fonctionnalité.
- Les fichiers CSS et JS sont chargés avec un paramètre de version basé sur leur date de modification afin d'éviter les problèmes de cache.
- Les boutons « Ajouter tranche » sont stylisés dans un esprit Apple pour être plus visibles.
- Le pied de page affiche « Fait avec ❤️ par Meditrust pour les pharmacies » avec le logo redirigeant vers la page de contact.

