# Script PowerShell pour la réinitialisation automatique des inspections
# À exécuter via Task Scheduler Windows

# Configuration
$ProjectDir = "C:\Users\DELL\Desktop\MyProject\Hse"
$LogFile = "C:\Users\DELL\Desktop\MyProject\Hse\var\log\reset_inspections.log"

# Créer le dossier de logs s'il n'existe pas
$LogDir = Split-Path $LogFile -Parent
if (!(Test-Path $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force
}

# Fonction de logging
function Write-Log {
    param([string]$Message)
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogEntry = "[$Timestamp] $Message"
    Add-Content -Path $LogFile -Value $LogEntry
    Write-Host $LogEntry
}

# Aller dans le répertoire du projet
Set-Location $ProjectDir

Write-Log "=== Début de la réinitialisation automatique ==="

# Réinitialisation quotidienne des monte-charge
Write-Log "Réinitialisation quotidienne des monte-charge..."
try {
    php bin/console app:reset-inspections monte_charge --reason="Réinitialisation quotidienne automatique" 2>&1 | Add-Content -Path $LogFile
    Write-Log "Réinitialisation quotidienne des monte-charge terminée"
} catch {
    Write-Log "ERREUR lors de la réinitialisation quotidienne: $($_.Exception.Message)"
}

# Vérifier si c'est le premier du mois pour la réinitialisation mensuelle
$CurrentDay = (Get-Date).Day
if ($CurrentDay -eq 1) {
    Write-Log "Premier du mois - Réinitialisation mensuelle de tous les équipements..."
    
    # Réinitialisation mensuelle des extincteurs
    try {
        php bin/console app:reset-inspections extincteur --reason="Réinitialisation mensuelle automatique" 2>&1 | Add-Content -Path $LogFile
        Write-Log "Réinitialisation mensuelle des extincteurs terminée"
    } catch {
        Write-Log "ERREUR lors de la réinitialisation des extincteurs: $($_.Exception.Message)"
    }
    
    # Réinitialisation mensuelle des sirènes
    try {
        php bin/console app:reset-inspections sirene --reason="Réinitialisation mensuelle automatique" 2>&1 | Add-Content -Path $LogFile
        Write-Log "Réinitialisation mensuelle des sirènes terminée"
    } catch {
        Write-Log "ERREUR lors de la réinitialisation des sirènes: $($_.Exception.Message)"
    }
    
    # Réinitialisation mensuelle des extinction RAM
    try {
        php bin/console app:reset-inspections extinction_ram --reason="Réinitialisation mensuelle automatique" 2>&1 | Add-Content -Path $LogFile
        Write-Log "Réinitialisation mensuelle des extinction RAM terminée"
    } catch {
        Write-Log "ERREUR lors de la réinitialisation des extinction RAM: $($_.Exception.Message)"
    }
    
    Write-Log "Réinitialisation mensuelle terminée"
} else {
    Write-Log "Pas le premier du mois - Réinitialisation mensuelle ignorée"
}

Write-Log "=== Fin de la réinitialisation automatique ==="
Write-Log ""
