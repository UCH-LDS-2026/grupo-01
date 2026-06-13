@echo off
REM Script para ejecutar tests desde CLI en Windows
REM Uso: npm test (después de configurar package.json)

cd /d "%~dp0"
C:\xampp\php\php.exe phpunit tests/ --configuration phpunit.xml
