# Mode Maintenance - Guide d'utilisation

## 🚧 Activation du mode maintenance

### Méthode 1 : Via .htaccess (Recommandée pour Apache)
1. **Pour activer la maintenance :**
   ```bash
   cd public/
   mv .htaccess .htaccess.normal
   mv .htaccess.maintenance .htaccess
   ```

2. **Pour désactiver la maintenance :**
   ```bash
   cd public/
   mv .htaccess .htaccess.maintenance
   mv .htaccess.normal .htaccess
   ```

### Méthode 2 : Via Symfony (Pour les environnements sans Apache)
1. Décommentez la route dans `src/Controller/MaintenanceController.php`
2. Commentez les autres routes dans `config/routes.yaml`

## 📁 Fichiers créés

- `templates/maintenance.html.twig` - Template Symfony pour la page de maintenance
- `src/Controller/MaintenanceController.php` - Contrôleur pour gérer la maintenance
- `public/.htaccess` - Configuration Apache pour rediriger vers la maintenance
- `public/maintenance.html` - Page de maintenance statique (fallback)

## 🎨 Fonctionnalités de la page de maintenance

- ✅ Design moderne et responsive
- ✅ Animation CSS (pulse, progress bar)
- ✅ Auto-refresh toutes les 5 minutes
- ✅ Horodatage en temps réel
- ✅ Informations de contact
- ✅ Temps estimé de retour
- ✅ Bouton d'actualisation manuelle

## 🔧 Personnalisation

### Modifier les informations de contact
Éditez le fichier `public/maintenance.html` et modifiez :
```html
<p><i class="fas fa-phone"></i> <strong>Téléphone :</strong> +33 1 23 45 67 89</p>
<p><i class="fas fa-envelope"></i> <strong>Email :</strong> support@hse-system.com</p>
<p><i class="fas fa-user-tie"></i> <strong>Responsable HSE :</strong> M. Dupont - 06 12 34 56 78</p>
```

### Modifier le temps estimé
Changez la valeur dans :
```html
<p><strong>2-4 heures</strong></p>
```

### Modifier le message
Éditez la section :
```html
<div class="maintenance-message">
    <p>Votre message personnalisé ici...</p>
</div>
```

## 🚀 Désactivation du mode maintenance

### Pour Apache (.htaccess)
```bash
cd public/
mv .htaccess .htaccess.maintenance
mv .htaccess.normal .htaccess
```

### Pour Symfony
1. Commentez la route de maintenance dans `MaintenanceController.php`
2. Décommentez les autres routes

## 📱 Responsive Design

La page de maintenance est entièrement responsive et s'adapte à tous les écrans :
- Desktop
- Tablette
- Mobile

## 🔒 Sécurité

- La page de maintenance empêche l'accès à toutes les fonctionnalités
- Les fichiers statiques (CSS, JS, images) restent accessibles
- Auto-refresh pour éviter les caches navigateur

## ⚡ Performance

- Page légère et rapide à charger
- Utilisation de CDN pour Bootstrap et Font Awesome
- CSS et JS optimisés
- Pas de dépendances lourdes
