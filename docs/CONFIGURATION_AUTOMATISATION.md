# ⚙️ Configuration de l'Automatisation des Réinitialisations

## 🎯 **Vue d'Ensemble**

Le système de réinitialisation automatique fonctionne selon cette logique :
- **Monte-charge** : Réinitialisation **quotidienne** (tous les jours)
- **Autres équipements** : Réinitialisation **mensuelle** (1er de chaque mois)

## 🖥️ **Configuration Windows (Votre Environnement)**

### 📋 **1. Préparation des Scripts**

#### **Scripts Disponibles :**
- `scripts/reset_inspections.bat` - Script batch (recommandé pour Windows)
- `scripts/reset_inspections.ps1` - Script PowerShell (avancé)
- `scripts/reset_inspections.sh` - Script bash (pour Linux/WSL)

#### **Test du Script :**
```cmd
# Tester le script batch
cd C:\Users\DELL\Desktop\MyProject\Hse
scripts\reset_inspections.bat
```

### 🔧 **2. Configuration du Task Scheduler Windows**

#### **Étape 1 : Ouvrir le Planificateur de Tâches**
1. Appuyez sur `Windows + R`
2. Tapez `taskschd.msc`
3. Appuyez sur `Entrée`

#### **Étape 2 : Créer une Tâche Quotidienne**

**Pour la réinitialisation quotidienne (Monte-charge) :**

1. **Créer une tâche de base :**
   - Clic droit sur "Bibliothèque du Planificateur de tâches"
   - "Créer une tâche de base..."
   - Nom : `HSE - Réinitialisation Quotidienne Monte-charge`

2. **Déclencheur :**
   - "Quotidiennement"
   - Heure de début : `00:00:00` (minuit)
   - Répéter : Tous les jours

3. **Action :**
   - "Démarrer un programme"
   - Programme : `C:\Users\DELL\Desktop\MyProject\Hse\scripts\reset_inspections.bat`

4. **Conditions :**
   - ✅ "Démarrer la tâche seulement si l'ordinateur est sous tension"
   - ✅ "Réveiller l'ordinateur pour exécuter cette tâche"

5. **Paramètres :**
   - ✅ "Autoriser l'exécution de la tâche à la demande"
   - ✅ "Exécuter la tâche dès que possible après un démarrage programmé manqué"

#### **Étape 3 : Créer une Tâche Mensuelle**

**Pour la réinitialisation mensuelle (Autres équipements) :**

1. **Créer une tâche de base :**
   - Nom : `HSE - Réinitialisation Mensuelle Équipements`

2. **Déclencheur :**
   - "Mensuellement"
   - Jour : `1` (premier du mois)
   - Heure : `01:00:00` (1h du matin)

3. **Action :**
   - Même script : `C:\Users\DELL\Desktop\MyProject\Hse\scripts\reset_inspections.bat`

### 🧪 **3. Test de la Configuration**

#### **Test Manuel :**
```cmd
# Exécuter le script manuellement
cd C:\Users\DELL\Desktop\MyProject\Hse
scripts\reset_inspections.bat

# Vérifier les logs
type var\log\reset_inspections.log
```

#### **Test de la Commande Symfony :**
```cmd
# Test en mode simulation
php bin/console app:reset-inspections all --dry-run

# Test réel (attention, cela va réinitialiser)
php bin/console app:reset-inspections monte_charge --reason="Test manuel"
```

### 📊 **4. Surveillance et Logs**

#### **Fichiers de Log :**
- **Emplacement :** `C:\Users\DELL\Desktop\MyProject\Hse\var\log\reset_inspections.log`
- **Contenu :** Historique complet des réinitialisations automatiques

#### **Vérification des Logs :**
```cmd
# Afficher les dernières entrées
tail -f var\log\reset_inspections.log

# Ou avec PowerShell
Get-Content var\log\reset_inspections.log -Tail 20 -Wait
```

#### **Surveillance via l'Interface Web :**
- Allez sur `/admin/reset-inspections/`
- Consultez l'historique des réinitialisations
- Vérifiez les statistiques

### 🔧 **5. Configuration Avancée**

#### **Variables d'Environnement :**
```cmd
# Ajouter PHP au PATH si nécessaire
set PATH=%PATH%;C:\php

# Vérifier que PHP fonctionne
php --version
```

#### **Permissions :**
- Le script doit avoir les permissions d'exécution
- Le dossier `var\log` doit être accessible en écriture

#### **Configuration du Serveur Web :**
```apache
# Si vous utilisez Apache, ajoutez dans .htaccess
<Files "reset_inspections.bat">
    Order allow,deny
    Deny from all
</Files>
```

### 🚨 **6. Dépannage**

#### **Problèmes Courants :**

**Erreur : "php n'est pas reconnu"**
```cmd
# Solution : Ajouter PHP au PATH
set PATH=%PATH%;C:\xampp\php
# Ou installer PHP globalement
```

**Erreur : "Accès refusé au fichier de log"**
```cmd
# Solution : Créer le dossier et donner les permissions
mkdir var\log
icacls var\log /grant Everyone:F
```

**Erreur : "Tâche non exécutée"**
- Vérifiez que l'ordinateur est allumé à l'heure programmée
- Activez "Réveiller l'ordinateur pour exécuter cette tâche"
- Vérifiez les logs du Task Scheduler

#### **Vérification du Task Scheduler :**
1. Ouvrir le Planificateur de tâches
2. Aller dans "Bibliothèque du Planificateur de tâches"
3. Vérifier l'état des tâches HSE
4. Consulter l'historique d'exécution

### 📅 **7. Planification Recommandée**

#### **Horaires Optimaux :**
- **Quotidien :** `00:00` (minuit) - Monte-charge
- **Mensuel :** `01:00` (1h du matin) - Autres équipements

#### **Justification :**
- **Minuit :** Heure de faible activité
- **1h du matin :** Évite les conflits avec la tâche quotidienne
- **Décalage :** Permet de traiter les logs séparément

### 🔄 **8. Alternatives à Windows Task Scheduler**

#### **Option 1 : Service Windows**
```cmd
# Créer un service Windows (avancé)
sc create "HSE Reset Service" binPath="C:\Users\DELL\Desktop\MyProject\Hse\scripts\reset_inspections.bat"
```

#### **Option 2 : WSL (Windows Subsystem for Linux)**
```bash
# Si vous avez WSL installé
crontab -e
# Ajouter :
# 0 0 * * * /mnt/c/Users/DELL/Desktop/MyProject/Hse/scripts/reset_inspections.sh
```

#### **Option 3 : Application Externe**
- **Quartz.NET** (pour .NET)
- **Hangfire** (pour ASP.NET)
- **Cron-like** applications

### 📈 **9. Monitoring et Alertes**

#### **Surveillance Automatique :**
```powershell
# Script de surveillance (PowerShell)
$LogFile = "C:\Users\DELL\Desktop\MyProject\Hse\var\log\reset_inspections.log"
$LastRun = Get-Content $LogFile | Select-Object -Last 1
$Today = Get-Date -Format "yyyy-MM-dd"

if ($LastRun -notlike "*$Today*") {
    # Envoyer une alerte
    Write-Host "ALERTE: Réinitialisation non exécutée aujourd'hui"
}
```

#### **Notifications Email :**
- Intégrer avec un service SMTP
- Envoyer des rapports par email
- Alertes en cas d'erreur

### 🎯 **10. Résumé de Configuration**

#### **Étapes Essentielles :**
1. ✅ **Tester le script** manuellement
2. ✅ **Créer les tâches** dans Task Scheduler
3. ✅ **Configurer les horaires** (quotidien + mensuel)
4. ✅ **Vérifier les permissions** sur les dossiers
5. ✅ **Surveiller les logs** régulièrement

#### **Vérification Finale :**
```cmd
# Test complet
cd C:\Users\DELL\Desktop\MyProject\Hse
scripts\reset_inspections.bat
type var\log\reset_inspections.log
```

---

## 🎉 **Configuration Terminée !**

Votre système de réinitialisation automatique est maintenant configuré et prêt à fonctionner selon vos besoins :
- **Monte-charge** : Réinitialisation quotidienne à minuit
- **Autres équipements** : Réinitialisation mensuelle le 1er à 1h du matin
- **Archivage complet** : Toutes les données sont conservées
- **Surveillance** : Logs et interface de gestion disponibles
