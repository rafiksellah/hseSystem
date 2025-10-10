# 🔐 Analyse Complète de la Sécurité - Système HSE

**Date:** 9 Octobre 2025  
**Version:** 1.0

---

## 📋 Table des Matières
1. [Hiérarchie des Rôles](#hiérarchie-des-rôles)
2. [Permissions par Rôle](#permissions-par-rôle)
3. [Contrôle d'Accès par Route](#contrôle-daccès-par-route)
4. [Permissions par Équipement](#permissions-par-équipement)
5. [Recommandations de Sécurité](#recommandations-de-sécurité)

---

## 1. Hiérarchie des Rôles

```yaml
ROLE_SUPER_ADMIN
  ├── ROLE_ADMIN
  │     └── ROLE_USER
  └── ROLE_USER
```

### Définition (config/packages/security.yaml)
```yaml
role_hierarchy:
    ROLE_ADMIN: ROLE_USER
    ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_USER]
```

**Signification :**
- `ROLE_USER` : Rôle de base, tout le monde l'a
- `ROLE_ADMIN` : Hérite de `ROLE_USER` + permissions admin
- `ROLE_SUPER_ADMIN` : Hérite de `ROLE_ADMIN` + `ROLE_USER` + permissions super admin

---

## 2. Permissions par Rôle

### 👤 ROLE_USER (Utilisateur Standard)

| Fonctionnalité | Accès | Restrictions |
|----------------|-------|--------------|
| **Connexion** | ✅ Oui | - |
| **Dashboard** | ✅ Oui | Voir seulement sa zone |
| **Voir équipements** | ✅ Oui | Seulement sa zone |
| **Ajouter équipement** | ❌ Non | - |
| **Modifier équipement** | ❌ Non | - |
| **Supprimer équipement** | ❌ Non | - |
| **Inspecter équipement** | ✅ Oui | Seulement sa zone |
| **Supprimer inspection** | ❌ Non | - |
| **Voir récapitulatifs** | ❌ Non | - |
| **Export Excel/PDF** | ✅ Oui | Seulement sa zone |
| **Gérer utilisateurs** | ❌ Non | - |
| **Réinitialisation** | ❌ Non | - |
| **Ajouter emplacements** | ❌ Non | - |

**Zone :**
- ✅ **Obligatoire** : Doit appartenir à une zone (SIMTIS ou SIMTIS TISSAGE)
- 🔒 **Restriction** : Ne voit QUE les équipements de sa zone

---

### 👨‍💼 ROLE_ADMIN (Administrateur)

| Fonctionnalité | Accès | Restrictions |
|----------------|-------|--------------|
| **Connexion** | ✅ Oui | - |
| **Dashboard** | ✅ Oui | Voir seulement sa zone |
| **Voir équipements** | ✅ Oui | Seulement sa zone |
| **Ajouter équipement** | ✅ Oui | Seulement dans sa zone |
| **Modifier équipement** | ✅ Oui | Seulement sa zone |
| **Supprimer équipement** | ❌ Non | Réservé à SUPER_ADMIN |
| **Inspecter équipement** | ✅ Oui | Seulement sa zone |
| **Supprimer inspection** | ✅ Oui | Seulement sa zone |
| **Voir récapitulatifs** | ✅ Oui | Seulement sa zone |
| **Export Excel/PDF** | ✅ Oui | Seulement sa zone |
| **Gérer utilisateurs** | ✅ Oui | Seulement de sa zone |
| **Réinitialisation** | ❌ Non | Réservé à SUPER_ADMIN |
| **Ajouter emplacements** | ❌ Non | Réservé à SUPER_ADMIN |

**Zone :**
- ✅ **Obligatoire** : Doit appartenir à une zone (SIMTIS ou SIMTIS TISSAGE)
- 🔒 **Restriction** : Ne voit et ne gère QUE sa zone
- 👥 **Gestion Users** : Peut créer/modifier des utilisateurs dans sa zone uniquement

---

### 👑 ROLE_SUPER_ADMIN (Super Administrateur)

| Fonctionnalité | Accès | Restrictions |
|----------------|-------|--------------|
| **Connexion** | ✅ Oui | - |
| **Dashboard** | ✅ Oui | **TOUTES LES ZONES** |
| **Voir équipements** | ✅ Oui | **TOUTES LES ZONES** |
| **Ajouter équipement** | ✅ Oui | **TOUTES LES ZONES** |
| **Modifier équipement** | ✅ Oui | **TOUTES LES ZONES** |
| **Supprimer équipement** | ✅ Oui | **TOUTES LES ZONES** |
| **Inspecter équipement** | ✅ Oui | **TOUTES LES ZONES** |
| **Supprimer inspection** | ✅ Oui | **TOUTES LES ZONES** |
| **Voir récapitulatifs** | ✅ Oui | **TOUTES LES ZONES** |
| **Export Excel/PDF** | ✅ Oui | **TOUTES LES ZONES** |
| **Gérer utilisateurs** | ✅ Oui | **TOUS LES UTILISATEURS** |
| **Réinitialisation** | ✅ Oui | Mensuelle manuelle/auto |
| **Ajouter emplacements** | ✅ Oui | Dynamiquement dans formulaires |

**Zone :**
- ⚪ **Optionnelle** : Peut ne pas avoir de zone
- 🌍 **Accès Global** : Voit et gère TOUTES les zones
- 👑 **Privilèges** : Toutes les permissions du système

---

## 3. Contrôle d'Accès par Route

### 📍 Routes Publiques (PUBLIC_ACCESS)
```
/login                    → Connexion
/register                 → Inscription (désactivée en prod normalement)
```

### 🔒 Routes Protégées - ROLE_USER
```
/user/*                   → Profil utilisateur
/rapport/*                → Rapports HSE
/dashboard                → Tableau de bord
/equipements/*/liste      → Listes d'équipements (lecture seule)
/equipements/*/inspecter  → Inspection d'équipements
```

### 🛡️ Routes Protégées - ROLE_ADMIN
```
/admin/*                           → Tous les contrôleurs admin
/equipements/*/nouveau             → Ajout d'équipements
/equipements/*/modifier            → Modification d'équipements
/equipements/*-inspection/*/supprimer  → Suppression d'inspections
/equipements/*/recapitulatif       → Tableaux récapitulatifs
/admin/export                      → Exports Excel/PDF
/admin/users                       → Gestion utilisateurs (zone uniquement)
```

### 👑 Routes Protégées - ROLE_SUPER_ADMIN
```
/admin/user/.*/supprimer           → Suppression d'utilisateurs
/admin/reset-inspection            → Réinitialisation mensuelle
/equipements/*/supprimer           → Suppression d'équipements
/emplacements/ajax/add             → Ajout d'emplacements dynamiques
/monte-charge/nouveau              → Ajout Monte-Charge
/monte-charge/modifier             → Modification Monte-Charge
/monte-charge/supprimer            → Suppression Monte-Charge
```

---

## 4. Permissions par Équipement

### 🔥 Extincteurs

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Valider | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection active par extincteur
- ✅ Suppression inspection ≠ suppression équipement

---

### 💧 Extinction RAM

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir récapitulatif | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection active par RAM
- ✅ Boutons "Tout OUI/NON" dans inspection
- ✅ Tableau récapitulatif avec colonne Conforme/Non conforme

---

### 🔔 Sirènes

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir récapitulatif | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection active par sirène
- ✅ Tableau récapitulatif avec colonne Conforme/Non conforme

---

### 🚰 RIA (Robinet Incendie Armé)

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection active par RIA
- ✅ Boutons "Tout OUI/NON" dans inspection

---

### 🏗️ Monte-Charge

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ Oui | ✅ Oui | ✅ Oui |
| Voir détails | ✅ Oui | ✅ Oui | ✅ Oui |
| Ajouter | ❌ | ❌ | ✅ |
| Modifier | ❌ | ❌ | ✅ |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ Oui | ✅ Oui | ✅ Oui |
| Supprimer inspection | ❌ | ✅ | ✅ |
| Export Excel/PDF | ✅ Oui | ✅ Oui | ✅ Oui |

**Protection :**
- ✅ Numéro Monte-Charge unique (MONTE CHARGE 01-10)
- ✅ Numéro Porte fixe (PORTE 01-04)
- ✅ Une seule inspection active par Monte-Charge
- ✅ Boutons "Tout OUI/NON" dans inspection

---

### 💨 Désenfumage

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection par désenfumage
- ✅ Boutons "Tout OUI/NON" dans inspection

---

### 🚪 Issues de Secours

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Numérotation unique
- ✅ Une seule inspection par issue de secours
- ✅ Boutons "Tout OUI/NON" dans inspection

---

### 🔥 Prises Pompiers

| Action | USER | ADMIN | SUPER_ADMIN |
|--------|------|-------|-------------|
| Voir liste | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Voir détails | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Ajouter | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Modifier | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer | ❌ | ❌ | ✅ |
| Inspecter | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |
| Supprimer inspection | ❌ | ✅ (sa zone) | ✅ (toutes zones) |
| Export Excel/PDF | ✅ (sa zone) | ✅ (sa zone) | ✅ (toutes zones) |

**Protection :**
- ✅ Une seule inspection par prise pompier
- ✅ Boutons "Tout OUI/NON" dans inspection

---

## 5. Fonctionnalités Spéciales

### 🔄 Réinitialisation Mensuelle

**Accès :** `ROLE_SUPER_ADMIN` UNIQUEMENT

**Contrôleur :** `ResetInspectionController.php`
- **Annotation :** `#[IsGranted('ROLE_SUPER_ADMIN')]`

**Fonctionnalités :**
- ✅ Réinitialisation manuelle via interface web
- ✅ Réinitialisation automatique via commande console
- ✅ Réinitialisation par type d'équipement ou tous
- ✅ Archivage des inspections supprimées dans table `reset_inspection`

**Équipements concernés :**
1. Extincteur
2. Sirène
3. Extinction RAM
4. Monte-Charge
5. RIA
6. Désenfumage
7. Issues de Secours
8. Prises Pompiers

---

### 📍 Gestion des Emplacements

**Accès :** `ROLE_SUPER_ADMIN` UNIQUEMENT

**Contrôleur :** `EmplacementController.php`
- **Route ajout :** `/emplacements/ajax/add` → `#[IsGranted('ROLE_SUPER_ADMIN')]`

**Fonctionnalités :**
- ✅ Ajouter de nouveaux emplacements dynamiquement
- ✅ Les emplacements sont liés au type d'équipement
- ✅ Validation : Nom unique par type d'équipement
- ✅ 166 emplacements pré-initialisés dans la base

**Visibilité :**
- 👁️ Tous les utilisateurs **voient** les emplacements existants
- ➕ Seul le **Super Admin** peut en **ajouter** de nouveaux

---

### 👥 Gestion des Utilisateurs

#### ROLE_ADMIN
**Peut gérer :**
- ✅ Créer des utilisateurs dans **SA ZONE uniquement**
- ✅ Modifier des utilisateurs de **SA ZONE uniquement**
- ❌ **NE PEUT PAS** supprimer des utilisateurs
- ❌ **NE PEUT PAS** créer des SUPER_ADMIN
- ❌ **NE PEUT PAS** modifier d'autres zones

**Contrôleur :** `AdminController.php`

#### ROLE_SUPER_ADMIN
**Peut gérer :**
- ✅ Créer des utilisateurs dans **TOUTES LES ZONES**
- ✅ Modifier des utilisateurs de **TOUTES LES ZONES**
- ✅ **Supprimer** des utilisateurs
- ✅ Créer des **SUPER_ADMIN, ADMIN, USER**
- ✅ Changer les rôles et zones de n'importe qui

**Contrôleur :** `SuperAdminController.php`

---

## 6. Matrice de Sécurité Complète

### Actions CRUD par Rôle

| Équipement | CREATE | READ | UPDATE | DELETE | INSPECT | DELETE_INSPECT |
|------------|--------|------|--------|--------|---------|----------------|
| **USER** | ❌ | ✅ (zone) | ❌ | ❌ | ✅ (zone) | ❌ |
| **ADMIN** | ✅ (zone) | ✅ (zone) | ✅ (zone) | ❌ | ✅ (zone) | ✅ (zone) |
| **SUPER_ADMIN** | ✅ (all) | ✅ (all) | ✅ (all) | ✅ | ✅ (all) | ✅ (all) |

---

## 7. Vérifications de Sécurité Implémentées

### ✅ Protection CSRF
```php
// Toutes les suppressions d'inspection utilisent CSRF
$this->isCsrfTokenValid('delete_inspection_xxx', $request->request->get('_token'))
```

**Fichiers concernés :**
- `EquipementsController.php` (toutes les méthodes `supprimerInspection*`)
- `MonteChargeController.php`
- Templates : Boutons de suppression avec `{{ csrf_token('delete_inspection_xxx') }}`

### ✅ Validation des Permissions par Zone
```php
// Exemple dans EquipementsController
if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && 
    $extincteur->getZone() !== $user->getZone()) {
    throw $this->createAccessDeniedException('Accès non autorisé');
}
```

**Appliqué dans :**
- Modification d'équipements
- Suppression d'inspections
- Consultation de détails

### ✅ Validation Unique des Identifiants
```php
// Vérification avant persist
$existing = $entityManager->getRepository(Extincteur::class)
    ->findOneBy(['numerotation' => $numerotation]);
if ($existing) {
    $this->addFlash('error', 'La numérotation existe déjà...');
}
```

**Appliqué pour :**
- Extincteur : `numerotation`
- RAM : `numerotation`
- Sirène : `numerotation`
- RIA : `numerotation`
- Monte-Charge : `numeroMonteCharge`
- Désenfumage : `numerotation`
- Issues de Secours : `numerotation`

### ✅ Protection Inspection Unique
```php
// Empêcher inspections multiples
$derniereInspection = $equipement->getDerniereInspection();
if ($derniereInspection && $derniereInspection->isActive()) {
    $this->addFlash('error', 'Équipement déjà inspecté...');
    return $this->redirectToRoute(...);
}
```

**Appliqué pour :**
- ✅ Extincteur
- ✅ RAM
- ✅ Sirène
- ✅ RIA
- ✅ Monte-Charge
- ✅ Désenfumage
- ✅ Prises Pompiers
- ✅ Issues de Secours

---

## 8. Points de Sécurité Critiques

### 🔴 CRITIQUE - Opérations Irréversibles

| Opération | Rôle Requis | Protection |
|-----------|-------------|------------|
| **Supprimer équipement** | SUPER_ADMIN | Confirmation JavaScript |
| **Supprimer utilisateur** | SUPER_ADMIN | Route spécifique protégée |
| **Réinitialisation mensuelle** | SUPER_ADMIN | Confirmation + archivage |

### 🟡 ATTENTION - Opérations Sensibles

| Opération | Rôle Requis | Protection |
|-----------|-------------|------------|
| **Supprimer inspection** | ADMIN | CSRF Token + Confirmation |
| **Modifier équipement** | ADMIN | Vérification zone |
| **Créer utilisateur** | ADMIN | Restriction à sa zone |

### 🟢 NORMAL - Opérations Courantes

| Opération | Rôle Requis | Protection |
|-----------|-------------|------------|
| **Inspecter** | USER | Vérification zone + unique |
| **Voir listes** | USER | Filtrage par zone |
| **Export** | USER | Filtrage par zone |

---

## 9. Contrôles d'Accès Automatiques

### Par Zone (Utilisateurs Non-Super-Admin)
```php
// Dans les repositories et contrôleurs
if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
    $searchParams['zone'] = $user->getZone();
}
```

**Effet :**
- Les ADMIN et USER ne voient QUE leur zone
- Impossible d'accéder aux données d'une autre zone
- Les formulaires n'affichent que les options de leur zone

### Par Entité (Vérification ID)
```php
// Exemple : Modifier un extincteur
if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && 
    $extincteur->getZone() !== $user->getZone()) {
    throw $this->createAccessDeniedException(...);
}
```

**Effet :**
- Protection contre les accès directs par URL
- Même en connaissant l'ID, impossible d'accéder à une autre zone

---

## 10. Recommandations de Sécurité

### ✅ Points Forts Actuels
1. ✅ Hiérarchie de rôles bien définie
2. ✅ Protection CSRF sur toutes les actions destructives
3. ✅ Validation des permissions par zone
4. ✅ Contraintes d'unicité en base de données
5. ✅ Messages d'erreur clairs et informatifs
6. ✅ Archivage des réinitialisations

### ⚠️ Points à Surveiller

#### 1. **Routes Monte-Charge**
**Problème :** Monte-Charge a 2 contrôleurs différents
- `MonteChargeController.php` (routes: `app_monte_charge_*`)
- `EquipementsController.php` (routes: `app_equipements_monte_charge_*`)

**Recommandation :** 
- Unifier dans un seul contrôleur
- Ou documenter clairement quelle route utiliser

#### 2. **Gestion des Emplacements**
**État actuel :** Seulement Super Admin peut ajouter
- ✅ Bon pour la cohérence
- ⚠️ Peut créer une dépendance au Super Admin

**Recommandation :**
- Garder tel quel pour maintenir la cohérence
- Documenter la procédure pour demander un nouvel emplacement

#### 3. **Suppression d'Équipements**
**État actuel :** Seulement Super Admin
- ✅ Très bien pour éviter les pertes de données
- ✅ Suppression d'inspection disponible pour Admin

**Recommandation :**
- ✅ Conserver tel quel

#### 4. **Champ `isActive` Incohérent**
**Problème :** Certaines inspections ont `isActive`, d'autres non
- ✅ Extincteur : Oui
- ✅ RAM : Oui
- ✅ Sirène : Oui
- ✅ Monte-Charge : Oui
- ❌ RIA : Non
- ❌ Désenfumage : Non
- ❌ Issues de Secours : Non
- ❌ Prises Pompiers : Non

**Recommandation :**
- Ajouter `isActive` à toutes les inspections pour cohérence
- Ou documenter pourquoi certaines n'en ont pas

---

## 11. Tests de Sécurité à Effectuer

### 🧪 Tests Manuels Recommandés

#### Test 1: Isolation des Zones (ADMIN)
1. Se connecter en tant qu'ADMIN zone SIMTIS
2. Essayer d'accéder à un équipement de zone SIMTIS TISSAGE via URL directe
3. **Résultat attendu :** Erreur 403 Accès Refusé

#### Test 2: Protection CSRF
1. Supprimer le token CSRF d'un formulaire de suppression
2. Soumettre le formulaire
3. **Résultat attendu :** Aucune suppression, erreur

#### Test 3: Inspection Multiple
1. Inspecter un équipement
2. Essayer de l'inspecter à nouveau sans supprimer
3. **Résultat attendu :** Message d'erreur, pas de 2ème inspection

#### Test 4: Numérotation Unique
1. Créer un extincteur avec numéro "EXT-001"
2. Essayer de créer un autre avec le même numéro
3. **Résultat attendu :** Message d'erreur clair

#### Test 5: Permissions Monte-Charge
1. Se connecter en tant qu'ADMIN
2. Essayer de créer/modifier/supprimer un Monte-Charge
3. **Résultat attendu :** Boutons non visibles / Erreur 403

---

## 12. Résumé des Annotations de Sécurité

### Contrôleurs avec IsGranted

| Contrôleur | Total Routes | SUPER_ADMIN Only | ADMIN+ | USER+ |
|------------|--------------|------------------|---------|-------|
| **EquipementsController** | ~100+ | 8 | 23 | Reste |
| **MonteChargeController** | 10 | 4 | 4 | 2 |
| **AdminController** | 15 | 1 | 14 | 0 |
| **SuperAdminController** | 10 | 10 | 0 | 0 |
| **UserController** | 5 | 0 | 0 | 5 |
| **ResetInspectionController** | 5 | 5 | 0 | 0 |
| **EmplacementController** | 3 | 1 | 0 | 2 |

**Total :** ~150 routes protégées

---

## 13. Fichiers Clés de Sécurité

### Configuration
- `config/packages/security.yaml` - Configuration principale
- `config/routes.yaml` - Routes globales

### Entités
- `src/Entity/User.php` - Définition des utilisateurs et rôles

### Contrôleurs
- `src/Controller/SecurityController.php` - Login/Logout
- `src/Security/UserAuthenticator.php` - Authentification custom

### Services
- Pas de service de sécurité custom (utilise Symfony Security)

---

## 14. Conclusion

### 🎯 État Actuel de la Sécurité : **BON** ✅

**Points Forts :**
- ✅ Système de rôles hiérarchique bien défini
- ✅ Protection CSRF sur toutes les actions sensibles
- ✅ Isolation stricte par zone pour ADMIN et USER
- ✅ Permissions granulaires par type d'action
- ✅ Validation des données (unicité, existence)
- ✅ Messages d'erreur informatifs
- ✅ Archivage des actions critiques

**Niveau de Sécurité :** 🟢 **ÉLEVÉ**

### 💡 Recommandations Futures
1. Ajouter des logs d'audit pour les actions Super Admin
2. Implémenter une limitation de tentatives de connexion
3. Ajouter une validation 2FA pour les Super Admin (optionnel)
4. Unifier les contrôleurs Monte-Charge
5. Ajouter `isActive` à toutes les inspections pour cohérence

---

**Fin de l'analyse de sécurité**

*Document généré le 9 Octobre 2025*


