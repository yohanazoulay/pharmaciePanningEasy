# Suivi des bugs - Sections du planning

## 2025-08-27
- **Problème** : l'ajout d'une tranche d'ouverture créait automatiquement un segment de planning couvrant toute la tranche, empêchant de définir plusieurs pharmaciens sur des horaires différents.
- **Résolution** : séparation du planning des pharmaciens des horaires d'ouverture. Les tranches de planning peuvent maintenant être ajoutées ou supprimées indépendamment, avec choix des heures et du pharmacien.
