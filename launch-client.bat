@echo off
title TechQueen Desktop Client
echo ===================================================
echo   Starting TechQueen Desktop Client...
echo ===================================================
echo.

set CONFIG_FILE=server_ip.txt

:: Check if config file exists
if not exist "%CONFIG_FILE%" (
    echo [Setup] First-time setup required.
    echo Please enter the IP address of your TechQueen server.
    echo (You can find this IP address on the server PC's terminal window).
    echo.
    set /p SERVER_IP="Enter Server IP Address (e.g., 192.168.1.50): "
    
    :: Save IP to file
    echo %SERVER_IP% > "%CONFIG_FILE%"
    echo.
)

:: Read server IP from file
set /p SERVER_IP=<"%CONFIG_FILE%"
:: Clean any trailing/leading spaces
set SERVER_IP=%SERVER_IP: =%

echo Connecting to TechQueen Server at http://%SERVER_IP%:8000/admin...
echo.

:: Try to launch in Chrome App Mode, fallback to Edge, fallback to default browser
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" --app=http://%SERVER_IP%:8000/admin
) else if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" --app=http://%SERVER_IP%:8000/admin
) else if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" --app=http://%SERVER_IP%:8000/admin
) else (
    start http://%SERVER_IP%:8000/admin
)

exit
