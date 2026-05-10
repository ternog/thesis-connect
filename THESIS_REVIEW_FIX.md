# Thesis Review API Fix

## Issue
"Failed to fetch reviews" error in Thesis Review & Approval page

## Root Cause
The frontend was calling the wrong API endpoint:
- ❌ Frontend was calling: `/api/thesis-reviews`
- ✅ Backend route is actually: `/api/reviews`

## Changes Made

### 1. Fixed API Endpoints in ThesisReview.js

**Changed:**
```javascript
// OLD (incorrect)
api.get('/thesis-reviews', ...)
api.put(`/thesis-reviews/${id}`, ...)

// NEW (correct)
api.get('/reviews', ...)
api.put(`/reviews/${id}`, ...)
```

### 2. Improved Reviewer Fetching

**Changed:**
```javascript
// OLD - hardcoded role_id
params: { role_id: 2, per_page: 100 }

// NEW - filter by role name
const eligibleReviewers = (response.data.data || []).filter(
  user => user.role?.name === 'admin' || user.role?.name === 'library_staff'
);
```

### 3. Added Error Logging

Added `console.error()` to help debug future issues:
```javascript
catch (error) {
  setError('Failed to fetch reviews');
  console.error('Error fetching reviews:', error);
}
```

## Correct API Endpoints

All thesis review endpoints:
- `GET /api/reviews` - List all reviews (with filters)
- `POST /api/theses/{id}/reviews` - Assign a review
- `PUT /api/reviews/{id}` - Update review (submit decision)
- `POST /api/theses/{id}/request-revision` - Request revision
- `POST /api/revisions/{id}/complete` - Mark revision complete

## Role IDs Reference

From `RoleSeeder.php`:
1. **admin** (ID: 1) - Can approve theses
2. **library_staff** (ID: 2) - Can approve theses
3. **faculty** (ID: 3) - Can upload theses
4. **researcher** (ID: 4) - Can view/download
5. **student** (ID: 5) - Can view/download

## Testing

To test the fix:

1. **Login as Admin**:
   ```
   Email: admin@mbc.edu.ph
   Password: admin123
   ```

2. **Navigate to Thesis Reviews**:
   - Go to `/admin/reviews`
   - Should now load without errors

3. **Verify Features Work**:
   - ✅ Reviews list loads
   - ✅ Pending theses show in alert card
   - ✅ Can assign reviewers
   - ✅ Can submit review decisions
   - ✅ Tabs filter correctly

## Status: FIXED ✅

The Thesis Review & Approval page should now work correctly!
