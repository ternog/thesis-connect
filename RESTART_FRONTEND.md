# Fix: Restart Frontend Server

## Problema
Ang webpack dev server ay gumagamit pa ng old/cached version ng ThesesList.js file kahit na-update na ang file.

## Solution

### Step 1: Stop ang Frontend Server
Sa terminal kung saan tumatakbo ang `npm start`:
- Press `Ctrl + C`
- Type `Y` kung may confirmation

### Step 2: Clear Node Modules Cache (Optional pero recommended)
```bash
cd thesis-system/frontend
rm -rf node_modules/.cache
```

O sa Windows PowerShell:
```powershell
cd thesis-system/frontend
Remove-Item -Recurse -Force node_modules/.cache -ErrorAction SilentlyContinue
```

### Step 3: Start Ulit ang Frontend
```bash
npm start
```

## Verification

Pagkatapos mag-restart, dapat:
1. Walang compile errors
2. May warning lang about `fetchTheses` dependency (okay lang yan)
3. Makikita mo na ang View PDF at Download buttons

## Ang File ay Tama Na!

Verified na walang duplicate declarations at walang leftover PDF viewer code. Ang problema lang ay cached version sa webpack dev server.

## Expected Buttons

Kung may PDF document ang thesis, makikita mo:
1. **View Details** - outlined green button
2. **View PDF** - contained green button (mag-oopen sa new tab)
3. **Download** - outlined green button
4. **Edit** - kung owner
5. **Delete** - kung owner
