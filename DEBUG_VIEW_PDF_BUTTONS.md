# Debug Guide: View PDF & Download Buttons Not Showing

## Nilagyan ko ng Debug Logs

Nilagyan ko ng console.log ang ThesesList.js para makita natin kung ano ang problema.

## Paano I-debug

### Step 1: I-restart ang Frontend
```bash
# Sa terminal ng frontend
Ctrl + C
npm start
```

### Step 2: Buksan ang Browser Console
1. Open http://localhost:3000
2. Press F12 (Developer Tools)
3. Go to "Console" tab
4. Navigate to Browse Theses page

### Step 3: Tingnan ang Console Logs

Dapat makita mo:
```
API Response: { data: [...], last_page: 1, ... }
First thesis: { id: 1, title: "...", active_document: {...} }
Has active_document?: { id: 1, file_path: "...", ... }
```

Para sa bawat thesis card:
```
Thesis 1: { id: 1, title: "...", active_document: {...} }
Has active_document? { id: 1, ... }
```

## Possible Issues

### Issue 1: Walang `active_document` sa response
Kung makikita mo sa console:
```
Has active_document?: null
```

**Solution**: Walang uploaded PDF ang thesis. Kailangan mag-upload muna ng PDF.

**Paano mag-upload:**
1. Login as Faculty/Staff/Admin
2. Go to "Upload Thesis"
3. Fill in the form
4. **IMPORTANT**: Upload a PDF file
5. Submit

### Issue 2: May `active_document` pero walang buttons
Kung makikita mo:
```
Has active_document? { id: 1, file_path: "..." }
```

Pero walang buttons, ibig sabihin may problema sa rendering.

**Solution**: Check if may error sa console

### Issue 3: Backend hindi nag-return ng `active_document`
Kung walang `active_document` field sa API response.

**Solution**: Check backend logs
```bash
cd thesis-system/backend
php artisan serve
```

Tingnan kung may error sa backend terminal.

### Issue 4: Database walang documents
Kung walang documents sa database.

**Solution**: Check database
```bash
cd thesis-system/backend
php artisan tinker
```

```php
// Check if may documents
\App\Models\Document::count();

// Check specific thesis
$thesis = \App\Models\Thesis::first();
$thesis->activeDocument;
$thesis->documents;
```

## Quick Test: Upload Sample Thesis with PDF

Para sigurado na may data:

1. Login as admin@example.com / password
2. Go to "Upload Thesis"
3. Fill in:
   - Title: "Test Thesis with PDF"
   - Authors: ["Test Author"]
   - Year: 2024
   - Department: "Computer Science"
   - Program: "BSCS"
   - Academic Level: "Undergraduate"
   - Document Type: "Student Thesis"
   - Abstract: "This is a test thesis"
   - Keywords: ["test", "sample"]
4. **Upload a PDF file** (kahit ano, basta .pdf)
5. Submit
6. Go to Browse Theses
7. Dapat makita mo ang View PDF at Download buttons

## Expected Console Output

Kung tama ang lahat:
```
API Response: {
  data: [
    {
      id: 1,
      title: "Test Thesis with PDF",
      active_document: {
        id: 1,
        thesis_id: 1,
        file_path: "documents/xxx.pdf",
        file_name: "test.pdf",
        is_active: true
      },
      ...
    }
  ]
}

Thesis 1: { id: 1, ..., active_document: { id: 1, ... } }
Has active_document? { id: 1, ... }
```

## Kung Wala Pa Rin

Kung after ng lahat ng ito wala pa rin ang buttons:

1. Take a screenshot ng console logs
2. Take a screenshot ng Network tab (XHR request to /api/theses)
3. Sabihin mo sa akin kung ano ang nakikita mo

## Remove Debug Logs Later

Pagkatapos ma-fix, tanggalin ang debug logs:
- Remove `console.log` lines sa ThesesList.js
