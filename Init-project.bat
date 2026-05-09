@echo off

cd /d "%~dp0"
start cmd /k "php artisan serve"
start cmd /k "npm run dev"
