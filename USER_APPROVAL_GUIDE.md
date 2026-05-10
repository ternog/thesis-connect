# User Registration Approval System

## 🎯 Overview

The registration system now requires admin approval before new users can login. This adds an extra security layer to control who can access the system.

## 🔄 How It Works

### For New Users (Registration Flow):

1. **User Registers**:
   - Goes to `/register`
   - Fills out registration form:
     - Full Name
     - Email
     - Department (optional)
     - Password
     - Confirm Password
   - Clicks "Sign Up"

2. **Account Created (Pending)**:
   - Account is created with `is_approved = false`
   - User sees success message: "Registration successful! Your account is pending admin approval."
   - User is redirected to login page
   - User CANNOT login yet

3. **User Tries to Login**:
   - If account not approved yet, sees error:
     - "Your account is pending admin approval. Please wait for approval."
   - Must wait for admin to approve

4. **Admin Approves**:
   - Admin logs in
   - Goes to "Manage Users"
   - Sees "Pending Account Approvals" card at top
   - Clicks "Approve" button
   - User account is now approved

5. **User Can Now Login**:
   - User tries login again
   - Successfully logs in
   - Can access the system

---

## 👨‍💼 For Admins (Approval Flow)

### Step 1: View Pending Approvals

When you login as admin and go to **Manage Users** (`/admin/users`), you'll see:

**Pending Account Approvals Card** (if there are pending users):
```
┌─────────────────────────────────────────────────┐
│ ⚠️ Pending Account Approvals (3)                │
├─────────────────────────────────────────────────┤
│ ┌──────────────┐ ┌──────────────┐ ┌──────────┐ │
│ │ Juan Dela Cruz│ │ Maria Santos │ │ Pedro Go │ │
│ │ juan@email   │ │ maria@email  │ │ pedro@   │ │
│ │ Dept: CS     │ │ Dept: CTE    │ │ Dept: N/A│ │
│ │ [✅ Approve] │ │ [✅ Approve] │ │ [✅Approve]│ │
│ └──────────────┘ └──────────────┘ └──────────┘ │
└─────────────────────────────────────────────────┘
```

### Step 2: Approve Users

Click the **"Approve"** button on any pending user:
- User's `is_approved` status changes to `true`
- User's `approved_by` is set to your user ID
- User's `approved_at` is set to current timestamp
- Activity log is created
- User can now login

### Step 3: View Approval Status in Table

In the users table, you'll see a new **"Approved"** column:
- 🟢 **Approved** - User can login
- 🟡 **Pending** - User cannot login yet

---

## 🗄️ Database Changes

### New Migration: `2026_03_27_000001_add_approval_to_users_table.php`

Added 3 new columns to `users` table:
- `is_approved` (boolean, default: false) - Whether user is approved
- `approved_by` (foreign key to users) - Which admin approved
- `approved_at` (timestamp) - When user was approved

---

## 🔧 Backend Changes

### 1. AuthController.php

**Login Method** - Added approval check:
```php
if (!$user->is_approved) {
    throw ValidationException::withMessages([
        'email' => ['Your account is pending admin approval. Please wait for approval.'],
    ]);
}
```

**Register Method** - Set new users as unapproved:
```php
$user = User::create([
    // ... other fields
    'is_approved' => false, // Requires admin approval
]);

return response()->json([
    'message' => 'Registration successful! Your account is pending admin approval.',
    'user' => $user->load('role'),
], 201);
```

### 2. UserController.php

**New Methods**:
- `pendingApprovals()` - Get list of unapproved users
- `approve(User $user)` - Approve a user account

### 3. User Model

**New Relationships**:
- `approver()` - BelongsTo relationship to user who approved
- `approvedUsers()` - HasMany relationship to users this admin approved

**New Scopes**:
- `scopeApproved()` - Filter approved users
- `scopePendingApproval()` - Filter unapproved users

**Updated Fillable**:
- Added: `is_approved`, `approved_by`, `approved_at`

**Updated Casts**:
- Added: `is_approved` (boolean), `approved_at` (datetime)

---

## 🌐 Frontend Changes

### 1. Register.js

**Changes**:
- Added info alert: "Your account will require admin approval before you can login."
- Updated submit handler to show approval message
- No longer auto-logs in after registration
- Redirects to login page with success message
- All password fields now have 56px height

### 2. UserManagement.js

**New Features**:
- **Pending Approvals Card** - Shows unapproved users at top
- **Approve Button** - Green checkmark button to approve users
- **Approved Column** - Shows approval status in table
- **fetchPendingApprovals()** - Fetches unapproved users
- **handleApproveUser()** - Approves a user

**UI Updates**:
- Orange alert card for pending approvals
- Approve button with CheckCircle icon
- Status chip showing "Approved" or "Pending"

---

## 🔗 API Endpoints

### New Endpoints:
- `GET /api/users/pending/approvals` - Get unapproved users (admin only)
- `POST /api/users/{id}/approve` - Approve a user (admin only)

### Updated Endpoints:
- `POST /api/register` - Now returns message instead of token
- `POST /api/login` - Now checks `is_approved` status

---

## 🧪 Testing the Approval System

### Test 1: Register New User
1. Logout (if logged in)
2. Go to `/register`
3. Fill form:
   - Name: "Test User"
   - Email: "test@example.com"
   - Department: "College of Computer Science"
   - Password: "password123"
   - Confirm Password: "password123"
4. Click "Sign Up"
5. Should see: "Registration successful! Your account is pending admin approval."
6. Redirected to login page

### Test 2: Try to Login (Should Fail)
1. Try to login with test@example.com / password123
2. Should see error: "Your account is pending admin approval. Please wait for approval."
3. Cannot access system

### Test 3: Admin Approves User
1. Login as admin@thesisconnect.com / admin123
2. Go to "Manage Users"
3. See "Pending Account Approvals (1)" card at top
4. See "Test User" in the card
5. Click "Approve" button
6. Success message: "User approved successfully"
7. Card disappears (no more pending users)
8. In users table, "Test User" shows "Approved" chip

### Test 4: User Can Now Login
1. Logout
2. Login with test@example.com / password123
3. Login successful!
4. Can access dashboard and system

---

## 🔐 Security Benefits

1. **Prevents Spam Accounts** - Admins control who can access
2. **Verifies Identity** - Can check email/department before approving
3. **Audit Trail** - Activity log records who approved whom
4. **Controlled Access** - Only legitimate users get access
5. **Prevents Abuse** - Stops unauthorized registrations

---

## 📊 Admin Dashboard Impact

Admins will now see:
- **Pending Approvals Count** - Number of users waiting
- **Pending Users Card** - Quick access to approve
- **Approval Status Column** - In users table
- **Approve Button** - Quick action in table
- **Activity Logs** - Records all approvals

---

## ⚙️ Configuration

### Auto-Approve Certain Roles (Optional)

If you want to auto-approve certain email domains (e.g., @mbc.edu.ph), you can modify `AuthController.php`:

```php
public function register(Request $request)
{
    // Auto-approve if email ends with @mbc.edu.ph
    $isApproved = str_ends_with($request->email, '@mbc.edu.ph');
    
    $user = User::create([
        // ... other fields
        'is_approved' => $isApproved,
    ]);
    
    $message = $isApproved 
        ? 'Registration successful! You can now login.'
        : 'Registration successful! Your account is pending admin approval.';
    
    return response()->json(['message' => $message, 'user' => $user], 201);
}
```

---

## 🎉 Status: IMPLEMENTED ✅

The user approval system is now fully functional!

**What Changed**:
- ✅ New users require admin approval
- ✅ Pending approvals shown to admins
- ✅ One-click approval process
- ✅ Activity logging for approvals
- ✅ Clear error messages for unapproved users
- ✅ Existing users auto-approved (backward compatible)

**To Use**:
1. Run migration: `php artisan migrate` ✅ (Already done)
2. Update existing users: ✅ (Already done)
3. Test registration flow
4. Test approval flow
5. Test login with approved/unapproved accounts
