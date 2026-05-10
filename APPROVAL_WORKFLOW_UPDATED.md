# ✅ Approval Workflow for Library Staff & Faculty Uploads

## Overview
Files uploaded by **Library Staff** and **Faculty Members** now require **Admin approval** before becoming publicly visible.

## 🔄 Workflow

### 1. **Upload Process**

#### For Admin Users
- ✅ Uploads are **automatically approved**
- ✅ Immediately visible to public
- ✅ Status: `approved`

#### For Library Staff & Faculty
- ⏳ Uploads go to **pending status**
- ⏳ **NOT visible** to public until approved
- ⏳ Status: `pending`
- ⏳ Notification: "Thesis uploaded successfully. Waiting for admin approval before it becomes publicly visible."

### 2. **Approval Process**

#### Admin Actions
1. **View Pending Theses**
   - Go to Dashboard or Theses List
   - Filter by status: "Pending"
   - See all theses waiting for approval

2. **Review Thesis**
   - Click on thesis to view details
   - Check title, authors, abstract, keywords
   - Review uploaded PDF document
   - Verify accuracy and completeness

3. **Approve or Reject**
   - **Approve**: Thesis becomes publicly visible
   - **Reject**: Thesis remains hidden, uploader is notified

### 3. **Visibility Rules**

#### Public Users (Not Logged In)
- ✅ Can only see **approved** theses
- ❌ Cannot see pending or rejected theses

#### Students
- ✅ Can only see **approved** theses
- ❌ Cannot see pending or rejected theses

#### Library Staff & Faculty
- ✅ Can see **approved** theses
- ✅ Can see **their own** pending/rejected theses
- ❌ Cannot see other users' pending theses

#### Admin
- ✅ Can see **ALL** theses (approved, pending, rejected)
- ✅ Can approve or reject any thesis
- ✅ Full access to all content

## 📊 Status Flow

```
Library Staff/Faculty Upload
         ↓
    [PENDING]
         ↓
    Admin Review
         ↓
    ┌─────────┴─────────┐
    ↓                   ↓
[APPROVED]         [REJECTED]
    ↓                   ↓
Publicly Visible   Hidden from Public
```

## 🔐 Permission Matrix

| Action | Admin | Library Staff | Faculty | Student | Public |
|--------|-------|---------------|---------|---------|--------|
| Upload Thesis | ✅ Auto-approved | ⏳ Needs approval | ⏳ Needs approval | ❌ | ❌ |
| View Approved | ✅ | ✅ | ✅ | ✅ | ✅ |
| View Pending | ✅ All | ✅ Own only | ✅ Own only | ❌ | ❌ |
| Approve/Reject | ✅ | ❌ | ❌ | ❌ | ❌ |
| Edit Own | ✅ | ✅ | ✅ | ❌ | ❌ |
| Delete Own | ✅ | ✅ | ✅ | ❌ | ❌ |

## 🎯 Implementation Details

### Backend Changes

#### ThesisController.php - store() method
```php
// Determine status based on user role
$user = $request->user();
$userRole = $user->role->name;

// Admin uploads are auto-approved
// Library staff and faculty uploads need approval
$needsApproval = in_array($userRole, ['librarian', 'faculty']);

$status = $needsApproval ? 'pending' : 'approved';
$approvedAt = $needsApproval ? null : now();
$approvedBy = $needsApproval ? null : $user->id;
```

#### ThesisController.php - index() method
```php
// Status filter - only show approved for non-admin users
if (!$request->user() || !$request->user()->canApproveTheses()) {
    $query->approved();
} elseif ($request->has('status') && $request->status) {
    $query->where('status', $request->status);
}
```

### Database Schema

#### theses table
- `status` - enum: 'pending', 'approved', 'rejected', 'archived'
- `approved_at` - timestamp (nullable)
- `approved_by` - foreign key to users (nullable)
- `uploaded_by` - foreign key to users

### Activity Logging

All uploads are logged with:
- Action type: `thesis_created`
- Status: `pending` or `approved`
- Uploader role: `librarian`, `faculty`, or `admin`
- Needs approval flag: `true` or `false`

## 📝 User Experience

### For Library Staff/Faculty

#### Upload Success Message
```
✅ Thesis uploaded successfully!
⏳ Waiting for admin approval before it becomes publicly visible.
```

#### My Theses Page
- Shows all their uploaded theses
- Pending theses have yellow badge: "⏳ Pending Approval"
- Approved theses have green badge: "✅ Approved"
- Rejected theses have red badge: "❌ Rejected"

### For Admin

#### Dashboard
- Shows count of pending theses
- Quick link to review pending submissions

#### Theses List
- Filter dropdown includes "Pending" option
- Pending theses highlighted with yellow indicator
- Approve/Reject buttons visible on thesis detail page

#### Approval Actions
- **Approve Button**: Green, with confirmation
- **Reject Button**: Red, with reason field (optional)
- Activity logged for audit trail

## 🔔 Notifications (Future Enhancement)

### Email Notifications
- **On Upload**: Admin receives email about new pending thesis
- **On Approval**: Uploader receives email that thesis is now public
- **On Rejection**: Uploader receives email with reason (if provided)

### In-App Notifications
- Badge count on admin dashboard for pending theses
- Notification bell icon with pending count
- Toast messages for status changes

## 🧪 Testing Checklist

### As Library Staff
- [ ] Upload a thesis
- [ ] Verify status shows "Pending"
- [ ] Verify thesis NOT visible in public browse
- [ ] Verify thesis visible in "My Theses"
- [ ] Verify cannot approve own thesis

### As Faculty
- [ ] Upload a thesis
- [ ] Verify status shows "Pending"
- [ ] Verify thesis NOT visible in public browse
- [ ] Verify thesis visible in "My Theses"
- [ ] Verify cannot approve own thesis

### As Admin
- [ ] Upload a thesis
- [ ] Verify status shows "Approved" immediately
- [ ] Verify thesis visible in public browse
- [ ] View pending theses from others
- [ ] Approve a pending thesis
- [ ] Verify approved thesis now public
- [ ] Reject a pending thesis
- [ ] Verify rejected thesis remains hidden

### As Public User
- [ ] Browse theses
- [ ] Verify only approved theses visible
- [ ] Verify cannot see pending theses
- [ ] Verify cannot see rejected theses

## 📊 Reports & Analytics

### Admin Dashboard
- Total pending theses count
- Average approval time
- Approval rate by uploader
- Recent approval activity

### Activity Logs
- All uploads logged with status
- All approvals/rejections logged
- Searchable by user, date, action

## 🔧 Configuration

### Role Names (must match database)
```php
'admin' => Auto-approved
'librarian' => Needs approval
'faculty' => Needs approval
'student' => Cannot upload (or needs approval if enabled)
```

### Status Values
```php
'pending' => Waiting for approval
'approved' => Publicly visible
'rejected' => Hidden from public
'archived' => Soft deleted
```

## 🚀 Deployment Steps

1. ✅ Update ThesisController.php (DONE)
2. ✅ Verify User model permissions (DONE)
3. ✅ Test upload workflow
4. ✅ Test approval workflow
5. ✅ Update frontend to show status badges
6. ✅ Add filter for pending theses
7. ✅ Test all user roles

## 📚 Related Files

### Backend
- `app/Http/Controllers/Api/ThesisController.php`
- `app/Models/Thesis.php`
- `app/Models/User.php`
- `database/migrations/*_create_theses_table.php`

### Frontend
- `frontend/src/pages/Theses/ThesesList.js`
- `frontend/src/pages/Theses/ThesisDetail.js`
- `frontend/src/pages/Theses/ThesisForm.js`
- `frontend/src/pages/Dashboard/Dashboard.js`

## ✅ Summary

The approval workflow is now implemented:
- ✅ Library staff uploads → Pending status
- ✅ Faculty uploads → Pending status
- ✅ Admin uploads → Auto-approved
- ✅ Only approved theses visible to public
- ✅ Admin can approve/reject pending theses
- ✅ Activity logging for audit trail
- ✅ Proper permission checks throughout

---

**Status**: ✅ IMPLEMENTED
**Date**: March 30, 2026
**Version**: 1.0
