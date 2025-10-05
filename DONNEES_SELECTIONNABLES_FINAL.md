# 📊 DONNÉES SÉLECTIONNABLES - CONFIGURATION FINALE

**Date** : 5 Octobre 2025

---

## ✅ SIRÈNES - Données Sélectionnables

### 🗂️ **ZONES** (22 zones)
```
1ER ETAGE STARSS
2EME ETAGE EMBALLAGE
3EME ETAGE CHALES ET FOULARDS
ADMINISTRATION
BRODERIE
BUREAU DES ETUDES
CALANDRE
CHAUFFERIE
CONFECTION DECATHLON
DIMANTINE
ENTREE ADMINISTRATION
GRATTAGE
IMPRESSION NUMERIQUE
LIVRAISON
PREPARATION
RAM
ROTATIVE
ROULAGE
SIMI
STOCK DECATHLON
STOCK PF
TEINTURE
```

### 📍 **EMPLACEMENTS** (40 emplacements)
```
En face montecharge N°2
Montecharge N°2
Au milieu
CANTINE FEMME
Au fond entre issue de secours et monte charge auxiliaire
Au dessus de l'issue de secours BED2
Issue de secours T4
Issue de secours T5
A coté de l'issue de secours T6
Atelier mécanique
Montecharge N°5
En face chaine Serviette
En face de table de lancement
Issue de secours ST.D 02
Au dessus de table de coupe
Porte Montecharge N°5
Porte Montecharge N°6
ISSUE DE SECOURS ( cote escalier)
Entre les machine sde rasage 1 et 2
Au milieu de mur de séparation avec DIMANTINE
A coté W.C
SORTIE RAM SUR DALLE
SORTIE RAM
Au dessus machine biancalani
RAM 3
RAM 2 SUR DALLE
A l'entrée cuisine rotative
AU DESSUS DES ADOUCISSEURS
DMS1
MACHINES TG
MACHINE SCV
CUISINE COLORANT
A COTE LABO
CANTINE HOMME
Monte charge N°5
En face porte sectionneur N°1
Au milieu du stock
A coté Magasin PDR
A coté Monte charge auxiliaire
MACHINE TEINTURE DE SOIE
```

### 🔧 **TYPE**
```
Sirène
Diffuseur sonore
```

### 📝 **NUMÉROTATION**
```
Saisie manuelle libre (Ex: SIR-001, SIR-002, etc.)
```

---

## ✅ ISSUES DE SECOURS - Données Sélectionnables

### 🗂️ **ZONES** (18 zones)
```
GRATTAGE
CONFECTION DECATHLON
STOCK DECATHLON
BRODERIE
PREPARATION
BUREAU D'ETUDES
NUMERIQUE
TEINTURE
SIMI
SIMI - CANTINE
RAM
1ER ETAGE STARSS
2EME ETAGE EMBALLAGE
3EME ETAGE CHALES ET FOULARDS
STOCK PF
BRODERIE - VESTIAIRE FEMME
DIAMANTINE
ADMINISTRATION
```

### 🔢 **NUMÉROTATIONS** (37 numérotations prédéfinies)
```
G01, G02                          (GRATTAGE)
CF.D 01, CF.D 02                  (CONFECTION DECATHLON)
ST.D 01, ST.D 02, ST.D 03, ST.D 04 (STOCK DECATHLON)
BR 01, BR 02, BR 03               (BRODERIE)
PR01, PR02                        (PREPARATION)
BE 01, BE 02                      (BUREAU D'ETUDES)
N01, N02, N03, N04                (NUMERIQUE)
T01, T02, T03, T04, T05, T06      (TEINTURE)
S01, S02                          (SIMI)
RAM 01, RAM 02                    (RAM)
ST.PF 01, ST.PF 02                (STOCK PF)
EMB 01, EMB 02                    (2EME ETAGE EMBALLAGE)
CF 01, CF 02                      (3EME ETAGE CHALES ET FOULARDS)
CP 01                             (STOCK PF)
DIAM 01                           (DIAMANTINE)
ADM 01, ADM 02                    (ADMINISTRATION)
```

### 🚪 **TYPE**
```
Coupe feu
Issue sans porte
Porte normale
Porte de passage transparente
```

### 🔒 **BARRE ANTIPANIQUE**
```
OK  (ok)
NOK (Nok)
N/A (NA)
```

---

## 📋 RÉCAPITULATIF DES CHAMPS

### Sirène - Formulaire Nouveau
```
[SELECT] Zone (22 options)
[INPUT]  Numérotation (saisie libre)
[SELECT] Emplacement (40 options)
[SELECT] Type (2 options)
```

### Issues de Secours - Formulaire Nouveau
```
[SELECT] Zone (18 options)
[SELECT] Numérotation (37 options prédéfinies)
[SELECT] Type (4 options)
[SELECT] Barre Antipanique (3 options)
```

---

## ✅ MODIFICATIONS APPLIQUÉES

### Fichiers Modifiés
```
✅ src/Entity/Sirene.php
   - Ajout const ZONES_SIRENE (22 zones)
   - Ajout const EMPLACEMENTS_SIRENE (40 emplacements)
   - Ajout const TYPES_SIRENE (2 types)

✅ src/Entity/IssueSecours.php
   - Ajout const ZONES_ISSUES (18 zones)
   - Ajout const NUMEROTATIONS_ISSUES (37 numérotations)
   - Modification const TYPES_ISSUES (4 types)
   - Modification const ETAT_BARRE (3 états)

✅ templates/equipements/sirenes/nouveau.html.twig
   - Champs Zone, Emplacement, Type en SELECT

✅ templates/equipements/issues_secours/nouveau.html.twig
   - TOUS les champs en SELECT (Zone, Numérotation, Type, Barre Antipanique)

✅ src/Controller/EquipementsController.php
   - Passage des constantes aux templates
```

---

## 🎯 UTILISATION

### Ajouter une Sirène
1. Sélectionner la **ZONE** (dropdown 22 options)
2. Saisir la **Numérotation** (texte libre)
3. Sélectionner l'**Emplacement** (dropdown 40 options)
4. Sélectionner le **Type** (Sirène / Diffuseur sonore)

### Ajouter une Issue de Secours
1. Sélectionner la **ZONE** (dropdown 18 options)
2. Sélectionner la **Numérotation** (dropdown 37 options)
3. Sélectionner le **Type** (dropdown 4 options)
4. Sélectionner la **Barre Antipanique** (dropdown 3 options)

---

## 🎉 TOUT EST CONFORME AUX IMAGES !

Toutes les données des images ont été extraites et configurées comme champs sélectionnables ! ✅

