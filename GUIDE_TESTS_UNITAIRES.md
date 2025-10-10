# 🧪 Guide des Tests Unitaires - Système HSE

**Date:** 9 Octobre 2025  
**Version:** 1.0

---

## 📋 Table des Matières
1. [Vue d'Ensemble](#vue-densemble)
2. [Structure des Tests](#structure-des-tests)
3. [Exécution des Tests](#exécution-des-tests)
4. [Tests Créés](#tests-créés)
5. [Résultats Attendus](#résultats-attendus)

---

## 1. Vue d'Ensemble

### 📊 Statistiques des Tests

| Catégorie | Fichiers | Tests | Lignes |
|-----------|----------|-------|--------|
| **Entités** | 3 | 25+ | ~350 |
| **Services** | 1 | 4 | ~130 |
| **Sécurité** | 1 | 5 | ~150 |
| **Fonctionnel** | 1 | 4 | ~170 |
| **TOTAL** | **6** | **38+** | **~800** |

### 🎯 Couverture des Tests

- ✅ Tests unitaires pour les entités principales
- ✅ Tests de sécurité et permissions
- ✅ Tests fonctionnels pour les inspections
- ✅ Tests de services métier
- ✅ Tests du système d'emplacements

---

## 2. Structure des Tests

```
tests/
├── bootstrap.php                          # Configuration PHPUnit
├── Entity/                                # Tests des entités
│   ├── UserTest.php                      # 10 tests - Rôles, permissions, zones
│   ├── ExtincteurTest.php                # 7 tests - Création, validation, statuts
│   ├── MonteChargeTest.php               # 6 tests - Constantes, dates
│   └── EmplacementTest.php               # 5 tests - Création, types, zones
├── Service/                               # Tests des services
│   └── ResetInspectionServiceTest.php    # 4 tests - Réinitialisation
├── Security/                              # Tests de sécurité
│   └── RolesAndPermissionsTest.php       # 5 tests - Routes protégées
└── Functional/                            # Tests fonctionnels
    └── InspectionTest.php                # 4 tests - Inspections, statuts
```

---

## 3. Exécution des Tests

### 🚀 Commandes de Base

#### Exécuter TOUS les tests
```bash
php bin/phpunit
```

#### Exécuter un fichier de test spécifique
```bash
# Tests User
php bin/phpunit tests/Entity/UserTest.php

# Tests Extincteur
php bin/phpunit tests/Entity/ExtincteurTest.php

# Tests Sécurité
php bin/phpunit tests/Security/RolesAndPermissionsTest.php
```

#### Exécuter une catégorie de tests
```bash
# Tous les tests d'entités
php bin/phpunit tests/Entity/

# Tous les tests de sécurité
php bin/phpunit tests/Security/

# Tous les tests fonctionnels
php bin/phpunit tests/Functional/
```

#### Exécuter un test spécifique
```bash
php bin/phpunit tests/Entity/UserTest.php --filter testUserRoles
```

### 📊 Options Utiles

```bash
# Avec coverage (nécessite Xdebug)
php bin/phpunit --coverage-html coverage/

# Mode verbeux
php bin/phpunit --verbose

# Afficher seulement les erreurs
php bin/phpunit --testdox

# Stopper au premier échec
php bin/phpunit --stop-on-failure
```

---

## 4. Tests Créés

### 📁 tests/Entity/UserTest.php

**10 tests pour l'entité User**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testUserCreation()` | Création basique | Email, nom, prénom, fullName |
| `testUserRoles()` | Gestion des rôles | ROLE_USER toujours présent |
| `testIsSuperAdmin()` | Méthode isSuperAdmin() | Détection correcte du rôle |
| `testIsAdmin()` | Méthode isAdmin() | Admin ≠ Super Admin |
| `testNeedsZone()` | Zone obligatoire | USER/ADMIN: oui, SUPER: non |
| `testGetDisplayZone()` | Affichage zone | "Toutes zones" pour Super Admin |
| `testCanManageUser()` | Permissions gestion | Zone et rôle |
| `testGetManagedZones()` | Zones gérées | Super: toutes, Admin: sa zone |
| `testUserIdentifier()` | Identifiant | Email comme identifiant |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Entity/UserTest.php
```

**Résultat attendu :**
```
OK (10 tests, 30+ assertions)
```

---

### 📁 tests/Entity/ExtincteurTest.php

**7 tests pour l'entité Extincteur**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testExtincteurCreation()` | Création basique | Tous les champs |
| `testStatutConformiteSansInspection()` | Statut initial | "Non inspecté" |
| `testNombreInspectionsSansInspection()` | Compteur initial | 0 inspections |
| `testValidation()` | Système de validation | Valide, date, validateur |
| `testGetZonesForUser()` | Zones par rôle | Super: 2, Admin: 1 |
| `testDatesExtincteur()` | Dates diverses | Fabrication, épreuve, fin vie |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Entity/ExtincteurTest.php
```

**Résultat attendu :**
```
OK (7 tests, 20+ assertions)
```

---

### 📁 tests/Entity/MonteChargeTest.php

**6 tests pour l'entité MonteCharge**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testMonteChargeCreation()` | Création basique | Numéro, zone, emplacement, porte |
| `testStatutConformiteSansInspection()` | Statut initial | "Non inspecté" |
| `testNumerosMonteChargeConstants()` | Constantes numéros | 10 monte-charges |
| `testNumerosPorteConstants()` | Constantes portes | 4 portes |
| `testZonesConstants()` | Constantes zones | SIMTIS, SIMTIS TISSAGE |
| `testDateCreationAutomatic()` | Date auto | Créée automatiquement |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Entity/MonteChargeTest.php
```

**Résultat attendu :**
```
OK (6 tests, 15+ assertions)
```

---

### 📁 tests/Entity/EmplacementTest.php

**5 tests pour l'entité Emplacement**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testEmplacementCreation()` | Création basique | Nom, type, zone |
| `testEmplacementDateCreationAutomatic()` | Date auto | Créée automatiquement |
| `testEmplacementDateModification()` | Date modification | Nullable, modifiable |
| `testEmplacementZoneOptional()` | Zone optionnelle | Peut être null |
| `testEmplacementTypesEquipement()` | Types valides | 8 types d'équipement |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Entity/EmplacementTest.php
```

**Résultat attendu :**
```
OK (5 tests, 15+ assertions)
```

---

### 📁 tests/Service/ResetInspectionServiceTest.php

**4 tests pour le service de réinitialisation**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testServiceInstantiation()` | Création service | Instance correcte |
| `testResetInspectionsByTypeWithInvalidType()` | Type invalide | Exception levée |
| `testResetInspectionsByTypeValidTypes()` | Types valides | Tous les 8 types |
| `testResetAllInspectionsCallsAllTypes()` | Reset global | 8 types traités |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Service/ResetInspectionServiceTest.php
```

**Résultat attendu :**
```
OK (4 tests, 30+ assertions)
```

---

### 📁 tests/Security/RolesAndPermissionsTest.php

**5 tests de sécurité**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testRoleHierarchy()` | Hiérarchie rôles | SUPER_ADMIN > ADMIN > USER |
| `testAdminRoutesProtection()` | Routes admin | Protégées (302/403) |
| `testSuperAdminRoutesProtection()` | Routes super admin | Protégées (302/403) |
| `testLoginPageIsPublic()` | Page login | Publique (200) |
| `testEquipmentDeletionRequiresSuperAdmin()` | Suppression équipement | SUPER_ADMIN only |
| `testInspectionDeletionRequiresAdmin()` | Suppression inspection | ADMIN+ |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Security/RolesAndPermissionsTest.php
```

**Résultat attendu :**
```
OK (5 tests, 20+ assertions)
```

---

### 📁 tests/Functional/InspectionTest.php

**4 tests fonctionnels pour les inspections**

| Test | Description | Vérifie |
|------|-------------|---------|
| `testExtincteurCanHaveInspection()` | Ajout inspection | Relation, statut "Conforme" |
| `testNonConformeInspectionChangesStatus()` | Inspection non conforme | Statut "Non conforme" |
| `testGetDerniereInspectionOnlyReturnsActive()` | Filtrage inspections | Seulement les actives |
| `testNombreInspections()` | Compteur inspections | Calcul correct |

**Exemple d'exécution :**
```bash
php bin/phpunit tests/Functional/InspectionTest.php
```

**Résultat attendu :**
```
OK (4 tests, 12+ assertions)
```

---

## 5. Résultats Attendus

### ✅ Tous les Tests Réussis

```
PHPUnit 10.x by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.x
Configuration: C:\Users\DELL\Desktop\MyProject\Hse\phpunit.dist.xml

......................................                            38 / 38 (100%)

Time: 00:02.345, Memory: 18.00 MB

OK (38 tests, 120+ assertions)
```

### 🎯 Couverture des Fonctionnalités

| Fonctionnalité | Testée | Fichier |
|----------------|--------|---------|
| **Rôles et hiérarchie** | ✅ | UserTest.php |
| **Permissions par zone** | ✅ | UserTest.php, RolesAndPermissionsTest.php |
| **Gestion utilisateurs** | ✅ | UserTest.php |
| **Création équipements** | ✅ | ExtincteurTest.php, MonteChargeTest.php |
| **Statuts conformité** | ✅ | ExtincteurTest.php, InspectionTest.php |
| **Inspections uniques** | ✅ | InspectionTest.php |
| **Réinitialisation** | ✅ | ResetInspectionServiceTest.php |
| **Emplacements dynamiques** | ✅ | EmplacementTest.php |
| **Routes protégées** | ✅ | RolesAndPermissionsTest.php |
| **Validation données** | ✅ | ExtincteurTest.php |

---

## 6. Interprétation des Résultats

### ✅ Tests Réussis (GREEN)
```
.  →  Test passé avec succès
```
**Action :** Aucune, tout fonctionne correctement

### ❌ Tests Échoués (RED)
```
F  →  Failure (assertion échouée)
E  →  Error (erreur PHP)
```
**Action :** Vérifier le code, corriger le bug

### ⚠️ Tests Ignorés (YELLOW)
```
S  →  Skipped (ignoré)
I  →  Incomplete (incomplet)
```
**Action :** Compléter ou activer le test

---

## 7. Exemples de Résultats

### ✅ Exemple Succès
```bash
PS C:\Users\DELL\Desktop\MyProject\Hse> php bin/phpunit tests/Entity/UserTest.php

PHPUnit 10.5.0 by Sebastian Bergmann and contributors.

..........                                                       10 / 10 (100%)

Time: 00:00.234, Memory: 6.00 MB

OK (10 tests, 35 assertions)
```

### ❌ Exemple Échec
```bash
PS C:\Users\DELL\Desktop\MyProject\Hse> php bin/phpunit tests/Entity/UserTest.php

PHPUnit 10.5.0 by Sebastian Bergmann and contributors.

.........F                                                       10 / 10 (100%)

Time: 00:00.234, Memory: 6.00 MB

There was 1 failure:

1) App\Tests\Entity\UserTest::testIsSuperAdmin
Failed asserting that false is true.

C:\Users\DELL\Desktop\MyProject\Hse\tests\Entity\UserTest.php:45

FAILURES!
Tests: 10, Assertions: 35, Failures: 1.
```

**Comment lire :**
- Ligne `1)` : Nom du test échoué
- Ligne suivante : Raison de l'échec
- Dernière ligne : Fichier et numéro de ligne

---

## 8. Tests Détaillés par Catégorie

### 🧩 Tests d'Entités

#### UserTest.php - Tests des Utilisateurs

**Ce qui est testé :**
1. ✅ Création d'un utilisateur avec tous les champs
2. ✅ Gestion des rôles (USER, ADMIN, SUPER_ADMIN)
3. ✅ Détection des rôles (`isSuperAdmin()`, `isAdmin()`)
4. ✅ Besoin de zone selon le rôle
5. ✅ Affichage de la zone
6. ✅ Permissions de gestion d'autres utilisateurs
7. ✅ Zones gérées par rôle
8. ✅ Identifiant utilisateur

**Assertions importantes :**
```php
// Un utilisateur a toujours ROLE_USER
$this->assertContains('ROLE_USER', $user->getRoles());

// Super Admin n'a pas besoin de zone
$this->assertFalse($superAdmin->needsZone());

// Admin peut gérer seulement sa zone
$this->assertTrue($adminSimtis->canManageUser($userSimtis));
$this->assertFalse($adminSimtis->canManageUser($userTissage));
```

---

#### ExtincteurTest.php - Tests des Extincteurs

**Ce qui est testé :**
1. ✅ Création avec tous les champs (numérotation, zone, agent, type, capacité)
2. ✅ Statut "Non inspecté" par défaut
3. ✅ Nombre d'inspections = 0 par défaut
4. ✅ Système de validation (valide, date, validateur)
5. ✅ Zones disponibles selon le rôle
6. ✅ Gestion des dates (fabrication, épreuve, fin de vie)

**Assertions importantes :**
```php
// Sans inspection, statut = Non inspecté
$this->assertEquals('Non inspecté', $extincteur->getStatutConformite());

// Super Admin voit toutes les zones
$zones = Extincteur::getZonesForUser($superAdmin);
$this->assertCount(2, $zones);
```

---

#### MonteChargeTest.php - Tests des Monte-Charges

**Ce qui est testé :**
1. ✅ Création avec numéro, zone, emplacement, porte
2. ✅ Statut "Non inspecté" par défaut
3. ✅ Constantes : 10 numéros de monte-charge
4. ✅ Constantes : 4 numéros de porte
5. ✅ Constantes : Zones SIMTIS et SIMTIS TISSAGE
6. ✅ Date de création automatique

**Assertions importantes :**
```php
// 10 monte-charges disponibles
$this->assertCount(10, MonteCharge::NUMEROS_MONTE_CHARGE);

// 4 portes maximum
$this->assertCount(4, MonteCharge::NUMEROS_PORTE);

// Date récente (moins de 60 secondes)
$this->assertLessThan(60, $diff);
```

---

#### EmplacementTest.php - Tests des Emplacements

**Ce qui est testé :**
1. ✅ Création avec nom, type d'équipement, zone
2. ✅ Date de création automatique
3. ✅ Date de modification nullable
4. ✅ Zone optionnelle (peut être null)
5. ✅ Validation des 8 types d'équipement

**Assertions importantes :**
```php
// Zone peut être null
$this->assertNull($emplacement->getZone());

// 8 types d'équipement valides
foreach ($typesValides as $type) {
    $this->assertEquals($type, $emplacement->getTypeEquipement());
}
```

---

### 🛠️ Tests de Services

#### ResetInspectionServiceTest.php - Service de Réinitialisation

**Ce qui est testé :**
1. ✅ Instantiation du service
2. ✅ Exception pour type d'équipement invalide
3. ✅ Réinitialisation par type (8 types valides)
4. ✅ Réinitialisation globale (tous les types)

**Assertions importantes :**
```php
// Type invalide lève une exception
$this->expectException(\InvalidArgumentException::class);

// Reset global traite 8 types
$this->assertCount(8, $results);
$this->assertArrayHasKey('extincteur', $results);
```

---

### 🔐 Tests de Sécurité

#### RolesAndPermissionsTest.php - Permissions et Routes

**Ce qui est testé :**
1. ✅ Hiérarchie des rôles dans configuration
2. ✅ Protection des routes admin
3. ✅ Protection des routes super admin
4. ✅ Page de login accessible publiquement
5. ✅ Suppression d'équipements réservée à SUPER_ADMIN
6. ✅ Suppression d'inspections accessible à ADMIN

**Assertions importantes :**
```php
// Hiérarchie correcte
$this->assertContains('ROLE_USER', $roleHierarchy['ROLE_ADMIN']);

// Routes protégées
$this->assertContains($response->getStatusCode(), [302, 403]);

// Login public
$this->assertEquals(200, $response->getStatusCode());
```

---

### ⚙️ Tests Fonctionnels

#### InspectionTest.php - Système d'Inspection

**Ce qui est testé :**
1. ✅ Ajout d'une inspection à un extincteur
2. ✅ Changement de statut après inspection non conforme
3. ✅ `getDerniereInspection()` retourne seulement les actives
4. ✅ Comptage correct du nombre d'inspections

**Assertions importantes :**
```php
// Inspection conforme change le statut
$this->assertEquals('Conforme', $extincteur->getStatutConformite());

// Inspection non conforme
$this->assertEquals('Non conforme', $extincteur->getStatutConformite());

// Seulement inspections actives
$this->assertTrue($derniere->isActive());

// Compteur correct
$this->assertEquals(3, $extincteur->getNombreInspections());
```

---

## 9. Commandes Avancées

### 🔍 Debugging

```bash
# Afficher toutes les assertions
php bin/phpunit --verbose --debug

# Afficher le détail des tests
php bin/phpunit --testdox-text testdox.txt

# Générer un rapport XML (pour CI/CD)
php bin/phpunit --log-junit report.xml
```

### 📈 Coverage (si Xdebug installé)

```bash
# Rapport HTML
php bin/phpunit --coverage-html coverage/

# Rapport texte
php bin/phpunit --coverage-text

# Rapport XML (pour SonarQube, etc.)
php bin/phpunit --coverage-clover coverage.xml
```

### ⚡ Performance

```bash
# Exécution parallèle (si paratest installé)
vendor/bin/paratest

# Uniquement les tests rapides
php bin/phpunit --group fast

# Exclure les tests lents
php bin/phpunit --exclude-group slow
```

---

## 10. Bonnes Pratiques

### ✅ DO (À FAIRE)

1. **Exécuter les tests avant chaque commit**
   ```bash
   php bin/phpunit && git commit
   ```

2. **Écrire un test pour chaque bug corrigé**
   - Reproduire le bug dans un test
   - Corriger le code
   - Vérifier que le test passe

3. **Tester les cas limites**
   - Valeurs null
   - Chaînes vides
   - Tableaux vides
   - Dates invalides

4. **Nommer clairement les tests**
   ```php
   testUserCannotManageUserFromDifferentZone()  // ✅ Clair
   testUser1()                                  // ❌ Pas clair
   ```

### ❌ DON'T (À ÉVITER)

1. **Ne pas tester le framework Symfony**
   - Pas besoin de tester que Doctrine fonctionne
   - Tester VOTRE logique métier uniquement

2. **Ne pas faire de tests dépendants**
   - Chaque test doit être indépendant
   - Utiliser `setUp()` pour préparer les données

3. **Ne pas ignorer les tests échoués**
   - Si un test échoue, corriger immédiatement
   - Ne pas commenter un test qui dérange

---

## 11. Maintenance des Tests

### 🔄 Après Modification du Code

| Modification | Action Test |
|--------------|-------------|
| Ajout d'une entité | Créer `tests/Entity/NouvelleEntiteTest.php` |
| Ajout d'un service | Créer `tests/Service/NouveauServiceTest.php` |
| Ajout d'une route protégée | Ajouter dans `RolesAndPermissionsTest.php` |
| Modification logique métier | Mettre à jour les tests concernés |
| Ajout d'un rôle | Ajouter tests dans `UserTest.php` |

### 📝 Checklist Avant Déploiement

- [ ] Tous les tests passent : `php bin/phpunit`
- [ ] Aucun warning PHPUnit
- [ ] Cache vidé : `php bin/console cache:clear`
- [ ] Base de données à jour : `php bin/console d:m:m`
- [ ] Tests de sécurité OK

---

## 12. Résumé Exécutif

### 📊 Vue d'Ensemble

```
╔════════════════════════════════════════════════════════════╗
║         TESTS UNITAIRES - SYSTÈME HSE                      ║
╠════════════════════════════════════════════════════════════╣
║  Total Tests:        38+                                   ║
║  Total Assertions:   120+                                  ║
║  Fichiers Tests:     6                                     ║
║  Couverture:         Entités, Services, Sécurité           ║
║  Status:             ✅ PRÊT POUR EXÉCUTION                ║
╚════════════════════════════════════════════════════════════╝
```

### 🎯 Objectifs Atteints

- ✅ Tests des entités principales (User, Extincteur, MonteCharge, Emplacement)
- ✅ Tests du service de réinitialisation
- ✅ Tests de sécurité (rôles, routes, permissions)
- ✅ Tests fonctionnels (inspections, statuts)
- ✅ Validation de la logique métier
- ✅ Protection contre les régressions

### 🚀 Pour Commencer

```bash
# 1. Exécuter tous les tests
php bin/phpunit

# 2. Si succès, vous êtes prêt !
# 3. Si échec, vérifier et corriger
```

---

**Fin du guide des tests unitaires**

*Document généré le 9 Octobre 2025*


