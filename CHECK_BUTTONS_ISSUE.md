# Bakit Walang View PDF at Download Buttons?

## Ang Code ay Tama!

Nandyan na ang View PDF at Download buttons sa code. Ang buttons ay lalabas lang kung:

```javascript
{thesis.active_document && (
  <>
    <Button>View PDF</Button>
    <Button>Download</Button>
  </>
)}
```

## Possible Reasons:

### 1. Hindi Pa Naka-Refresh ang Browser
**SOLUTION:**
- Press `Ctrl + Shift + R` (hard refresh)
- O kaya `Ctrl + F5`
- O manually clear cache at i-refresh

### 2. Walang PDF Document ang Thesis
Ang buttons ay lalabas lang kung may `active_document` ang thesis.

**Paano i-check:**
1. Open browser console (F12)
2. Type: `console.log(theses)`
3. Tingnan kung may `active_document` property

**Paano mag-upload ng PDF:**
1. Login as Faculty/Staff
2. Go to "Upload Thesis"
3. Fill in the form
4. Upload PDF file
5. Submit

### 3. Backend Issue
Baka hindi nag-rereturn ng `active_document` ang API.

**Paano i-check:**
1. Open browser Network tab (F12 > Network)
2. Refresh the page
3. Look for `/api/theses` request
4. Check the response - dapat may `active_document` field

## Quick Test

Gawin ito para ma-verify:

1. **Hard Refresh**: `Ctrl + Shift + R`
2. **Check Console**: F12 > Console tab
3. **Check Network**: F12 > Network tab > XHR
4. **Look for theses with PDF**: Dapat may thesis na may uploaded PDF

## Expected Button Order

Kung may PDF document ang thesis:
1. **View Details** (outlined green)
2. **View PDF** (contained green) ← Ito dapat makita mo
3. **Download** (outlined green) ← Ito din dapat makita mo
4. **Edit** (kung owner ka)
5. **Delete** (kung owner ka)

## Debug Steps

Kung wala pa rin after refresh:

```javascript
// Add this temporarily sa ThesesList.js after fetchTheses
console.log('Theses data:', theses);
console.log('First thesis:', theses[0]);
console.log('Has active_document?', theses[0]?.active_document);
```

Tapos tingnan mo sa console kung ano ang lumalabas.
