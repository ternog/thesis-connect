# Complete Approval Workflow Guide

## Overview
ALL thesis submissions (from students, faculty, and library staff) now require admin approval before being visible to the public.

## Workflow

### 1. Thesis Submission
**Who can submit:**
- Students
- Faculty
- Library Staff
- Researchers

**Process:**
1. User uploads thesis with all required information
2. System automatically sets status to "pending"
3. User receives confirmation message
4. Thesis is NOT visible to public users
5. Activity log records the submission

### 2. Admin Review
**Who can approve:**
- Only users with Admin role

**Review Process:**
1. Admin navigates to "Thesis Review & Approval" page
2. Views list of all pending theses
3. Can filter by:
   - Status (pending/approved/rejected)
   - Uploader role
   - Date submitted
   - Department/Program

4. For each thesis, admin can:
   - View full details
   - Check plagiarism
   - Review content
   - Approve or reject

### 3. Approval Actions

#### Approve Thesis
1. Admin clicks "Approve" button
2. System updates:
   - Status → "approved"
   - Approved by → Admin user ID
   - Approved at → Current timestamp
3. Thesis becomes visible to public
4. Activity log records approval
5. Uploader can see their thesis is approved

#### Reject Thesis
1. Admin clicks "Reject" button
2. Optional: Add rejection reason
3. System updates:
   - Status → "rejected"
4. Thesis remains hidden from public
5. Activity log records rejection
6. Uploader can see rejection and reason

### 4. Post-Approval

**Approved Theses:**
- Visible in public browse page
- Searchable by all users
- Downloadable by authenticated users
- Included in recommendations
- Counted in statistics

**Rejected Theses:**
- Hidden from public
- Only visible to uploader and admin
- Can be edited and resubmitted
- Requires new approval after edit

## User Roles and Permissions

### Admin
- ✅ Upload thesis (requires approval)
- ✅ Approve/reject any thesis
- ✅ View all theses (any status)
- ✅ Edit any thesis
- ✅ Delete any thesis
- ✅ View approval queue
- ✅ Check plagiarism

### Library Staff
- ✅ Upload thesis (requires approval)
- ❌ Cannot approve theses
- ✅ View approved theses
- ✅ View own pending theses
- ✅ Edit own theses
- ✅ Check plagiarism

### Faculty
- ✅ Upload thesis (requires approval)
- ❌ Cannot approve theses
- ✅ View approved theses
- ✅ View own pending theses
- ✅ Edit own theses
- ✅ Check plagiarism

### Student/Researcher
- ❌ Cannot upload thesis
- ✅ View approved theses
- ✅ Download theses
- ✅ View recommendations

## Status Flow

```
[Upload] → PENDING → [Admin Review] → APPROVED → [Public]
                                    ↓
                                 REJECTED → [Edit & Resubmit]
```

## Notification System (Future Enhancement)

### Email Notifications
1. **On Submission:**
   - To Admin: "New thesis pending approval"
   - To Uploader: "Thesis submitted successfully"

2. **On Approval:**
   - To Uploader: "Your thesis has been approved"

3. **On Rejection:**
   - To Uploader: "Your thesis requires revision"
   - Include rejection reason

## API Endpoints

### Submit Thesis
```
POST /api/theses
Status: Always set to "pending"
Response: Confirmation message
```

### Approve Thesis
```
POST /api/theses/{id}/approve
Permission: Admin only
Updates: status, approved_by, approved_at
```

### Reject Thesis
```
POST /api/theses/{id}/reject
Permission: Admin only
Body: { reason: "optional rejection reason" }
```

### Get Pending Theses
```
GET /api/theses?status=pending
Permission: Admin only
Returns: All pending theses
```

## Frontend Pages

### 1. Thesis Review & Approval (Admin Only)
**Path:** `/admin/reviews`

**Features:**
- List all pending theses
- Filter by status, uploader, date
- Quick approve/reject buttons
- View thesis details
- Check plagiarism
- Add review comments

### 2. My Theses (All Users)
**Path:** `/my-theses`

**Features:**
- View own submitted theses
- See approval status
- Edit pending/rejected theses
- View rejection reasons
- Resubmit after edits

### 3. Browse Theses (Public)
**Path:** `/theses`

**Features:**
- Only shows approved theses
- Search and filter
- Download PDFs
- View details

## Database Schema

### theses Table
```sql
- status: enum('pending', 'approved', 'rejected', 'archived')
- approved_by: foreign key to users (nullable)
- approved_at: timestamp (nullable)
- uploaded_by: foreign key to users
```

### Activity Logs
All approval actions are logged:
- Thesis submission
- Approval action
- Rejection action
- Status changes

## Best Practices

### For Uploaders
1. Ensure all information is complete
2. Run plagiarism check before submission
3. Review content for accuracy
4. Wait for admin approval
5. Check status regularly

### For Admins
1. Review submissions promptly
2. Check plagiarism before approval
3. Provide clear rejection reasons
4. Maintain approval standards
5. Document review process

## Testing Checklist

- [ ] Student cannot upload (correct)
- [ ] Faculty upload goes to pending
- [ ] Library staff upload goes to pending
- [ ] Pending thesis not visible to public
- [ ] Admin can see pending theses
- [ ] Admin can approve thesis
- [ ] Approved thesis visible to public
- [ ] Admin can reject thesis
- [ ] Rejected thesis hidden from public
- [ ] Uploader can see own pending theses
- [ ] Activity logs record all actions

## Troubleshooting

### Thesis Not Appearing After Upload
- Check status is "pending"
- Verify user has upload permission
- Check activity logs for errors

### Cannot Approve Thesis
- Verify user is admin
- Check thesis exists
- Review Laravel logs

### Approved Thesis Not Visible
- Verify status is "approved"
- Clear browser cache
- Check database directly

## Configuration

### Change Approval Requirements
Edit `ThesisController.php`:
```php
// Current: All uploads require approval
'status' => 'pending',

// To auto-approve library staff:
'status' => $request->user()->isLibraryStaff() ? 'approved' : 'pending',
```

### Change Who Can Approve
Edit `User.php` model:
```php
public function canApproveTheses(): bool
{
    // Current: Only admin
    return $this->isAdmin();
    
    // To allow library staff:
    return $this->isAdmin() || $this->isLibraryStaff();
}
```

## Summary

The approval workflow ensures quality control by requiring admin review of all thesis submissions before public visibility. This protects the integrity of the repository and ensures only verified, quality content is accessible to users.
