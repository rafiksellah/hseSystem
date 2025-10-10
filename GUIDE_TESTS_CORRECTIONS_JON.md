# 🧪 Guide de Tests - Corrections des Remarques de Jon

**Date:** 10 Octobre 2025  
**Testeur:** À compléter  
**Version:** 1.0

---

## 📋 Liste des Corrections à Tester

### ✅ Test 1: Monte-charge - Permissions d'ajout/modification/suppression

**Problème signalé:** L'ajout de monte charge = erreur

**Correction appliquée:** Changé les permissions de `ROLE_ADMIN` à `ROLE_SUPER_ADMIN`

**Tests à effectuer:**

1. **En tant que ROLE_USER:**
   - [ ] Aller sur `/monte-charge`
   - [ ] Vérifier que le bouton "Ajouter" n'est PAS visible
   - [ ] **Résultat attendu:** Pas de bouton "Ajouter"

2. **En tant que ROLE_ADMIN:**
   - [ ] Aller sur `/monte-charge`
   - [ ] Vérifier que le bouton "Ajouter" n'est PAS visible
   - [ ] **Résultat attendu:** Pas de bouton "Ajouter" (c'est normal, réservé aux SUPER_ADMIN)

3. **En tant que ROLE_SUPER_ADMIN:**
   - [ ] Aller sur `/monte-charge`
   - [ ] Cliquer sur "Ajouter"
   - [ ] Remplir le formulaire (Type, Zone, Numéro Monte-Charge, etc.)
   - [ ] Soumettre le formulaire
   - [ ] **Résultat attendu:** Monte-charge créé avec succès, message de confirmation
   - [ ] Essayer de modifier un monte-charge existant
   - [ ] **Résultat attendu:** Modification réussie
   - [ ] Essayer de supprimer un monte-charge
   - [ ] **Résultat attendu:** Suppression réussie

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 2: Issue de Secours - Champs Type et Barre Antipanique + Zone

**Problème signalé:** 
- Champ Barre Antipanique: pas le droit de saisir
- Champ Type: pas le droit de saisir
- ZONE: Doit être Saisie + Liste déroulante

**Correction appliquée:** 
- Les champs Type et Barre Antipanique étaient déjà en Type 2 (Saisie + Liste déroulante)
- Changé le champ Zone en Type 2 (Saisie + Liste déroulante)

**Tests à effectuer:**

1. **En tant que ROLE_ADMIN:**
   - [ ] Aller sur `/issues-secours/nouveau`
   - [ ] **Tester le champ ZONE:**
     - [ ] Cliquer sur le champ Zone
     - [ ] Vérifier qu'on peut SAISIR du texte libre
     - [ ] Vérifier qu'on peut aussi CHOISIR dans la liste déroulante
     - [ ] **Résultat attendu:** Fonctionnalité Type 2 active
   
   - [ ] **Tester le champ TYPE:**
     - [ ] Cliquer sur le champ Type
     - [ ] Vérifier qu'on peut SAISIR du texte libre
     - [ ] Vérifier qu'on peut aussi CHOISIR dans la liste (Porte simple, Porte double, etc.)
     - [ ] **Résultat attendu:** Champ modifiable en saisie ET liste
   
   - [ ] **Tester le champ BARRE ANTIPANIQUE:**
     - [ ] Cliquer sur le champ Barre Antipanique
     - [ ] Vérifier qu'on peut SAISIR du texte libre
     - [ ] Vérifier qu'on peut aussi CHOISIR dans la liste (Oui, Non, Fonctionnel, etc.)
     - [ ] **Résultat attendu:** Champ modifiable en saisie ET liste

2. **Créer une Issue de Secours:**
   - [ ] Remplir tous les champs avec des valeurs personnalisées
   - [ ] Soumettre le formulaire
   - [ ] **Résultat attendu:** Issue de Secours créée avec les valeurs saisies

3. **Modifier une Issue de Secours:**
   - [ ] Aller sur la page de modification
   - [ ] Vérifier que les 3 champs (Zone, Type, Barre Antipanique) sont modifiables
   - [ ] **Résultat attendu:** Tous les champs sont modifiables

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 3: SIRENE - Zone en Saisie + Liste déroulante

**Problème signalé:** ZONE doit être Saisie + Liste déroulante

**Correction appliquée:** Changé le champ Zone de SELECT simple à Type 2

**Tests à effectuer:**

1. **Ajouter une Sirène:**
   - [ ] Aller sur `/sirenes/nouveau`
   - [ ] Cliquer sur le champ Zone
   - [ ] Taper une zone personnalisée (ex: "ZONE TEST 123")
   - [ ] **Résultat attendu:** Le texte est saisi
   - [ ] Effacer et cliquer sur la liste déroulante
   - [ ] Sélectionner une zone de la liste
   - [ ] **Résultat attendu:** La zone est sélectionnée
   - [ ] Créer la sirène
   - [ ] **Résultat attendu:** Sirène créée avec succès

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 4: SIRENE - Permissions de Suppression

**Problème signalé:** "J'AI PAS LE DROIT DE SUPPRIMER UN ENREGISTREMENT"

**Note:** La configuration actuelle est CORRECTE selon l'analyse de sécurité:
- ROLE_ADMIN: Peut supprimer les INSPECTIONS
- ROLE_SUPER_ADMIN: Peut supprimer les ÉQUIPEMENTS

**Tests à effectuer:**

1. **En tant que ROLE_ADMIN:**
   - [ ] Aller sur les détails d'une Sirène qui a une inspection
   - [ ] Dans l'historique des inspections, chercher le bouton "Supprimer" (icône poubelle)
   - [ ] Cliquer sur le bouton "Supprimer" d'une inspection
   - [ ] **Résultat attendu:** L'INSPECTION est supprimée (pas l'équipement)

2. **En tant que ROLE_SUPER_ADMIN:**
   - [ ] Aller sur la liste des Sirènes
   - [ ] Cliquer sur une Sirène
   - [ ] Vérifier qu'il y a un bouton pour supprimer l'ÉQUIPEMENT complet
   - [ ] **Résultat attendu:** L'équipement complet peut être supprimé

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 5: DESENFUMAGE - Zone en Saisie + Liste déroulante

**Problème signalé:** ZONE doit être Saisie + Liste déroulante

**Correction appliquée:** Changé le champ Zone de SELECT hardcodé à Type 2 dynamique

**Tests à effectuer:**

1. **Ajouter un Désenfumage:**
   - [ ] Aller sur `/desenfumage/nouveau`
   - [ ] Cliquer sur le champ Zone
   - [ ] Taper une zone personnalisée
   - [ ] **Résultat attendu:** Le texte est saisi
   - [ ] Vérifier qu'on peut aussi choisir dans la liste
   - [ ] **Résultat attendu:** Liste déroulante fonctionnelle
   - [ ] Créer le désenfumage
   - [ ] **Résultat attendu:** Désenfumage créé avec succès

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 6: DESENFUMAGE - Permissions de Suppression

**Problème signalé:** "J'AI PAS LE DROIT DE SUPPRIMER UN ENREGISTREMENT"

**Configuration:** Même logique que Sirène
- ADMIN: Supprime les inspections
- SUPER_ADMIN: Supprime les équipements

**Tests à effectuer:**

1. **En tant que ROLE_ADMIN:**
   - [ ] Supprimer une inspection de désenfumage
   - [ ] **Résultat attendu:** Inspection supprimée

2. **En tant que ROLE_SUPER_ADMIN:**
   - [ ] Supprimer un équipement désenfumage complet
   - [ ] **Résultat attendu:** Équipement supprimé

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 7: RAM - Permissions de Suppression

**Problème signalé:** "J'AI PAS LE DROIT DE SUPPRIMER UN ENREGISTREMENT"

**Configuration:** Même logique
- ADMIN: Supprime les inspections
- SUPER_ADMIN: Supprime les équipements

**Tests à effectuer:**

1. **En tant que ROLE_ADMIN:**
   - [ ] Aller sur les détails d'un RAM avec inspection
   - [ ] Supprimer l'inspection
   - [ ] **Résultat attendu:** Inspection supprimée

2. **En tant que ROLE_SUPER_ADMIN:**
   - [ ] Supprimer un équipement RAM complet
   - [ ] **Résultat attendu:** Équipement supprimé

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 8: SELECT ALL - Problème de blocage après sélection

**Problème signalé:** 
"Quand je clique sur select all et je veux enlever un cochage et le remplacer par non il me bloque (dans Extincteur je peux consulter les résultats d'inspection + quand je clique sur select je peux enlever un cochage de oui à non)"

**Correction appliquée:** Ajouté des event listeners sur les radio buttons pour tous les équipements (RIA, Désenfumage, Issues de Secours, Prises Pompiers)

**Tests à effectuer:**

1. **Test RIA:**
   - [ ] Aller sur l'inspection d'un RIA
   - [ ] Cliquer sur "Tout OUI"
   - [ ] **Résultat attendu:** Tous les critères passent à OUI
   - [ ] Essayer de changer MANUELLEMENT un critère de OUI à NON
   - [ ] **Résultat attendu:** Le changement fonctionne (pas de blocage)
   - [ ] Cliquer sur "Tout NON"
   - [ ] **Résultat attendu:** Tous les critères passent à NON
   - [ ] Essayer de changer MANUELLEMENT un critère de NON à OUI
   - [ ] **Résultat attendu:** Le changement fonctionne

2. **Test Désenfumage:**
   - [ ] Même test que pour RIA
   - [ ] **Résultat attendu:** Sélection/désélection fonctionne après "Tout OUI/NON"

3. **Test Issues de Secours:**
   - [ ] Même test que pour RIA
   - [ ] **Résultat attendu:** Sélection/désélection fonctionne après "Tout OUI/NON"

4. **Test Prises Pompiers:**
   - [ ] Même test que pour RIA
   - [ ] **Résultat attendu:** Sélection/désélection fonctionne après "Tout OUI/NON"

5. **Comparaison avec Extincteur (référence):**
   - [ ] Faire le même test avec un Extincteur
   - [ ] Vérifier que le comportement est IDENTIQUE
   - [ ] **Résultat attendu:** Comportement identique pour tous les équipements

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

### ✅ Test 9: PRISE POMPIER - Zone en Saisie + Liste déroulante

**Problème signalé:** "ZONE IL DOIT ETRE Saisie + Liste déroulante"

**Correction appliquée:** Changé le champ Zone en Type 2 dans nouveau.html.twig et modifier.html.twig

**Tests à effectuer:**

1. **Ajouter une Prise Pompier:**
   - [ ] Aller sur `/prises-pompiers/nouveau`
   - [ ] Cliquer sur le champ Zone
   - [ ] Taper une zone personnalisée
   - [ ] **Résultat attendu:** Saisie libre fonctionne
   - [ ] Vérifier la liste déroulante
   - [ ] **Résultat attendu:** Liste déroulante fonctionne
   - [ ] Créer la prise pompier
   - [ ] **Résultat attendu:** Création réussie

2. **Modifier une Prise Pompier:**
   - [ ] Modifier une prise existante
   - [ ] Changer la zone avec saisie libre
   - [ ] **Résultat attendu:** Modification réussie

**Status:** ⬜ Non testé / ✅ Réussi / ❌ Échoué

**Notes:**
```
_______________________________________________________
_______________________________________________________
```

---

## 🎯 Résumé des Tests

| Test | Équipement | Correction | Status |
|------|-----------|------------|--------|
| 1 | Monte-charge | Permissions SUPER_ADMIN | ⬜ |
| 2 | Issue de Secours | Zone + Type + Barre | ⬜ |
| 3 | Sirène | Zone Type 2 | ⬜ |
| 4 | Sirène | Permissions suppression | ⬜ |
| 5 | Désenfumage | Zone Type 2 | ⬜ |
| 6 | Désenfumage | Permissions suppression | ⬜ |
| 7 | RAM | Permissions suppression | ⬜ |
| 8 | Tous | Select All/Deselect | ⬜ |
| 9 | Prise Pompier | Zone Type 2 | ⬜ |

---

## 📝 Instructions de Test

### Prérequis
1. Avoir 3 comptes de test:
   - Un compte ROLE_USER
   - Un compte ROLE_ADMIN
   - Un compte ROLE_SUPER_ADMIN

2. Base de données avec des données de test pour chaque équipement

### Méthode de Test
1. Pour chaque test, se connecter avec le rôle approprié
2. Suivre les étapes décrites
3. Noter le résultat (✅ ou ❌)
4. En cas d'échec, noter les détails dans la section Notes

### En cas de problème
Si un test échoue:
1. Noter exactement ce qui ne fonctionne pas
2. Faire une capture d'écran si possible
3. Noter le message d'erreur
4. Noter l'URL et le navigateur utilisé

---

## 🔍 Tests Additionnels Demandés

### Test 10: Consultation des détails d'inspection

**Demande:** "je veux consulter les détails d'inspection"

**Note:** Cette fonctionnalité existe déjà pour les Extincteurs. À vérifier/ajouter pour les autres équipements.

**Tests à effectuer:**

Pour chaque équipement:
- [ ] Extincteur: Vérifier la page détails d'inspection
- [ ] RIA: Vérifier la page détails d'inspection
- [ ] Monte-Charge: Vérifier la page détails d'inspection
- [ ] Sirène: Vérifier la page détails d'inspection
- [ ] Désenfumage: Vérifier la page détails d'inspection
- [ ] RAM: Vérifier la page détails d'inspection
- [ ] Issue de Secours: Vérifier la page détails d'inspection
- [ ] Prise Pompier: Vérifier la page détails d'inspection

**Status:** ⬜ Non testé / ⚠️ Fonctionnalité à ajouter

---

### Test 11: Statistiques globales

**Demande:** "statistiques globales (comme le truc de rapports HSE)"

**Note:** Fonctionnalité à développer

**À faire:**
- [ ] Créer une page de statistiques pour tous les équipements
- [ ] Afficher le nombre total d'équipements par type
- [ ] Afficher le taux de conformité global
- [ ] Afficher les inspections du mois
- [ ] Graphiques et tableaux récapitulatifs

**Status:** ⬜ Non testé / ⚠️ Fonctionnalité à ajouter

---

## ✅ Validation Finale

Une fois tous les tests effectués:

- [ ] Tous les tests sont ✅ (réussis)
- [ ] Les problèmes signalés par Jon sont résolus
- [ ] L'application fonctionne correctement
- [ ] Aucune régression détectée

**Date de validation:** _______________

**Validé par:** _______________

**Signature:** _______________

---

**Fin du guide de tests**

