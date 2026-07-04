@echo off
set PATH=D:\laragon\bin\php\php-8.5.8-nts;D:\laragon\bin\composer;%PATH%
title TechQueen Local Server
echo ===================================================
echo   Starting TechQueen Local Server...
echo   Please keep this window open while using the app.
echo ===================================================
echo.

:: Detect local IP address on Windows
set LOCAL_IP=127.0.0.1
for /f "tokens=4 delims= " %%i in ('route print ^| findstr 0.0.0.0 ^| findstr /V "127.0.0.1"') do (
    set LOCAL_IP=%%i
)

if "%LOCAL_IP%"=="127.0.0.1" (
    for /f "tokens=2 delims=:" %%a in ('ipconfig ^| find "IPv4"') do set LOCAL_IP=%%a
)

:: Clean spaces
set LOCAL_IP=%LOCAL_IP: =%

echo ---------------------------------------------------
echo   Local Address (This PC): http://localhost:8000/admin
echo   Office Wi-Fi Address:    http://%LOCAL_IP%:8000/admin
echo ---------------------------------------------------
echo.

:: Launch the browser automatically on the server computer
start http://localhost:8000/admin

:: Start PHP artisan server bound to 0.0.0.0 (all network interfaces)
php artisan serve --host=0.0.0.0 --port=8000

pause
