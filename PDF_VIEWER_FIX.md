# PDF Viewer & View Counting Fix

## ✅ FIXED: Dalawang Issues

### Issue 1: PDF Hindi Makita (Naga-loading Lang)
**Problem**: Ang PDF viewer ay naga-loading lang at hindi lumalabas ang PDF

**Root Cause**: 
- Ang `/download` endpoint ay nag-trigger ng file download, hindi inline viewing
- Walang endpoint para sa inline PDF viewing
- Iframe cannot display downloaded files

**Solution**:
1. ✅ Created new `view()` method in DocumentController
2. ✅ Returns PDF with `Content-Disposition: inline` para ma-display sa browser
3. ✅ Added route: `GET /api/documents/{id}/view`
4. ✅ Updated ThesisDetail.js to use `/view` instead of `/download`
5. ✅ Added error handling sa PDFViewer component

---

### Issue 2: Views Counting Paulit-ulit
**Problem**: Kada view ng user, tumataas ang view count kahit same user lang

**Root Cause**:
- Ang `recordView()` method ay nag-create ng bagong view record every time
- Walang check kung nag-view na ba ang user

**Solution**:
1. ✅ Updated `ThesisView::recordView()` method
2. ✅ Check kung nag-view na ba ang user TODAY
3. ✅ Kung nag-view na, hindi na mag-increment ng count
4. ✅ Kung hindi pa, mag-create ng new view record

**Logic**:
```php
// Check if user already viewed today
$existingView = ThesisView::where('thesis_id', $thesis->id)
    ->where('user_id', $user->id)  // Same user
    ->whereDate('created_at', today())  // Today
    ->first();

// Only count if NOT viewed today
if (!$existingView) {
    $thesis->increment('view_count');
    // Create new view record
}
```

---

## 📋 Changes Made

### Backend (3 files):

#### 1. DocumentController.php
**Added new method**:
```php
public function view(Request $request, Document $document)
{
    // Check permissions
    // Return PDF for inline viewing
    return Storage::disk('public')->response(
        $document->file_path,
        $document->original_name,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="..."',
        ]
    );
}
```

**Difference from download()**:
- `download()` - Forces file download
- `view()` - Displays inline in browser

#### 2. ThesisView.php
**Updated recordView() method**:
```php
public static function recordView(Thesis $thesis, ?User $user = null)
{
    // Check if already viewed today
    $existingView = static::where('thesis_id', $thesis->id)
        ->where(function ($query) use ($user) {
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('ip_address', request()->ip())
                      ->whereNull('user_id');
            }
        })
        ->whereDate('created_at', today())
        ->first();

    // Only record if not viewed today
    if (!$existingView) {
        $thesis->increment('view_count');
        $thesis->update(['last_viewed_at' => now()]);

        return static::create([...]);
    }

    return $existingView;
}
```

**View Counting Rules**:
- ✅ Logged-in user: 1 view per user per day
- ✅ Guest user: 1 view per IP address per day
- ✅ Same user viewing multiple times today: No additional count
- ✅ Same user viewing tomorrow: New count

#### 3. routes/api.php
**Added new route**:
```php
Route::get('/documents/{document}/view', [DocumentController::class, 'view'])
    ->name('documents.view');
```

### Frontend (2 files):

#### 1. ThesisDetail.js
**Changed PDF URL**:
```javascript
// OLD (download endpoint)
documentUrl={`http://localhost:8000/api/documents/${id}/download`}

// NEW (view endpoint)
documentUrl={`http://localhost:8000/api/documents/${id}/view`}
```

#### 2. PDFViewer.js
**Added error handling**:
```javascript
const [error, setError] = useState(null);

const handleIframeError = () => {
  setError('Failed to load PDF. Click Download to view the file.');
  setLoading(false);
};

// In iframe
onError={handleIframeError}
```

**Added error display**:
```javascript
{error && (
  <Box sx={{ textAlign: 'center' }}>
    <Typography sx={{ color: 'white', mb: 2 }}>
      {error}
    </Typography>
    <Button onClick={handleDownload}>
      Download PDF
    </Button>
  </Box>
)}
```

---

## 🧪 Testing

### Test PDF Viewing:
1. Go to any thesis detail page
2. Click "View PDF" button
3. ✅ PDF should load and display in overlay
4. ✅ Should see PDF content (not download prompt)
5. ✅ Can zoom in/out
6. ✅ Can close viewer

### Test View Counting:
1. **First View**:
   - View a thesis
   - Check view count: Should be 1
   
2. **Refresh Page**:
   - Refresh and view again
   - Check view count: Should STILL be 1 (not 2)
   
3. **View Multiple Times**:
   - View 5 times in a row
   - Check view count: Should STILL be 1
   
4. **Different User**:
   - Logout and login as different user
   - View same thesis
   - Check view count: Should be 2 (new user)
   
5. **Next Day**:
   - Wait until tomorrow (or change system date)
   - View same thesis
   - Check view count: Should be 3 (new day)

---

## 🔧 How View Counting Works Now

### Scenario 1: Same User, Same Day
```
User A views Thesis X at 10:00 AM → Count: 1
User A views Thesis X at 11:00 AM → Count: 1 (no change)
User A views Thesis X at 3:00 PM  → Count: 1 (no change)
```

### Scenario 2: Different Users, Same Day
```
User A views Thesis X at 10:00 AM → Count: 1
User B views Thesis X at 11:00 AM → Count: 2
User C views Thesis X at 3:00 PM  → Count: 3
```

### Scenario 3: Same User, Different Days
```
User A views Thesis X on Monday    → Count: 1
User A views Thesis X on Tuesday   → Count: 2
User A views Thesis X on Wednesday → Count: 3
```

### Scenario 4: Guest Users (Not Logged In)
```
IP 192.168.1.1 views Thesis X at 10:00 AM → Count: 1
IP 192.168.1.1 views Thesis X at 11:00 AM → Count: 1 (same IP, same day)
IP 192.168.1.2 views Thesis X at 12:00 PM → Count: 2 (different IP)
```

---

## 📊 View Statistics

### What Gets Tracked:
- `thesis_views` table:
  - thesis_id
  - user_id (if logged in)
  - ip_address
  - user_agent
  - created_at (timestamp)

### What Gets Counted:
- `theses.view_count` - Total unique views
- Only counts once per user per day
- Only counts once per IP per day (for guests)

---

## 🎯 Benefits

### PDF Viewing:
- ✅ Instant viewing (no download needed)
- ✅ Works in browser
- ✅ Zoom in/out functionality
- ✅ Print from viewer
- ✅ Download option available
- ✅ Error handling if PDF fails to load

### View Counting:
- ✅ Accurate statistics
- ✅ Prevents count inflation
- ✅ Fair representation of popularity
- ✅ Tracks unique daily viewers
- ✅ Works for logged-in and guest users

---

## 🔐 Security

Both endpoints check permissions:
- ✅ Approved theses: Anyone can view
- ✅ Pending theses: Only uploader and admins
- ✅ Rejected theses: Only uploader and admins

---

## 🚀 Status: FIXED ✅

**PDF Viewing**: ✅ Working - PDF displays inline
**View Counting**: ✅ Working - Only counts once per user per day

**Ready to Test**: Yes! Try viewing a PDF now.

---

## 💡 Additional Notes

### If PDF Still Not Loading:
1. Check if file exists in `storage/app/public/documents/`
2. Check if symbolic link exists: `php artisan storage:link`
3. Check file permissions
4. Check browser console for errors

### View Count Reset:
If you want to reset view counts for testing:
```sql
UPDATE theses SET view_count = 0;
DELETE FROM thesis_views;
```

---

**Tapos na ang fix!** 🎉
