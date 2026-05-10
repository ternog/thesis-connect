# Troubleshooting: "No Theses Found"

## Problema
Nakikita ang "No theses found" kahit may data sa database o kahit nag-search.

## Debug Steps

### Step 1: Check Browser Console
1. Open browser (Chrome/Edge/Firefox)
2. Press F12 to open Developer Tools
3. Go to "Console" tab
4. Refresh the Browse Theses page
5. Tingnan ang console logs:

**Expected logs:**
```
Fetching theses with params: { page: 1, per_page: 12 }
Theses response: { data: [...], last_page: 1, ... }
Number of theses: 5
```

**If you see errors:**
- Network Error → Backend is not running
- 401 Unauthorized → Authentication issue
- 500 Server Error → Backend error

### Step 2: Check if Backend is Running
```bash
cd thesis-system/backend
php artisan serve
```

Should see:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### Step 3: Check Database
```bash
cd thesis-system/backend
php artisan tinker
```

Then run:
```php
// Check if may theses
\App\Models\Thesis::count();

// Check approved theses
\App\Models\Thesis::where('status', 'approved')->count();

// Get first thesis
$thesis = \App\Models\Thesis::first();
print_r($thesis->toArray());

// Check with relationships
$thesis = \App\Models\Thesis::with(['activeDocument', 'authors'])->first();
print_r($thesis->toArray());
```

### Step 4: Check API Directly
Open browser and go to:
```
http://localhost:8000/api/theses
```

Should see JSON response with theses data.

If you see:
- "Unauthenticated" → Normal for non-logged in users, but should still show approved theses
- Empty data array → No approved theses in database
- Error → Backend issue

## Common Issues & Solutions

### Issue 1: No Theses in Database
**Symptoms:**
- Console shows: `Number of theses: 0`
- API returns empty array

**Solution:** Upload a thesis
1. Login as admin@example.com / password
2. Go to "Upload Thesis"
3. Fill in all required fields:
   - Title: "Sample Thesis"
   - Authors: ["John Doe"]
   - Year: 2024
   - Department: "Computer Science"
   - Program: "BSCS"
   - Academic Level: "Undergraduate"
   - Document Type: "Student Thesis"
   - Abstract: "This is a sample thesis"
   - Keywords: ["sample", "test"]
4. Upload a PDF file
5. Submit
6. Go back to Browse Theses

### Issue 2: All Theses are Pending/Rejected
**Symptoms:**
- Database has theses but none showing
- Only admins can see them

**Solution:** Approve theses or check status
```bash
php artisan tinker
```

```php
// Check thesis statuses
\App\Models\Thesis::select('id', 'title', 'status')->get();

// Approve all theses
\App\Models\Thesis::where('status', 'pending')->update([
    'status' => 'approved',
    'approved_at' => now(),
    'approved_by' => 1
]);
```

### Issue 3: Backend Not Running
**Symptoms:**
- Console shows: `Network Error`
- Cannot connect to server

**Solution:**
```bash
cd thesis-system/backend
php artisan serve
```

Keep this terminal open!

### Issue 4: Wrong API URL
**Symptoms:**
- Console shows 404 errors
- API calls failing

**Solution:** Check `thesis-system/frontend/src/services/api.js`
```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000/api',  // Should be this
  // ...
});
```

### Issue 5: CORS Issues
**Symptoms:**
- Console shows CORS errors
- "Access-Control-Allow-Origin" errors

**Solution:** Check `thesis-system/backend/config/cors.php`
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:3000'],
```

### Issue 6: Search Not Working
**Symptoms:**
- Normal browse works
- Search returns no results

**Solution:** Check SmartSearchController
```bash
cd thesis-system/backend
php artisan route:list | grep search
```

Should see:
```
GET|HEAD  api/search ..................... SmartSearchController@search
GET|HEAD  api/search/autocomplete ........ SmartSearchController@autocomplete
```

## Quick Fix: Seed Sample Data

If walang data sa database:

```bash
cd thesis-system/backend

# Create sample thesis
php artisan tinker
```

```php
$thesis = \App\Models\Thesis::create([
    'title' => 'Machine Learning in Healthcare',
    'authors' => ['John Doe', 'Jane Smith'],
    'adviser' => 'Dr. Robert Johnson',
    'year' => 2024,
    'department' => 'Computer Science',
    'program' => 'BSCS',
    'academic_level' => 'undergraduate',
    'document_type' => 'student_thesis',
    'abstract' => 'This thesis explores the application of machine learning algorithms in healthcare diagnostics.',
    'keywords' => ['machine learning', 'healthcare', 'diagnostics', 'AI'],
    'uploaded_by' => 1,
    'status' => 'approved',
    'approved_at' => now(),
    'approved_by' => 1
]);

echo "Created thesis ID: " . $thesis->id;
```

## Verification Checklist

After troubleshooting, verify:
- [ ] Backend is running (http://localhost:8000)
- [ ] Frontend is running (http://localhost:3000)
- [ ] Database has approved theses
- [ ] API endpoint works (http://localhost:8000/api/theses)
- [ ] Browser console shows no errors
- [ ] Theses appear on Browse Theses page

## Still Not Working?

1. Check browser console for errors
2. Check backend terminal for errors
3. Take screenshots of:
   - Browser console
   - Network tab (XHR requests)
   - Backend terminal
4. Share the error messages
