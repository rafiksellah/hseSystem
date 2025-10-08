# 📘 Guide d'Utilisation - Nouvelles Fonctionnalités

## 🎯 Nouveautés Implémentées

### 1. 📊 Tableaux Récapitulatifs (RAM et Sirène)

#### Comment y accéder ?

**Pour RAM:**
```
Menu → Équipements → Extinction RAM → Bouton bleu "Récapitulatif"
URL: /equipements/extinction-ram/recapitulatif
```

**Pour Sirène:**
```
Menu → Équipements → Sirènes → Bouton bleu "Récapitulatif"
URL: /equipements/sirenes/recapitulatif
```

#### Qu'est-ce qu'on y voit ?

**Vue tableau complète avec:**
- ✅ Toutes les données (Zone, Emplacement, Type, etc.)
- ✅ **Colonne CONFORMITÉ** en gros et en couleur
- ✅ Filtres: Zone, Numérotation, Conformité
- ✅ Statistiques: Total, Conformes, Non conformes, Non inspectés
- ✅ Coloration des lignes selon statut
- ✅ Boutons d'action (Détails, Inspecter)

**Exemple de vue:**
```
┌─────────────────────────────────────────────────────────┐
│ Total: 8 | Conformes: 5 | Non conformes: 2 | Non: 1    │
├─────────────────────────────────────────────────────────┤
│ N° │ Zone │ Empl. │ Type │ CONFORMITÉ │ Actions        │
├─────────────────────────────────────────────────────────┤
│ 1  │ RAM  │ RAM1  │ CO2  │ ✓ CONFORME │ [👁️] [✓]      │ ← Ligne verte
│ 2  │ RAM  │ RAM2  │ Eau  │ ✗ NON CONF │ [👁️] [✓]      │ ← Ligne rouge
└─────────────────────────────────────────────────────────┘
```

---

### 2. 🗑️ Suppression d'Inspections

#### Équipements concernés
- RAM
- Sirène
- Désenfumage
- Issues de Secours

#### Comment supprimer ?

**Étapes:**
1. Aller sur la page "Détails" d'un équipement
2. Voir le tableau "Historique des Inspections"
3. Dans la colonne "Actions", cliquer sur le bouton rouge 🗑️
4. Confirmer la suppression dans la popup
5. ✅ L'inspection est supprimée

**Important:**
- ⚠️ Seule l'inspection est supprimée
- ✅ L'équipement reste intact
- ✅ Nécessite les droits ROLE_ADMIN minimum

---

### 3. ✅ Monte-Charge Amélioré

#### Nouvelle fonctionnalité: Nombre de Portes
- Nouveau champ lors de la création/modification
- Type: Nombre entier (1 à 10)
- Visible dans les formulaires

#### Validation Anti-Doublons
- ✅ Impossible d'ajouter deux monte-charges avec le même numéro
- ✅ Message d'erreur clair si doublon détecté
- ✅ Contrainte unique en base de données

---

### 4. 🔄 Réinitialisation Corrigée

#### Problème résolu
Avant, quand on réinitialisait, les équipements marqués "Conforme" restaient conformes.

Maintenant: **Remise à zéro complète** pour tous les équipements.

#### Comment utiliser ?

**Commande:**
```bash
# Réinitialiser tous les équipements
php bin/console app:reset-inspections all

# Réinitialiser un type spécifique
php bin/console app:reset-inspections extincteur
```

**Résultat:**
- ✅ Inspections supprimées (pas juste marquées inactives)
- ✅ Statut remis à "Non inspecté"
- ✅ Historique archivé dans la table reset_inspection

---

## 📝 Formulaires Simplifiés

### Règle des Champs

**Pour TOUS les équipements:**

#### Listes Déroulantes (2 seulement)
1. **Zone** → `<select>` classique
2. **Emplacement** → `<select>` classique

#### Saisie Libre (tout le reste)
- Numérotation
- Type
- Agent
- Capacité
- Vanne
- Diamètre
- Barre antipanique
- etc.

### Pourquoi ?

**Avant (problème):**
- Liste déroulante avec 100 numéros d'extincteurs ❌
- Liste déroulante avec 40 numérotations d'issues ❌
- Impossible à utiliser ❌

**Maintenant (solution):**
- Saisie libre pour tous les numéros ✅
- Données Excel directement en base ✅
- Interface simple et rapide ✅

---

## 🎨 Exemples d'Interface

### Formulaire Extincteur
```
┌─────────────────────────────────────┐
│ Numérotation *    [___4___]         │ ← Saisie libre
│ Zone *            [▼ SIMTIS]        │ ← Liste déroulante
│ Emplacement       [▼ Broderie]      │ ← Liste déroulante
│ Agent extincteur  [___CO2___]       │ ← Saisie libre
│ Type              [___Portatif___]  │ ← Saisie libre
│ Capacité          [___6L___]        │ ← Saisie libre
└─────────────────────────────────────┘
```

### Tableau Récapitulatif Sirène
```
┌────────────────────────────────────────────────────────┐
│ Filtres: [Zone ▼] [Numérotation] [Conformité ▼]      │
│ Stats: Total: 15 | ✓ Conf: 12 | ✗ NC: 2 | ? NI: 1   │
├────────────────────────────────────────────────────────┤
│ N° │ Zone │ Empl.│ CONFORMITÉ          │ Actions     │
├────────────────────────────────────────────────────────┤
│ 1  │ SIMI │ ...  │ [✓ CONFORME]        │ [👁️] [✓]   │
│ 2  │ RAM  │ ...  │ [✗ NON CONFORME]    │ [👁️] [✓]   │
│ 3  │ PREP │ ...  │ [? NON INSPECTÉ]    │ [👁️] [✓]   │
└────────────────────────────────────────────────────────┘
```

---

## ✅ Tests Recommandés

### 1. Test Tableaux Récapitulatifs
- [ ] Accéder au récapitulatif RAM
- [ ] Vérifier que la colonne CONFORMITÉ est bien visible
- [ ] Tester les filtres
- [ ] Vérifier les statistiques

### 2. Test Suppression
- [ ] Créer une inspection test
- [ ] La supprimer via le bouton
- [ ] Vérifier que l'équipement reste intact

### 3. Test Formulaires
- [ ] Créer un nouvel extincteur
- [ ] Vérifier: Zone et Emplacement en `<select>`
- [ ] Vérifier: Tous les autres champs en saisie libre

### 4. Test Monte-Charge
- [ ] Créer un monte-charge
- [ ] Tenter de créer un doublon → doit refuser
- [ ] Ajouter le nombre de portes

### 5. Test Réinitialisation
- [ ] Créer des inspections (conformes et non conformes)
- [ ] Lancer: `php bin/console app:reset-inspections all`
- [ ] Vérifier que tout est remis à "Non inspecté"

---

## 🎉 Fonctionnalités Terminées

| Fonctionnalité | Équipements | Statut |
|----------------|-------------|--------|
| Listes déroulantes Zone/Emplacement | Tous | ✅ |
| Saisie libre autres champs | Tous | ✅ |
| Suppression inspections | 4 | ✅ |
| Tableaux récapitulatifs | 2 | ✅ |
| Validation Monte-Charge | 1 | ✅ |
| Réinitialisation corrigée | Tous | ✅ |

**TOTAL: 100% ✅**

---

**Prêt pour la production ! 🚀**

