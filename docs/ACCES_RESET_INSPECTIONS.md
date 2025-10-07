# 🔗 Guide d'Accès au Système de Réinitialisation

## 📍 **Comment Accéder au Système de Réinitialisation**

### 🎯 **Méthodes d'Accès :**

#### **1. Via la Navigation Principale (Recommandé)**

**Pour les Super Admins :**
- Connectez-vous avec un compte Super Admin
- Dans la sidebar gauche, section "OUTILS SUPER ADMIN"
- Cliquez sur **"🔄 Réinitialisations (Inspections)"**

**Pour les Admins :**
- Connectez-vous avec un compte Admin
- Dans la sidebar gauche, section "OUTILS ADMIN"  
- Cliquez sur **"🔄 Réinitialisations (Inspections)"**

#### **2. Via le Dashboard des Équipements**

- Allez sur **"Dashboard Équipements"** (`/equipements/dashboard`)
- Cliquez sur le bouton **"🔄 Réinitialisations"** en haut à droite
- (Visible uniquement pour les utilisateurs avec `ROLE_ADMIN`)

#### **3. Accès Direct par URL**

- **Interface principale :** `/admin/reset-inspections/`
- **Statistiques :** `/admin/reset-inspections/statistics`
- **Archive détaillée :** `/admin/reset-inspections/archive/{id}`

### 🔐 **Permissions Requises**

- **Rôle minimum :** `ROLE_ADMIN`
- **Accès :** Tous les admins (Super Admin et Admin de zone)
- **Restriction :** Les utilisateurs normaux n'ont pas accès

### 🎛️ **Fonctionnalités Disponibles**

#### **Interface Principale (`/admin/reset-inspections/`)**
- ✅ **Statistiques rapides** : Nombre total de réinitialisations
- ✅ **Réinitialisation manuelle** : Formulaire pour déclencher une réinitialisation
- ✅ **Historique complet** : Liste de toutes les réinitialisations
- ✅ **Filtres et recherche** : Par type d'équipement, date, etc.

#### **Statistiques (`/admin/reset-inspections/statistics`)**
- ✅ **Graphiques** : Répartition par type d'équipement
- ✅ **Évolution temporelle** : Tendances sur 30 jours
- ✅ **Métriques détaillées** : Analyses approfondies

#### **Consultation des Archives (`/admin/reset-inspections/archive/{id}`)**
- ✅ **Données complètes** : Toutes les informations de l'inspection archivée
- ✅ **Photos** : Images d'observation conservées
- ✅ **Critères** : Détail de tous les critères d'inspection
- ✅ **Traçabilité** : Qui, quand, pourquoi

### 🚀 **Utilisation Rapide**

#### **Réinitialisation Manuelle :**
1. Allez sur `/admin/reset-inspections/`
2. Sélectionnez le type d'équipement
3. Saisissez une raison (optionnel)
4. Cliquez sur **"🔄 Réinitialiser"**
5. Confirmez l'action

#### **Consultation des Archives :**
1. Dans la liste des réinitialisations
2. Cliquez sur **"👁️ Voir"** pour une archive
3. Consultez toutes les données archivées

#### **Statistiques :**
1. Cliquez sur **"📊 Statistiques"**
2. Explorez les graphiques et métriques
3. Analysez les tendances

### 🔧 **Commandes Console (Avancé)**

#### **Réinitialisation via ligne de commande :**
```bash
# Tous les équipements
php bin/console app:reset-inspections all

# Type spécifique
php bin/console app:reset-inspections extincteur --reason="Maintenance"

# Simulation (recommandé avant exécution)
php bin/console app:reset-inspections all --dry-run
```

#### **Automatisation :**
```bash
# Réinitialisation quotidienne (monte-charge)
0 0 * * * /path/to/scripts/reset_inspections.sh

# Réinitialisation mensuelle (autres équipements)
0 1 1 * * /path/to/scripts/reset_inspections.sh
```

### 🆘 **Dépannage**

#### **Problème : "Accès refusé"**
- ✅ Vérifiez que vous avez le rôle `ROLE_ADMIN`
- ✅ Reconnectez-vous si nécessaire

#### **Problème : "Page non trouvée"**
- ✅ Vérifiez l'URL : `/admin/reset-inspections/`
- ✅ Videz le cache : `php bin/console cache:clear`

#### **Problème : "Erreur de base de données"**
- ✅ Vérifiez que les migrations sont appliquées
- ✅ Exécutez : `php bin/console doctrine:migrations:migrate`

### 📞 **Support**

Si vous rencontrez des problèmes :
1. **Vérifiez les logs** : `/var/log/reset_inspections.log`
2. **Testez en mode simulation** : `--dry-run`
3. **Contactez l'administrateur système**

### 🎯 **Résumé des URLs**

| Fonctionnalité | URL | Description |
|---|---|---|
| **Interface principale** | `/admin/reset-inspections/` | Gestion des réinitialisations |
| **Statistiques** | `/admin/reset-inspections/statistics` | Graphiques et métriques |
| **Archive** | `/admin/reset-inspections/archive/{id}` | Détail d'une archive |
| **Dashboard équipements** | `/equipements/dashboard` | Accès rapide via bouton |

---

## 🎉 **Vous êtes maintenant prêt à utiliser le système de réinitialisation !**

**Prochaines étapes recommandées :**
1. **Explorez l'interface** : Familiarisez-vous avec les fonctionnalités
2. **Testez une réinitialisation** : Utilisez le mode simulation d'abord
3. **Configurez l'automatisation** : Planifiez les réinitialisations automatiques
4. **Surveillez les logs** : Assurez-vous que tout fonctionne correctement
