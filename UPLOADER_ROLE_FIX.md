# Uploader Role Display Fix

## Problem
The system was showing "System Administrator (undefined)" for all thesis uploads because the role information was not being loaded from the database.

## Root Cause
The backend was loading the `uploader` relationship but not the nested `role` relationship. The frontend was trying to access `thesis.uploader?.role?.display_name`, but the role data was not included in the API response.

## Changes Made

### Backend Changes

1. **ThesisController.php** - Updated to load uploader role:
   - `index()` method: Changed `'uploader'` to `'uploader.role'`
   - `show()` method: Changed `'uploader'` to `'uploader.role'` and `'approver'` to `'approver.role'`

2. **DashboardController.php** - Updated to load uploader role:
   - `recentActivity()` method: Changed `'uploader'` to `'uploader.role'` for both theses and documents

### Frontend Changes

1. **ThesisDetail.js** - Enhanced uploader display:
   - Now shows both name and role: "John Doe (Library Staff)"
   - Gracefully handles missing role data

## How It Works

The system now properly loads the role relationship chain:
```
Thesis → User (uploader) → Role
```

This allows the frontend to display:
- Staff uploads as: "Staff Name (Library Staff)"
- Faculty uploads as: "Faculty Name (Faculty)"
- Admin uploads as: "Admin Name (System Administrator)"

## Files Modified

1. `thesis-system/backend/app/Http/Controllers/Api/ThesisController.php`
2. `thesis-system/backend/app/Http/Controllers/Api/DashboardController.php`
3. `thesis-system/frontend/src/pages/Theses/ThesisDetail.js`

## Testing

After these changes:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Refresh the thesis review page
3. You should now see the correct uploader name and role for each thesis

## Note

The role information comes from the `roles` table which has:
- `name`: Internal identifier (e.g., 'library_staff')
- `display_name`: User-friendly name (e.g., 'Library Staff')

The system displays the `display_name` to users.
