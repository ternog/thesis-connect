@echo off
echo ========================================
echo Thesis System - MySQL Setup for Laragon
echo ========================================
echo.

REM Find MySQL in common Laragon paths
set MYSQL_PATH=
if exist "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" set MYSQL_PATH=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe
if exist "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" set MYSQL_PATH=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe
if exist "C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe" set MYSQL_PATH=C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe
if exist "C:\laragon\bin\mysql\mariadb-10.4.10-winx64\bin\mysql.exe" set MYSQL_PATH=C:\laragon\bin\mysql\mariadb-10.4.10-winx64\bin\mysql.exe

if "%MYSQL_PATH%"=="" (
    echo ERROR: MySQL not found in Laragon directory!
    echo Please make sure Laragon is installed and MySQL is running.
    echo.
    echo You can manually create the database by:
    echo 1. Open HeidiSQL or phpMyAdmin
    echo 2. Create database: thesis_system
    echo 3. Run: php artisan migrate:fresh --seed
    pause
    exit /b 1
)

echo Found MySQL at: %MYSQL_PATH%
echo.

REM Check if MySQL is running
echo Checking MySQL connection...
"%MYSQL_PATH%" -u root -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    echo ERROR: Cannot connect to MySQL!
    echo Please make sure:
    echo 1. Laragon is running
    echo 2. MySQL service is started
    echo 3. MySQL is accessible without password for root user
    echo.
    pause
    exit /b 1
)

echo MySQL is running!
echo.

REM Create database
echo Creating database 'thesis_system'...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS thesis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo ERROR: Failed to create database!
    pause
    exit /b 1
)

echo Database created successfully!
echo.

REM Clear Laravel cache
echo Clearing Laravel cache...
php artisan config:clear
php artisan cache:clear

echo.
echo Running migrations...
php artisan migrate:fresh --seed

if errorlevel 1 (
    echo.
    echo ERROR: Migration failed!
    echo Please check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Database: thesis_system
echo Host: 127.0.0.1
echo Port: 3306
echo Username: root
echo Password: (empty)
echo.
echo Default Admin Account:
echo Email: admin@thesisconnect.com
echo Password: admin123
echo.
echo You can now start the Laravel server with:
echo php artisan serve
echo.
pause
