# 📋 Corrections Formulaires - Spécification Finale

## 🎯 Règle Universelle

**SEULS Zone et Emplacement restent en listes déroulantes**  
**TOUS les autres champs deviennent en saisie libre** (même pour Super Admin)

## 📊 État Actuel vs État Souhaité

### ✅ **RIA** (Déjà conforme)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numéro | Saisie libre | Saisie libre | ✅ |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Agent Extincteur | Saisie libre | Saisie libre | ✅ |
| Diamètre | Saisie libre | Saisie libre | ✅ |
| Longueur | Saisie libre | Saisie libre | ✅ |

### 🔄 **EXTINCTEUR** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numérotation | Saisie libre | Saisie libre | ✅ |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| **Agent extincteur** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |
| **Type** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |
| **Capacité** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |

### 🔄 **MONTE-CHARGE** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numéro Monte-Charge | Liste déroulante | Saisie libre | 🔄 **À CORRIGER** |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| **Numéro de Porte** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |

### 🔄 **SIRÈNE** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numérotation | Saisie libre | Saisie libre | ✅ |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| **Type** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |

### 🔄 **RAM** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numérotation | Saisie libre | Saisie libre | ✅ |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| Type | Saisie libre | Saisie libre | ✅ |
| Vanne | Saisie libre | Saisie libre | ✅ |

### 🔄 **DÉSENFUMAGE** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numérotation | Saisie libre | Saisie libre | ✅ |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| Type | Saisie libre | Saisie libre | ✅ |
| État Commande | Saisie libre | Saisie libre | ✅ |
| État Skydome | Saisie libre | Saisie libre | ✅ |

### 🔄 **ISSUES DE SECOURS** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Numérotation | Liste déroulante | Saisie libre | 🔄 **À CORRIGER** |
| Zone | Liste déroulante | Liste déroulante | ✅ |
| **Type** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |
| **Barre Antipanique** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |

### 🔄 **PRISES POMPIERS** (À corriger)
| Champ | Actuel | Souhaité | Status |
|-------|--------|----------|---------|
| Zone | Liste déroulante | Liste déroulante | ✅ |
| Emplacement | Liste déroulante | Liste déroulante | ✅ |
| **Diamètre** | **Liste déroulante** | **Saisie libre** | 🔄 **À CORRIGER** |

---

## 🔧 Corrections à Effectuer

### 1. **EXTINCTEUR** ✅ (Terminé)
- ✅ Agent extincteur : `<select>` → `<input type="text">`
- ✅ Type : `<select>` → `<input type="text">`
- ✅ Capacité : `<select>` → `<input type="text">`

### 2. **MONTE-CHARGE** 🔄 (À faire)
- 🔄 Numéro Monte-Charge : `<select>` → `<input type="text">`
- 🔄 Numéro de Porte : `<select>` → `<input type="text">`

### 3. **SIRÈNE** 🔄 (À faire)
- 🔄 Type : `<select>` → `<input type="text">`

### 4. **ISSUES DE SECOURS** 🔄 (À faire)
- 🔄 Numérotation : `<select>` → `<input type="text">`
- 🔄 Type : `<select>` → `<input type="text">`
- 🔄 Barre Antipanique : `<select>` → `<input type="text">`

### 5. **PRISES POMPIERS** 🔄 (À faire)
- 🔄 Diamètre : `<select>` → `<input type="text">`

---

## 📝 Fonctionnalités à Ajouter

### **Tableaux Récapitulatifs**
- 🔄 **RAM** : Tableau récapitulatif avec colonne "Conforme / Non conforme"
- 🔄 **Sirène** : Tableau récapitulatif avec colonne "Conforme / Non conforme"

### **Suppression d'Inspections**
- 🔄 **RAM** : Bouton suppression inspection
- 🔄 **Sirène** : Bouton suppression inspection
- 🔄 **Désenfumage** : Bouton suppression inspection
- 🔄 **Issues de Secours** : Bouton suppression inspection

### **Monte-Charge**
- 🔄 Validation anti-doublons de numéro
- 🔄 Champ "Nombre de portes" lors de l'ajout

### **RIA**
- 🔄 Problème constaté au niveau des zones (à clarifier)

---

## 🎯 Résultat Final

**Tous les équipements auront :**
- ✅ Zone et Emplacement : Listes déroulantes
- ✅ Tous les autres champs : Saisie libre
- ✅ Même interface pour Admin et Super Admin
- ✅ Données Excel intégrées en base (pas en listes)

**Status**: 🔄 En cours de correction
