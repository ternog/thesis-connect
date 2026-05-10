# Fix Login Issue - Quick Guide

## Problem
Cannot login with the provided credentials.

## Solution

### Option 1: Reset Database (Recommended)

**Windows:**
```bash
cd thesis-system/backend
reset-and-seed.bat
```

**Mac/Linux:**
```bash
cd thesis-system/backend
chmod +x reset-and-seed.sh
./reset-and-seed.sh
```

**Manual:**
```bash
cd thesis-system/backend
php artisan migrate:fresh --seed
```

### Option 2: Just Run Seeders (If migrations already ran)

```bash
cd thesis-system/backend
php artisan db:seed
```

### Option 3: Create Admin User Manually

```bash
cd thesis-system/backend
php artisan tinker
```

Then paste this:
```php
$adminRole = App\Models\Role::where('name', 'admin')->first();
App\Models\User::create([
    'name' => 'System Administrator',
    'email' => 'admin@thesisconnect.com',
    'password' => Hash::make('admin123'),
    'role_id' => $adminRole->id,
    'department' => 'IT Department',
    'is_active' => true,
]);
exit
```

---

## ✅ Correct Login Credentials

After running the fix, use these credentials:

### Admin Account
- **Email:** admin@thesisconnect.com
- **Password:** admin123

### Librarian Account
- **Email:** librarian@thesisconnect.com
- **Password:** librarian123

### Student Account
- **Email:** student@thesisconnect.com
- **Password:** student123

---

## 🔍 Verify Users Exist

Check if users were created:

```bash
cd thesis-system/backend
php artisan tinker
```

Then:
```php
App\Models\User::all(['id', 'name', 'email']);
exit
```

You should see 3 users listed.

---

## 🐛 Still Not Working?

### Check 1: Database File Exists
```bash
ls -la thesis-system/backend/database/database.sqlite
```

If not found, create it:
```bash
touch thesis-system/backend/database/database.sqlite
php artisan migrate:fresh --seed
```

### Check 2: Backend Server Running
```bash
# Make sure backend is running
cd thesis-system/backend
php artisan serve
```

Should show: `Server running on [http://127.0.0.1:8000]`

### Check 3: Frontend API URL
Check `thesis-system/frontend/src/services/api.js`:
```javascript
baseURL: 'http://localhost:8000/api'
```

### Check 4: CORS Settings
Check `thesis-system/backend/config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
```

### Check 5: Clear Browser Cache
- Press `Ctrl+Shift+Delete` (Windows) or `Cmd+Shift+Delete` (Mac)
- Clear cookies and cached data
- Refresh the page

---

## 🚀 Quick Test

After fixing, test login:

1. Open browser: `http://localhost:3000`
2. Enter:
   - Email: `admin@thesisconnect.com`
   - Password: `admin123`
3. Click Login

Should redirect to dashboard!

---

## 📞 Additional Help

If still having issues, check:

1. **Backend logs:**
   ```bash
   tail -f thesis-system/backend/storage/logs/laravel.log
   ```

2. **Browser console:**
   - Press F12
   - Check Console tab for errors

3. **Network tab:**
   - Press F12
   - Go to Network tab
   - Try logging in
   - Check the `/api/login` request
   - Look at the response

---

**Fixed!** You should now be able to login successfully.
