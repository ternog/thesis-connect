# Thesis System - MySQL Setup for Laragon
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Thesis System - MySQL Setup for Laragon" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Find MySQL in common Laragon paths
$mysqlPaths = @(
    "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.0.31-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mariadb-10.4.10-winx64\bin\mysql.exe",
    "C:\laragon\bin\mysql\mariadb-10.11.2-winx64\bin\mysql.exe"
)

$mysqlPath = $null
foreach ($path in $mysqlPaths) {
    if (Test-Path $path) {
        $mysqlPath = $path
        break
    }
}

# If not found in predefined paths, search for it
if (-not $mysqlPath) {
    Write-Host "Searching for MySQL in Laragon directory..." -ForegroundColor Yellow
    $mysqlExe = Get-ChildItem -Path "C:\laragon\bin\mysql" -Filter "mysql.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($mysqlExe) {
        $mysqlPath = $mysqlExe.FullName
    }
}

if (-not $mysqlPath) {
    Write-Host "ERROR: MySQL not found in Laragon directory!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please make sure Laragon is installed and MySQL is running." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "You can manually create the database by:" -ForegroundColor Yellow
    Write-Host "1. Open HeidiSQL or phpMyAdmin" -ForegroundColor Yellow
    Write-Host "2. Create database: thesis_system" -ForegroundColor Yellow
    Write-Host "3. Run: php artisan migrate:fresh --seed" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Found MySQL at: $mysqlPath" -ForegroundColor Green
Write-Host ""

# Check if MySQL is running
Write-Host "Checking MySQL connection..." -ForegroundColor Yellow
$testConnection = & $mysqlPath -u root -e "SELECT 1;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Cannot connect to MySQL!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please make sure:" -ForegroundColor Yellow
    Write-Host "1. Laragon is running" -ForegroundColor Yellow
    Write-Host "2. MySQL service is started" -ForegroundColor Yellow
    Write-Host "3. MySQL is accessible without password for root user" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Error details: $testConnection" -ForegroundColor Red
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "MySQL is running!" -ForegroundColor Green
Write-Host ""

# Create database
Write-Host "Creating database 'thesis_system'..." -ForegroundColor Yellow
$createDb = & $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS thesis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to create database!" -ForegroundColor Red
    Write-Host "Error details: $createDb" -ForegroundColor Red
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Database created successfully!" -ForegroundColor Green
Write-Host ""

# Clear Laravel cache
Write-Host "Clearing Laravel cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear

Write-Host ""
Write-Host "Running migrations..." -ForegroundColor Yellow
php artisan migrate:fresh --seed

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR: Migration failed!" -ForegroundColor Red
    Write-Host "Please check the error messages above." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Setup completed successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Database Configuration:" -ForegroundColor Cyan
Write-Host "  Database: thesis_system" -ForegroundColor White
Write-Host "  Host: 127.0.0.1" -ForegroundColor White
Write-Host "  Port: 3306" -ForegroundColor White
Write-Host "  Username: root" -ForegroundColor White
Write-Host "  Password: (empty)" -ForegroundColor White
Write-Host ""
Write-Host "Default Admin Account:" -ForegroundColor Cyan
Write-Host "  Email: admin@thesisconnect.com" -ForegroundColor White
Write-Host "  Password: admin123" -ForegroundColor White
Write-Host ""
Write-Host "You can now start the Laravel server with:" -ForegroundColor Yellow
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
