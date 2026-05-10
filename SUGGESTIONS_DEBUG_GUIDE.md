# Suggestions Feature Debug Guide

## Common Issues and Solutions

### Issue: "Failed to load suggestions. Please try again."

This error can occur for several reasons. Follow these steps to diagnose and fix:

### Step 1: Check if the route exists
Run this command in the backend directory:
```bash
php artisan route:list | grep suggestions
```

You should see:
```
GET|HEAD  api/suggestions .... SmartSearchController@suggestions
```

If not found, clear the route cache:
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Step 2: Check database tables exist
Run this SQL query in your database:
```sql
SHOW TABLES LIKE 'thesis_views';
```

If the table doesn't exist, run migrations:
```bash
php artisan migrate
```

### Step 3: Check user has required fields
Run this SQL query:
```sql
DESCRIBE users;
```

Make sure these columns exist:
- program (varchar, nullable)
- interests (json, nullable)
- department (varchar, nullable)

If missing, run:
```bash
php artisan migrate:fresh --seed
```
**WARNING**: This will delete all data!

Or manually add columns:
```sql
ALTER TABLE users ADD COLUMN program VARCHAR(255) NULL AFTER department;
ALTER TABLE users ADD COLUMN interests JSON NULL AFTER program;
```

### Step 4: Check Laravel logs
Check the Laravel log file for detailed errors:
```bash
tail -f storage/logs/laravel.log
```

Then try accessing the suggestions page again and watch for errors.

### Step 5: Test the API directly
Use curl or Postman to test the endpoint:

```bash
# Get your auth token first (login)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use the token to test suggestions
curl -X GET http://localhost:8000/api/suggestions \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Step 6: Check if there are approved theses
The suggestions need approved theses to work. Check:
```sql
SELECT COUNT(*) FROM theses WHERE status = 'approved';
```

If zero, approve some theses or create test data.

### Step 7: Update user profile
Make sure your user has program and interests set:
```sql
UPDATE users 
SET program = 'Computer Science', 
    interests = '["Machine Learning", "Web Development"]',
    department = 'College of Computer Study'
WHERE email = 'your@email.com';
```

### Step 8: Check CORS settings
If you see CORS errors in browser console, update `backend/config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:3000'],
'supports_credentials' => true,
```

### Step 9: Restart servers
Sometimes a simple restart helps:
```bash
# Backend
php artisan serve

# Frontend (in another terminal)
cd frontend
npm start
```

## Quick Fix Script

Run this in your backend directory:
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan migrate
php artisan serve
```

## Testing Checklist

- [ ] Backend server is running (http://localhost:8000)
- [ ] Frontend server is running (http://localhost:3000)
- [ ] User is logged in
- [ ] Database migrations are up to date
- [ ] At least one approved thesis exists
- [ ] User has program/interests set in profile
- [ ] No errors in browser console
- [ ] No errors in Laravel logs

## Still Not Working?

Check the browser console (F12) for detailed error messages. Common errors:

1. **401 Unauthorized**: Token expired or invalid - logout and login again
2. **404 Not Found**: Route not registered - clear caches and restart
3. **500 Server Error**: Check Laravel logs for details
4. **Network Error**: Backend server not running or wrong URL

## Environment Variables

Make sure your frontend `.env` file has:
```
REACT_APP_API_URL=http://localhost:8000/api
```

And backend `.env` has:
```
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
```
