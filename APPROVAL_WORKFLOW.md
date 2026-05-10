# Thesis Approval Workflow

## Overview

The Thesis System implements a comprehensive approval workflow to ensure quality control and proper review of all submissions before they become publicly visible.

---

## Approval Rules

### Who Needs Approval?

| User Role | Upload Permission | Requires Approval | Auto-Approved |
|-----------|------------------|-------------------|---------------|
| **Admin** | ✅ Yes | ❌ No | ✅ Yes |
| **Library Staff** | ✅ Yes | ✅ Yes | ❌ No |
| **Faculty** | ✅ Yes | ✅ Yes | ❌ No |
| **Student** | ✅ Yes | ✅ Yes | ❌ No |
| **Researcher** | ❌ No | N/A | N/A |

### Key Points

1. **Admin submissions** are automatically approved and immediately visible to the public
2. **All other users** (Library Staff, Faculty, Students) must wait for admin approval
3. **Only Admin and Library Staff** can approve/reject theses
4. **Pending theses** are NOT visible to the public until approved

---

## Workflow Steps

### 1. Thesis Submission (Faculty/Staff/Student)

```
User uploads thesis → Status: PENDING → Notification to Admin
```

**What happens:**
- Thesis is created with status `pending`
- Activity log is created
- Thesis is NOT visible in public browse
- User can see their submission in "My Theses"
- Admin/Library Staff see it in "Thesis Review" page

### 2. Admin Review

```
Admin reviews thesis → Approve OR Reject
```

**Admin can:**
- View full thesis details
- Read abstract and metadata
- Download and review PDF
- Approve or reject with reason

### 3. Approval

```
Admin approves → Status: APPROVED → Public visibility
```

**What happens:**
- Status changes to `approved`
- `approved_at` timestamp is set
- `approved_by` is set to admin user ID
- Activity log is created
- Thesis becomes visible in public browse
- Thesis appears in search results

### 4. Rejection

```
Admin rejects → Status: REJECTED → User notified
```

**What happens:**
- Status changes to `rejected`
- Rejection reason is logged
- Activity log is created
- Thesis remains hidden from public
- User can see rejection in "My Theses"
- User may need to revise and resubmit

---

## User Experience

### For Faculty/Staff/Students

#### Uploading a Thesis

1. Navigate to "Upload Thesis"
2. Fill in all required information
3. Upload PDF document
4. Click "Submit"
5. See success message: "Thesis submitted successfully! It will be visible to the public after admin approval."
6. Thesis appears in "My Theses" with status "Pending"

#### Checking Status

1. Go to "My Theses"
2. See thesis with status badge:
   - 🟡 **Pending** - Waiting for review
   - 🟢 **Approved** - Published and visible
   - 🔴 **Rejected** - Needs revision

#### After Approval

- Thesis appears in public browse
- Thesis is searchable
- PDF is downloadable
- Statistics are tracked

#### After Rejection

- View rejection reason in activity logs
- Revise thesis based on feedback
- Resubmit as new submission

### For Admin/Library Staff

#### Reviewing Theses

1. Navigate to "Thesis Review & Approval"
2. See count of pending theses
3. View thesis cards with key information
4. Click "View Details" to see full thesis
5. Click "Approve" or "Reject"

#### Approving a Thesis

1. Click "Approve" button
2. Confirm in dialog
3. Thesis is immediately published
4. Success notification appears
5. Thesis removed from pending list

#### Rejecting a Thesis

1. Click "Reject" button
2. Enter detailed rejection reason
3. Confirm rejection
4. User is notified via activity log
5. Thesis remains in system but hidden

---

## Technical Implementation

### Backend Logic

#### ThesisController::store()

```php
// Determine status based on user role
'status' => $request->user()->isAdmin() ? 'approved' : 'pending'
```

#### ThesisController::index()

```php
// Filter by status for non-admin users
if (!$request->user() || !$request->user()->canApproveTheses()) {
    $query->approved(); // Only show approved theses
}
```

#### ThesisController::approve()

```php
$thesis->update([
    'status' => 'approved',
    'approved_at' => now(),
    'approved_by' => $request->user()->id,
]);
```

#### ThesisController::reject()

```php
$thesis->update(['status' => 'rejected']);
// Log rejection reason in activity log
```

### Frontend Logic

#### Public Browse (ThesesList)

```javascript
// Non-admin users only see approved theses
// Admin/Library Staff can filter by status
```

#### Thesis Review Page

```javascript
// Only accessible by Admin and Library Staff
if (!canApproveTheses()) {
  return <AccessDenied />;
}
```

#### Thesis Form

```javascript
// Show appropriate message based on response
if (requiresApproval) {
  setSuccess('Waiting for admin approval');
} else {
  setSuccess('Published successfully');
}
```

---

## Database Schema

### Theses Table

```sql
status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending'
approved_at TIMESTAMP NULL
approved_by FOREIGN KEY(users.id) NULL
```

### Activity Logs

All approval actions are logged:

```sql
- thesis_created (with status)
- thesis_approved (with approver)
- thesis_rejected (with reason)
```

---

## API Endpoints

### Public Endpoints

```
GET /api/theses
- Returns only approved theses for non-admin users
- Returns all theses with status filter for admin
```

### Protected Endpoints

```
POST /api/theses
- Creates thesis with pending status (non-admin)
- Creates thesis with approved status (admin)

POST /api/theses/{id}/approve
- Requires: canApproveTheses() permission
- Changes status to approved
- Sets approved_at and approved_by

POST /api/theses/{id}/reject
- Requires: canApproveTheses() permission
- Changes status to rejected
- Logs rejection reason
```

---

## Permissions

### canApproveTheses()

Defined in `User` model:

```php
public function canApproveTheses(): bool
{
    return $this->hasPermission('approve_thesis') || 
           $this->isAdmin() || 
           $this->isLibraryStaff();
}
```

### Role Permissions

From `RoleSeeder`:

```php
'admin' => [
    'approve_thesis',
    'manage_users',
    'manage_system',
    // ... more permissions
]

'library_staff' => [
    'approve_thesis',
    'upload_thesis',
    'manage_categories',
    // ... more permissions
]
```

---

## UI Components

### Thesis Review Page

**Features:**
- ✅ Responsive grid layout
- ✅ Professional card design
- ✅ Status badges and indicators
- ✅ Quick action buttons
- ✅ Confirmation dialogs
- ✅ Detailed thesis information
- ✅ Empty state handling
- ✅ Loading states
- ✅ Success/error notifications

**Design:**
- Material-UI components
- Consistent color scheme
- Hover effects and transitions
- Mobile-responsive
- Accessible (ARIA labels)

### Status Badges

```
🟡 Pending   - Orange badge, waiting for review
🟢 Approved  - Green badge, published
🔴 Rejected  - Red badge, needs revision
⚫ Archived  - Gray badge, archived
```

---

## Notifications

### Activity Log Entries

All actions create activity log entries:

1. **Thesis Created**
   - Message: "Submitted thesis for review: [Title]"
   - Metadata: status, uploader_role

2. **Thesis Approved**
   - Message: "Approved thesis: [Title]"
   - Metadata: approved_by, approved_at

3. **Thesis Rejected**
   - Message: "Rejected thesis: [Title]"
   - Metadata: rejected_by, reason

---

## Best Practices

### For Admins

1. **Review promptly** - Check pending theses regularly
2. **Provide detailed feedback** - When rejecting, explain why
3. **Check for duplicates** - Ensure thesis isn't already in system
4. **Verify metadata** - Confirm authors, year, department are correct
5. **Review PDF quality** - Ensure document is readable and complete

### For Faculty/Staff

1. **Complete all fields** - Provide accurate information
2. **Proofread** - Check for typos and errors
3. **Use proper format** - Follow thesis formatting guidelines
4. **Upload quality PDF** - Ensure document is clear and complete
5. **Be patient** - Wait for admin review

### For Students

1. **Get adviser approval** - Ensure thesis is ready for submission
2. **Follow guidelines** - Adhere to department requirements
3. **Check requirements** - Verify all information is correct
4. **Upload final version** - Don't submit drafts
5. **Monitor status** - Check "My Theses" for updates

---

## Troubleshooting

### Thesis Not Visible After Upload

**Cause:** Thesis is pending approval

**Solution:** Wait for admin to review and approve

### Can't Approve Theses

**Cause:** User doesn't have permission

**Solution:** Only Admin and Library Staff can approve

### Thesis Stuck in Pending

**Cause:** Admin hasn't reviewed yet

**Solution:** Contact admin or check "Thesis Review" page

### Rejected Thesis

**Cause:** Thesis didn't meet requirements

**Solution:** Check activity logs for reason, revise, and resubmit

---

## Future Enhancements

### Planned Features

1. **Email Notifications**
   - Notify admin when new thesis is submitted
   - Notify user when thesis is approved/rejected

2. **Revision System**
   - Allow users to revise rejected theses
   - Track revision history

3. **Bulk Actions**
   - Approve multiple theses at once
   - Export pending theses list

4. **Advanced Filtering**
   - Filter by uploader
   - Filter by date range
   - Filter by department

5. **Review Comments**
   - Add comments during review
   - Request specific changes
   - Track review history

---

## Summary

The approval workflow ensures:

✅ Quality control for all submissions
✅ Admin oversight of public content
✅ Clear status tracking for users
✅ Proper permissions and access control
✅ Comprehensive activity logging
✅ Professional user experience

**Key Principle:** All non-admin submissions require approval before becoming publicly visible.
