# TechQueen Local Windows Server Setup Guide

This guide explains how to install and host this application on a local Windows PC (server) in the office, making it accessible to all other devices (laptops, phones, tablets) connected to the same office Wi-Fi network.

---

## 1. Prerequisites on the Windows Server PC

You need to install the following free software on the host Windows computer:

1. **PHP (8.2 or 8.3)**:
   - Download the **Non Thread Safe** zip for Windows from [windows.php.net](https://windows.php.net/download/).
   - Extract it to `C:\php`.
   - Add `C:\php` to your System Environment variables **Path** (so `php` can be run from the command line).
   - In `C:\php`, rename `php.ini-development` to `php.ini`, open it, and enable the following extensions by removing the semicolon `;` at the beginning of their lines:
     - `extension=curl`
     - `extension=fileinfo`
     - `extension=mbstring`
     - `extension=openssl`
     - `extension=pdo_sqlite`
     - `extension=sqlite3`

2. **Composer**:
   - Download and run the Composer-Setup.exe installer from [getcomposer.org](https://getcomposer.org/download/).
   - Follow the installation wizard (it will auto-detect your `php.exe` in `C:\php\php.exe`).

---

## 2. First-Time Setup Instructions

Copy the project folder to the server PC, open the command prompt (cmd) inside the project folder, and run:

```bash
# 1. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 2. Setup configuration file
copy .env.example .env

# 3. Generate secure app key
php artisan key:generate

# 4. Create empty SQLite database file
# (Open PowerShell in the folder and run):
New-Item -ItemType File -Path database\database.sqlite -Force
# (Or on standard cmd run):
type null > database\database.sqlite

# 5. Run migrations & setup tables
php artisan migrate --force

# 6. Connect public storage folder
php artisan storage:link
```

---

## 3. Running with One-Click

To start the server, simply **double-click** the `start-server.bat` file in the project folder.

- A command window will open.
- The default browser will automatically launch to `http://localhost:8000/admin`.
- **Important**: Keep the command prompt window open while you are using the application. Closing it turns off the server.

---

## 4. Connecting Other Devices on the Wi-Fi

When you start `start-server.bat`, the window will output two addresses:
1. **Local Address**: `http://localhost:8000/admin` (only works on the server PC itself).
2. **Office Wi-Fi Address**: `http://192.168.X.Y:8000/admin` (works for all devices connected to the same office Wi-Fi).

To access the app from another device:
1. Ensure the device is connected to the **same office Wi-Fi** as the server PC.
2. Open the browser and enter the **Office Wi-Fi Address** (e.g. `http://192.168.1.50:8000/admin`).

---

## 5. Troubleshooting Wi-Fi Connection (Windows Firewall)

If other devices cannot open the page, the Windows Defender Firewall is likely blocking incoming requests. To allow them:

1. Open the **Windows Start Menu**, search for **Windows Defender Firewall with Advanced Security**, and open it.
2. Click **Inbound Rules** in the left sidebar.
3. Click **New Rule...** in the right sidebar.
4. Select **Port** and click Next.
5. Select **TCP** and enter **8000** under **Specific local ports**, then click Next.
6. Select **Allow the connection** and click Next.
7. Check **Domain**, **Private**, and **Public**, then click Next.
8. Name the rule (e.g., `TechQueen Server (Port 8000)`) and click **Finish**.

Now all other devices on the office network will be able to access the application.
