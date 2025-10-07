# 🔄 Système de Réinitialisation des Inspections

## 📋 Vue d'ensemble

Le système de réinitialisation des inspections permet de gérer automatiquement la réinitialisation périodique des inspections avec archivage de l'historique.

### 🎯 Objectifs

- **Réinitialisation automatique** : Monte-charge (quotidienne), autres équipements (mensuelle)
- **Archivage complet** : Conservation de l'historique des inspections
- **Gestion manuelle** : Interface admin pour réinitialisations ponctuelles
- **Traçabilité** : Suivi des réinitialisations et statistiques

## 🏗️ Architecture

### Entités

#### `ResetInspection`
- **Rôle** : Archive des inspections réinitialisées
- **Champs** :
  - `equipmentType` : Type d'équipement
  - `equipmentId` : ID de l'équipement
  - `equipmentName` : Nom de l'équipement
  - `inspectionData` : Données complètes de l'inspection (JSON)
  - `resetDate` : Date de réinitialisation
  - `resetType` : Type de reset (daily/monthly/manual)
  - `resetReason` : Raison de la réinitialisation
  - `resetBy` : Utilisateur ayant effectué la réinitialisation

#### Entités d'Inspection Modifiées
Toutes les entités d'inspection ont été enrichies avec :
- `isActive` : Booléen indiquant si l'inspection est active
- `resetDate` : Date de la dernière réinitialisation
- `resetReason` : Raison de la réinitialisation

### Services

#### `ResetInspectionService`
- **Méthodes principales** :
  - `resetInspectionsByType()` : Réinitialise un type d'équipement
  - `resetAllInspections()` : Réinitialise tous les équipements
  - `needsReset()` : Vérifie si une réinitialisation est nécessaire
  - `archiveInspection()` : Archive une inspection avant réinitialisation

### Commandes Console

#### `ResetInspectionsCommand`
```bash
# Réinitialisation manuelle
php bin/console app:reset-inspections extincteur --reason="Maintenance"

# Réinitialisation forcée
php bin/console app:reset-inspections all --force

# Simulation (dry-run)
php bin/console app:reset-inspections all --dry-run
```

### Interface Admin

#### Routes
- `/admin/reset-inspections/` : Liste des réinitialisations
- `/admin/reset-inspections/statistics` : Statistiques
- `/admin/reset-inspections/archive/{id}` : Détail d'une archive

#### Fonctionnalités
- **Réinitialisation manuelle** : Interface pour déclencher des réinitialisations
- **Consultation des archives** : Visualisation des données archivées
- **Statistiques** : Graphiques et tableaux de bord

## ⚙️ Configuration

### Périodicité

#### Monte-charge
- **Fréquence** : Quotidienne
- **Déclenchement** : Chaque jour à 00:00
- **Commande** : `php bin/console app:reset-inspections monte_charge`

#### Autres équipements
- **Fréquence** : Mensuelle
- **Déclenchement** : Premier de chaque mois
- **Équipements** : Extincteurs, Sirènes, Extinction RAM

### Planification (Cron)

#### Configuration recommandée
```bash
# Réinitialisation quotidienne des monte-charge (tous les jours à 00:00)
0 0 * * * /path/to/scripts/reset_inspections.sh

# Réinitialisation mensuelle (premier du mois à 01:00)
0 1 1 * * /path/to/scripts/reset_inspections.sh
```

#### Script d'automatisation
Le script `scripts/reset_inspections.sh` gère :
- Réinitialisation quotidienne des monte-charge
- Réinitialisation mensuelle des autres équipements
- Logging des opérations
- Gestion des erreurs

## 🔧 Utilisation

### Réinitialisation Manuelle

1. **Via l'interface admin** :
   - Aller sur `/admin/reset-inspections/`
   - Sélectionner le type d'équipement
   - Saisir une raison (optionnel)
   - Cliquer sur "Réinitialiser"

2. **Via la ligne de commande** :
   ```bash
   php bin/console app:reset-inspections [type] --reason="Raison"
   ```

### Consultation des Archives

1. **Liste des réinitialisations** :
   - Accès : `/admin/reset-inspections/`
   - Affichage : Tableau avec filtres et recherche

2. **Détail d'une archive** :
   - Accès : `/admin/reset-inspections/archive/{id}`
   - Affichage : Données complètes de l'inspection archivée

3. **Statistiques** :
   - Accès : `/admin/reset-inspections/statistics`
   - Affichage : Graphiques et tableaux de bord

### Surveillance

#### Logs
- **Fichier** : `/var/log/reset_inspections.log`
- **Contenu** : Historique des réinitialisations automatiques

#### Vérification
```bash
# Vérifier les logs
tail -f /var/log/reset_inspections.log

# Tester une réinitialisation
php bin/console app:reset-inspections all --dry-run
```

## 🚨 Gestion des Erreurs

### Types d'erreurs
1. **Erreurs de base de données** : Problèmes de connexion ou de requêtes
2. **Erreurs de sérialisation** : Problèmes lors de l'archivage des données
3. **Erreurs de permissions** : Problèmes d'accès aux fichiers

### Résolution
1. **Vérifier les logs** : Consulter les fichiers de log
2. **Tester manuellement** : Utiliser la commande avec `--dry-run`
3. **Vérifier les permissions** : S'assurer que les droits sont corrects

## 📊 Monitoring

### Métriques importantes
- **Nombre de réinitialisations** : Par type d'équipement et période
- **Taux d'erreur** : Pourcentage d'échecs
- **Temps de traitement** : Durée des opérations
- **Taille des archives** : Espace disque utilisé

### Alertes recommandées
- **Échec de réinitialisation** : Notification en cas d'erreur
- **Espace disque** : Alerte si les archives deviennent trop volumineuses
- **Performance** : Alerte si les opérations prennent trop de temps

## 🔒 Sécurité

### Permissions
- **Interface admin** : Réservée aux utilisateurs avec `ROLE_ADMIN`
- **Commandes console** : Accessibles aux administrateurs système
- **Archives** : Lecture seule pour les utilisateurs normaux

### Données sensibles
- **Archivage** : Les données sont sérialisées en JSON
- **Photos** : Les fichiers sont conservés dans le système de fichiers
- **Traçabilité** : Toutes les opérations sont loggées

## 🚀 Évolutions futures

### Améliorations possibles
1. **Compression des archives** : Réduction de l'espace disque
2. **Rétention automatique** : Suppression des anciennes archives
3. **Notifications** : Alertes par email en cas d'erreur
4. **API REST** : Interface programmatique pour les réinitialisations
5. **Dashboard temps réel** : Monitoring en direct des opérations

### Intégrations
1. **Monitoring** : Intégration avec des outils comme Prometheus
2. **Logs centralisés** : Envoi vers ELK Stack ou équivalent
3. **Notifications** : Intégration avec Slack, Teams, etc.
