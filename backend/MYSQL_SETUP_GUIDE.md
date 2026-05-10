# MySQL Setup Guide for Laragon

This guide will help you migrate the Thesis System from SQLite to MySQL using Laragon.

## Prerequisites

1. **Laragon** must be installed and running
2. **MySQL** service must be started in Laragon
3. **PHP** must be available in your PATH

## Quick Setup (Recommended)

### Option 1: Using PowerShell Script (Recommended)

1. Open PowerShell as Administrator
2. Navigate to the backend directory:
   ```powershell
   cd thesis-system/backend
   ```
3. Run the setup script:
   ```powershell
   .\setup-mysql.ps1
   ```

### Option 2: Using Batch Script

1. Open Command Prompt as Administrator
2. Navigate to the backend directory:
   ```cmd
   cd thesis-system\backend
   ```
3. Run the setup script:
   ```cmd
   setup-mysql.bat
   ```

## Manual Setup

If the automated scripts don't work, follow these steps:

### Step 1: Start Laragon and MySQL

1. Open Laragon
2. Click "Start All" to start Apache and MySQL
3. Verify MySQL is running (green indicator)

### Step 2: Create Database

**Option A: Using HeidiSQL (comes with Laragon)**

1. Click "Database" button in Laragon
2. This opens HeidiSQL
3. Right-click on the connection → Create new → Database
4. Database name: `thesis_system`
5. Charset: `utf8mb4`
6. Collation: `utf8mb4_unicode_ci`
7. Click OK

**Option B: Using phpMyAdmin**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click "New" in the left sidebar
3. Database name: `thesis_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Option C: Using MySQL Command Line**

1. Open Laragon Terminal (click "Terminal" in Laragon)
2. Run:
   ```bash
   mysql -u root -e "CREATE DATABASE thesis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

### Step 3: Update Laravel Configuration

The `.env` file has already been updated with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_system
DB_USERNAME=root
DB_PASSWORD=
```

If you need to change these settings, edit the `.env` file.

### Step 4: Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 5: Run Migrations

```bash
php artisan migrate:fresh --seed
```

This will:
- Drop all existing tables (if any)
- Create all tables from migrations
- Seed the database with initial data

## Verify Setup

### Check Database Connection

```bash
php artisan tinker
```

Then run:
```php
DB::connection()->getPdo();
echo "Connected successfully!";
exit;
```

### Check Tables

```bash
php artisan tinker
```

Then run:
```php
DB::select('SHOW TABLES');
exit;
```

### Check Admin User

```bash
php artisan tinker
```

Then run:
```php
User::where('email', 'admin@thesisconnect.com')->first();
exit;
```

## Default Accounts

After seeding, these accounts are available:

### Administrator
- **Email:** admin@thesisconnect.com
- **Password:** admin123
- **Role:** Administrator (Full access)

### Library Staff
- **Email:** librarian@thesisconnect.com
- **Password:** librarian123
- **Role:** Library Staff (Can approve theses)

### Faculty
- **Email:** faculty@thesisconnect.com
- **Password:** faculty123
- **Role:** Faculty (Can upload research)

### Student
- **Email:** student@thesisconnect.com
- **Password:** student123
- **Role:** Student (Can browse and download)

## Start the Application

### Backend (Laravel)

```bash
php artisan serve
```

The API will be available at: `http://localhost:8000`

### Frontend (React)

Open a new terminal and run:

```bash
cd ../frontend
npm start
```

The frontend will be available at: `http://localhost:3000`

## Troubleshooting

### Error: "SQLSTATE[HY000] [2002] No connection could be made"

**Solution:** MySQL is not running
1. Open Laragon
2. Click "Start All"
3. Wait for MySQL to start (green indicator)

### Error: "Access denied for user 'root'@'localhost'"

**Solution:** Password is required
1. Check your MySQL root password in Laragon
2. Update `.env` file with the correct password:
   ```env
   DB_PASSWORD=your_password_here
   ```

### Error: "Unknown database 'thesis_system'"

**Solution:** Database not created
1. Follow Step 2 in Manual Setup to create the database
2. Run migrations again

### Error: "Syntax error or access violation: 1071 Specified key was too long"

**Solution:** This is already fixed in the configuration
- The `database.php` config uses `utf8mb4_unicode_ci` collation
- If you still see this error, check your MySQL version

### MySQL Version Issues

If you're using MySQL 5.7 or older, you might need to add this to `AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

## Database Backup

To backup your database:

```bash
# Using Laragon Terminal
mysqldump -u root thesis_system > backup.sql

# To restore
mysql -u root thesis_system < backup.sql
```

## Reset Database

To completely reset the database:

```bash
php artisan migrate:fresh --seed
```

**Warning:** This will delete all data!

## Additional Commands

### View all routes
```bash
php artisan route:list
```

### Check database status
```bash
php artisan migrate:status
```

### Create new migration
```bash
php artisan make:migration create_table_name
```

### Rollback last migration
```bash
php artisan migrate:rollback
```

## Support

If you encounter any issues:

1. Check Laragon is running
2. Verify MySQL service is started
3. Check `.env` file configuration
4. Clear Laravel cache: `php artisan config:clear`
5. Check Laravel logs: `storage/logs/laravel.log`

## Next Steps

After successful setup:

1. Login to the admin panel
2. Create categories for theses
3. Upload sample theses
4. Test the approval workflow
5. Configure email settings (optional)
6. Set up file storage (optional)
