# Setup Checklist - Thesis System

Use this checklist to ensure everything is properly configured.

## Pre-Setup Checklist

- [ ] Laragon is installed
- [ ] Laragon is running (Start All clicked)
- [ ] MySQL service is running (green indicator in Laragon)
- [ ] PHP is accessible from command line (`php --version` works)
- [ ] Node.js is installed (`node --version` works)
- [ ] npm is installed (`npm --version` works)

## Database Setup Checklist

- [ ] Database `thesis_system` is created
- [ ] Database uses `utf8mb4_unicode_ci` collation
- [ ] Backend `.env` file is configured with MySQL settings
- [ ] Laravel cache is cleared (`php artisan config:clear`)
- [ ] Migrations are run successfully (`php artisan migrate:fresh --seed`)
- [ ] Admin user exists (check with `php artisan tinker`)

## Backend Setup Checklist

- [ ] Composer dependencies are installed (`composer install`)
- [ ] `.env` file exists (copy from `.env.example` if needed)
- [ ] APP_KEY is generated (`php artisan key:generate`)
- [ ] Storage is linked (`php artisan storage:link`)
- [ ] Backend server starts without errors (`php artisan serve`)
- [ ] API is accessible at http://localhost:8000

## Frontend Setup Checklist

- [ ] npm dependencies are installed (`npm install`)
- [ ] `.env` file is configured (if needed)
- [ ] Frontend starts without errors (`npm start`)
- [ ] Frontend is accessible at http://localhost:3000
- [ ] Frontend can connect to backend API

## Functionality Checklist

- [ ] Can access login page
- [ ] Can login with admin account (admin@thesisconnect.com / admin123)
- [ ] Dashboard loads correctly
- [ ] Can view theses list
- [ ] Can view thesis details
- [ ] Can upload new thesis (as admin/faculty)
- [ ] Can approve/reject thesis (as admin/librarian)
- [ ] Can manage users (as admin)
- [ ] Can view activity logs
- [ ] Can view analytics

## Database Verification Commands

Run these in `php artisan tinker`:

```php
// Check database connection
DB::connection()->getPdo();
echo "Connected!";

// Check tables exist
DB::select('SHOW TABLES');

// Check admin user
User::where('email', 'admin@thesisconnect.com')->first();

// Check roles
Role::all();

// Check categories
Category::all();

// Check theses count
Thesis::count();
```

## Common Issues and Solutions

### Issue: "Can't connect to MySQL server"
**Solution:**
1. Open Laragon
2. Click "Start All"
3. Wait for MySQL to show green
4. Try again

### Issue: "Unknown database 'thesis_system'"
**Solution:**
1. Open HeidiSQL from Laragon
2. Create database: thesis_system
3. Run: `php artisan migrate:fresh --seed`

### Issue: "Class 'PDO' not found"
**Solution:**
1. Enable PDO extension in php.ini
2. Uncomment: `extension=pdo_mysql`
3. Restart Laragon

### Issue: "SQLSTATE[42000]: Syntax error"
**Solution:**
1. Clear cache: `php artisan config:clear`
2. Check database collation is utf8mb4_unicode_ci
3. Run migrations again

### Issue: "Port 8000 already in use"
**Solution:**
Use different port: `php artisan serve --port=8001`
Update frontend API URL accordingly

### Issue: "npm ERR! code ELIFECYCLE"
**Solution:**
1. Delete node_modules folder
2. Delete package-lock.json
3. Run: `npm install`
4. Try: `npm start` again

## File Permissions (if needed)

If you encounter permission errors:

```bash
# In backend directory
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Environment Variables

Verify these in `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_system
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
```

## Test Accounts

After seeding, these accounts should work:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@thesisconnect.com | admin123 |
| Librarian | librarian@thesisconnect.com | librarian123 |
| Faculty | faculty@thesisconnect.com | faculty123 |
| Student | student@thesisconnect.com | student123 |

## Next Steps After Setup

1. [ ] Login as admin
2. [ ] Review existing categories
3. [ ] Add new categories if needed
4. [ ] Review pending theses
5. [ ] Test approval workflow
6. [ ] Create test users
7. [ ] Upload sample theses
8. [ ] Test search functionality
9. [ ] Test download functionality
10. [ ] Review activity logs

## Maintenance Commands

```bash
# Backup database
mysqldump -u root thesis_system > backup.sql

# Restore database
mysql -u root thesis_system < backup.sql

# Reset database (WARNING: Deletes all data!)
php artisan migrate:fresh --seed

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# View logs
tail -f storage/logs/laravel.log
```

## Support Resources

- **Quick Start Guide:** `QUICK_START.md`
- **MySQL Setup Guide:** `backend/MYSQL_SETUP_GUIDE.md`
- **Laravel Logs:** `backend/storage/logs/laravel.log`
- **Laragon Documentation:** https://laragon.org/docs/

---

**Status:** 
- [ ] Setup Complete
- [ ] All Tests Passed
- [ ] Ready for Use

**Date Completed:** _______________

**Notes:**
_______________________________________
_______________________________________
_______________________________________
