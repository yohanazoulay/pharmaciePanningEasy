# Suivi des bugs - Rechargement des fichiers CSS/JS

## 2025-08-27
### Problème
Après modification des fichiers CSS ou JS, les navigateurs continuaient d'utiliser les anciennes versions mises en cache.

### Correction
Ajout d'un paramètre de version basé sur `filemtime` aux liens vers `style.css` et `script.js` dans `index.php` pour forcer le chargement de la dernière version des fichiers.
