# Suivi des bugs - Sections du planning

## 2025-08-27
- **Problème** : l'ajout d'une tranche d'ouverture créait automatiquement un segment de planning couvrant toute la tranche, empêchant de définir plusieurs pharmaciens sur des horaires différents.
- **Résolution** : séparation du planning des pharmaciens des horaires d'ouverture. Les tranches de planning peuvent maintenant être ajoutées ou supprimées indépendamment, avec choix des heures et du pharmacien.

## 2025-08-28
- **Problème** : possibilité d'ajouter un horaire de pharmacien en dehors des heures d'ouverture de l'officine.
- **Résolution** : ajout de contrôles côté client et côté serveur. Le formulaire refuse désormais les tranches hors horaires d'ouverture et celles-ci sont ignorées à la sauvegarde.

## 2025-08-29
- **Amélioration** : refonte de l'interface avec Tailwind CSS. Les sections sont présentées sous forme de cartes, les sections 0 et 3 partagent une ligne en deux colonnes et les sections 1 et 2 occupent chacune toute la largeur.

## 2025-08-30
- **Problème** : les boutons "Ajouter tranche" étaient peu visibles et ne respectaient pas le design Apple.
- **Résolution** : style unifié des boutons avec une couleur bleue, des coins arrondis et un symbole « + » pour améliorer la visibilité.
