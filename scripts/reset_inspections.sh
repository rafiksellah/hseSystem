#!/bin/bash

# Script de réinitialisation automatique des inspections
# À exécuter via cron pour automatiser les réinitialisations

# Configuration
PROJECT_DIR="C:\Users\DELL\Desktop\MyProject\Hse"  # Chemin Windows
LOG_FILE="C:\Users\DELL\Desktop\MyProject\Hse\var\log\reset_inspections.log"

# Fonction de logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Aller dans le répertoire du projet
cd "$PROJECT_DIR" || exit 1

log "=== Début de la réinitialisation automatique ==="

# Réinitialisation quotidienne des monte-charge
log "Réinitialisation quotidienne des monte-charge..."
php bin/console app:reset-inspections monte_charge --reason="Réinitialisation quotidienne automatique" >> "$LOG_FILE" 2>&1

# Vérifier si c'est le premier du mois pour la réinitialisation mensuelle
if [ "$(date +%d)" = "01" ]; then
    log "Premier du mois - Réinitialisation mensuelle de tous les équipements..."
    
    # Réinitialisation mensuelle des extincteurs
    php bin/console app:reset-inspections extincteur --reason="Réinitialisation mensuelle automatique" >> "$LOG_FILE" 2>&1
    
    # Réinitialisation mensuelle des sirènes
    php bin/console app:reset-inspections sirene --reason="Réinitialisation mensuelle automatique" >> "$LOG_FILE" 2>&1
    
    # Réinitialisation mensuelle des extinction RAM
    php bin/console app:reset-inspections extinction_ram --reason="Réinitialisation mensuelle automatique" >> "$LOG_FILE" 2>&1
    
    log "Réinitialisation mensuelle terminée"
else
    log "Pas le premier du mois - Réinitialisation mensuelle ignorée"
fi

log "=== Fin de la réinitialisation automatique ==="
log ""
