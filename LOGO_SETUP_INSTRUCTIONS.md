# MSU Logo Setup Instructions

## Step 1: Prepare the Logo

### Option A: Using Online Tool (Easiest)
1. Go to https://www.remove.bg/
2. Upload the MSU logo image
3. Download the PNG with transparent background
4. Save as `msu-logo.png`

### Option B: Using Photoshop/GIMP
1. Open the MSU logo image
2. Use Magic Wand or Select by Color tool
3. Select the white background
4. Delete the white background
5. Export as PNG with transparency
6. Save as `msu-logo.png`

### Option C: Using PowerPoint (Quick Method)
1. Insert the MSU logo image in PowerPoint
2. Click on the image
3. Go to Picture Format → Remove Background
4. Mark areas to keep/remove
5. Right-click → Save as Picture
6. Choose PNG format
7. Save as `msu-logo.png`

## Step 2: Place the Logo

Copy the `msu-logo.png` file to:
```
thesis-system/frontend/public/msu-logo.png
```

## Step 3: Verify

The logo should be:
- ✅ PNG format
- ✅ Transparent background (no white)
- ✅ Good resolution (at least 500x500px recommended)
- ✅ Named exactly: `msu-logo.png`

## Current Usage

The logo is used in:
1. **Login Page** - Large logo at top
2. **Register Page** - Large logo at top
3. **Layout Header** - Small logo in sidebar (to be implemented)
4. **Dashboard** - Medium logo (to be implemented)

## Logo Sizes in Code

```css
.msu-logo-sm { width: 40px; height: 40px; }    /* Sidebar */
.msu-logo { width: 60px; height: 60px; }       /* Default */
.msu-logo-lg { width: 80px; height: 80px; }    /* Headers */
.msu-logo-xl { width: 120px; height: 120px; }  /* Auth pages */
```

## Alternative: Use SVG (If you have vector file)

If you have the MSU logo in SVG format:
1. Save it as `msu-logo.svg`
2. Place in `thesis-system/frontend/public/msu-logo.svg`
3. SVG automatically has transparent background
4. Better for scaling (no pixelation)

## Testing

After placing the logo, restart the frontend:
```bash
cd thesis-system/frontend
npm start
```

Then check:
- Login page should show MSU logo
- Register page should show MSU logo
- No white background should be visible

---

**Note:** The system is already configured to use `/msu-logo.png`. Just place the file and it will work automatically!
