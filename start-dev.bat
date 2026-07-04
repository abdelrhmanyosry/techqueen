@echo off
set PATH=D:\laragon\bin\php\php-8.5.8-nts;D:\laragon\bin\composer;%PATH%
title TechQueen Local Development Server
echo ===================================================
echo   Starting TechQueen Development Server...
echo ===================================================
echo.
npx concurrently -c "#93c5fd,#c4b5fd,#fd97af,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1 --timeout=0" "php artisan schedule:work" "npm run dev" --names=server,queue,backup,vite --kill-others
pause
