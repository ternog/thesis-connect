# Quick Start Guide - Thesis System with MySQL

## Step 1: Start Laragon

1. **Open Laragon** application
2. Click **"Start All"** button (or press Ctrl+Alt+S)
3. Wait for services to start:
   - Apache should show green
   - MySQL should show green
4. Verify MySQL is running (you should see "MySQL 8.x" in green)

## Step 2: Setup Database

### Automated Setup (Easiest)

Open PowerShell in the backend directory and run:

```powershell
cd thesis-system/backend
.\setup-mysql.ps1
```

This will automatically:
- Find your MySQL installation
- Create the database
- Run all migrations
- Seed initial data

### Manual Setup (If script fails)

1. **Create Database:**
   - In Laragon, click "Database" button (opens HeidiSQL)
   - Right-click → Create new → Database
   - Name: `thesis_system`
   - Charset: `utf8mb4`
   - Collation: `utf8mb4_unicode_ci`
   - Click OK

2. **Run Migrations:**
   ```bash
   cd thesis-system/backend
   php artisan config:clear
   php artisan migrate:fresh --seed
   ```

## Step 3: Start Backend Server

```bash
cd thesis-system/backend
php artisan serve
```

Backend will run at: **http://localhost:8000**

## Step 4: Start Frontend Server

Open a new terminal:

```bash
cd thesis-system/frontend
npm start
```

Frontend will run at: **http://localhost:3000**

## Step 5: Login

Open browser and go to: **http://localhost:3000**

### Admin Account
- **Email:** admin@thesisconnect.com
- **Password:** admin123

### Other Test Accounts
- **Librarian:** librarian@thesisconnect.com / librarian123
- **Faculty:** faculty@thesisconnect.com / faculty123
- **Student:** student@thesisconnect.com / student123

## Troubleshooting

### "Can't connect to MySQL server"
- **Solution:** Start Laragon and click "Start All"
- Make sure MySQL shows green status

### "Unknown database 'thesis_system'"
- **Solution:** Run the setup script again or manually create the database

### "Port 8000 already in use"
- **Solution:** Use a different port: `php artisan serve --port=8001`

### "Port 3000 already in use"
- **Solution:** The frontend will automatically suggest another port

## Database Configuration

The system is configured with these MySQL settings:

```
Host: 127.0.0.1
Port: 3306
Database: thesis_system
Username: root
Password: (empty)
```

If your Laragon uses a different configuration, update `backend/.env` file.

## What's Next?

1. **Browse Theses** - View existing sample theses
2. **Upload Thesis** - Add new thesis documents
3. **Approve Theses** - Use admin account to approve pending submissions
4. **Manage Users** - Add or approve new users
5. **View Analytics** - Check system statistics and reports

## Need Help?

- Check `backend/MYSQL_SETUP_GUIDE.md` for detailed instructions
- Check `backend/storage/logs/laravel.log` for error logs
- Make sure Laragon is running before starting the servers

## Common Commands

```bash
# Clear Laravel cache
php artisan config:clear
php artisan cache:clear

# Reset database (WARNING: Deletes all data!)
php artisan migrate:fresh --seed

# View all routes
php artisan route:list

# Check migration status
php artisan migrate:status
```

---

**Important:** Always start Laragon first before running the backend server!
