@echo off
REM Script batch pour la réinitialisation automatique des inspections
REM À exécuter via Task Scheduler Windows

REM Configuration
set PROJECT_DIR=C:\Users\DELL\Desktop\MyProject\Hse
set LOG_FILE=%PROJECT_DIR%\var\log\reset_inspections.log

REM Créer le dossier de logs s'il n'existe pas
if not exist "%PROJECT_DIR%\var\log" mkdir "%PROJECT_DIR%\var\log"

REM Aller dans le répertoire du projet
cd /d "%PROJECT_DIR%"

REM Fonction de logging
echo [%date% %time%] === Début de la réinitialisation automatique === >> "%LOG_FILE%"

REM Réinitialisation quotidienne des monte-charge
echo [%date% %time%] Réinitialisation quotidienne des monte-charge... >> "%LOG_FILE%"
php bin/console app:reset-inspections monte_charge --reason="Réinitialisation quotidienne automatique" >> "%LOG_FILE%" 2>&1

REM Vérifier si c'est le premier du mois
for /f "tokens=1" %%a in ('date /t') do set CURRENT_DAY=%%a
if "%CURRENT_DAY:~0,1%"=="1" (
    echo [%date% %time%] Premier du mois - Réinitialisation mensuelle... >> "%LOG_FILE%"
    
    REM Réinitialisation mensuelle des extincteurs
    php bin/console app:reset-inspections extincteur --reason="Réinitialisation mensuelle automatique" >> "%LOG_FILE%" 2>&1
    
    REM Réinitialisation mensuelle des sirènes
    php bin/console app:reset-inspections sirene --reason="Réinitialisation mensuelle automatique" >> "%LOG_FILE%" 2>&1
    
    REM Réinitialisation mensuelle des extinction RAM
    php bin/console app:reset-inspections extinction_ram --reason="Réinitialisation mensuelle automatique" >> "%LOG_FILE%" 2>&1
    
    echo [%date% %time%] Réinitialisation mensuelle terminée >> "%LOG_FILE%"
) else (
    echo [%date% %time%] Pas le premier du mois - Réinitialisation mensuelle ignorée >> "%LOG_FILE%"
)

echo [%date% %time%] === Fin de la réinitialisation automatique === >> "%LOG_FILE%"
echo. >> "%LOG_FILE%"
