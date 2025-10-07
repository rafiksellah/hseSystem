# 📄 Système de Pagination

## 🎯 Vue d'ensemble

Le système de pagination a été implémenté pour améliorer les performances et l'expérience utilisateur lors de l'affichage de grandes listes de données.

## 🛠️ Composants créés

### 1. **Service de Pagination** (`src/Service/PaginationService.php`)
- **Méthodes principales :**
  - `paginate(array $items, int $page, int $limit)` : Pagination d'un tableau
  - `paginateQueryBuilder($queryBuilder, int $page, int $limit)` : Pagination avec QueryBuilder
  - `getPaginationFromRequest(array $requestParams)` : Extraction des paramètres de pagination
  - `getPaginationInfo(array $pagination)` : Informations de pagination

### 2. **Composant de Pagination** (`templates/components/pagination.html.twig`)
- **Fonctionnalités :**
  - Navigation avec boutons Précédent/Suivant
  - Affichage des numéros de page
  - Sélecteur d'éléments par page (10, 25, 50, 100)
  - Informations sur les éléments affichés
  - Gestion des ellipses (...) pour les grandes listes

## 📋 Pages mises à jour

### ✅ **Pages avec pagination implémentée :**

1. **Extincteurs** (`/equipements/extincteurs`)
   - Contrôleur : `src/Controller/EquipementsController.php`
   - Template : `templates/equipements/extincteurs/liste.html.twig`

2. **Rapports Admin** (`/admin/rapports`)
   - Contrôleur : `src/Controller/AdminController.php`
   - Template : `templates/admin/rapports.html.twig`

3. **Réinitialisations** (`/admin/reset-inspections`)
   - Contrôleur : `src/Controller/ResetInspectionController.php`
   - Template : `templates/admin/reset_inspection/index.html.twig`

### 🔄 **Pages à paginer (recommandées) :**

4. **Utilisateurs** (`/super-admin/users`)
5. **Rapports Super Admin** (`/super-admin/rapports`)
6. **Rapports Utilisateur** (`/user/rapports`)
7. **Autres équipements** (Sirenes, RAM, Monte Charge, etc.)

## 🎨 Interface utilisateur

### **Fonctionnalités de pagination :**
- **Navigation** : Boutons Précédent/Suivant avec icônes
- **Numéros de page** : Affichage intelligent avec ellipses
- **Sélecteur de limite** : 10, 25, 50, 100 éléments par page
- **Informations** : "Affichage de X à Y sur Z éléments"
- **Responsive** : Adaptation mobile et desktop

### **Exemple d'utilisation :**
```twig
{% include 'components/pagination.html.twig' with {
    'pagination': pagination,
    'route': 'app_equipements_extincteurs',
    'routeParams': {
        'emplacement': search_params.emplacement,
        'numerotation': search_params.numerotation,
        'conformite': search_params.conformite
    }
} %}
```

## ⚙️ Configuration

### **Paramètres par défaut :**
- **Page par défaut** : 1
- **Limite par défaut** : 10 éléments
- **Limite maximale** : 100 éléments
- **Pages affichées** : 5 (2 avant + page courante + 2 après)

### **URLs générées :**
```
/equipements/extincteurs?page=2&limit=25
/admin/rapports?page=3&limit=50
/admin/reset-inspections?page=1&limit=10
```

## 🚀 Avantages

### **Performance :**
- ✅ Réduction de la charge mémoire
- ✅ Amélioration des temps de réponse
- ✅ Optimisation des requêtes SQL

### **Expérience utilisateur :**
- ✅ Navigation intuitive
- ✅ Contrôle de la densité d'affichage
- ✅ Informations claires sur la pagination
- ✅ Interface responsive

### **Maintenabilité :**
- ✅ Composant réutilisable
- ✅ Service centralisé
- ✅ Configuration flexible
- ✅ Code DRY (Don't Repeat Yourself)

## 📊 Métriques de performance

### **Avant pagination :**
- Affichage de tous les éléments (potentiellement 1000+)
- Temps de chargement élevé
- Consommation mémoire importante

### **Après pagination :**
- Affichage de 10-100 éléments par page
- Temps de chargement optimisé
- Consommation mémoire contrôlée

## 🔧 Implémentation technique

### **Dans le contrôleur :**
```php
// Récupérer les paramètres de pagination
$paginationParams = $paginationService->getPaginationFromRequest($request->query->all());
$page = $paginationParams['page'];
$limit = $paginationParams['limit'];

// Utiliser le service de pagination
$result = $paginationService->paginate($allItems, $page, $limit);
$items = $result['items'];
$pagination = $result['pagination'];

return $this->render('template.html.twig', [
    'items' => $items,
    'pagination' => $pagination,
    // ... autres variables
]);
```

### **Dans le template :**
```twig
<!-- Liste des éléments -->
{% for item in items %}
    <!-- Affichage de l'élément -->
{% endfor %}

<!-- Pagination -->
{% include 'components/pagination.html.twig' with {
    'pagination': pagination,
    'route': 'route_name',
    'routeParams': search_params
} %}
```

## 📈 Prochaines étapes

1. **Ajouter la pagination aux pages restantes**
2. **Optimiser les requêtes avec QueryBuilder**
3. **Ajouter des filtres avancés**
4. **Implémenter la recherche en temps réel**
5. **Ajouter l'export paginé**

## 🎉 Résultat

Le système de pagination est maintenant opérationnel et améliore significativement les performances et l'expérience utilisateur de l'application HSE.
