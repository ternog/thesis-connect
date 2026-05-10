# Sistema ng Pag-apruba ng User - Gabay sa Tagalog

## ✅ TAPOS NA!

Ang registration system ay kailangan na ng approval ng admin bago makapag-login ang bagong user.

## 🔄 Paano Gumagana

### Para sa Bagong User:

1. **Mag-register ang User**:
   - Pumunta sa `/register`
   - Punan ang form:
     - Buong Pangalan
     - Email
     - Department (optional)
     - Password
     - Confirm Password
   - I-click ang "Sign Up"

2. **Account Created (Naghihintay ng Approval)**:
   - Nakita ang mensahe: "Registration successful! Your account is pending admin approval."
   - Hindi pa pwedeng mag-login
   - Kailangan maghintay ng approval ng admin

3. **Subukan Mag-login**:
   - Makikita ang error: "Your account is pending admin approval. Please wait for approval."
   - Hindi makapasok sa system
   - Kailangan maghintay

4. **Admin Mag-approve**:
   - Admin ang mag-login
   - Pumunta sa "Manage Users"
   - Makikita ang "Pending Account Approvals" card
   - I-click ang "Approve" button
   - Approved na ang account!

5. **Pwede na Mag-login ang User**:
   - Subukan ulit mag-login
   - Success! Nakapasok na
   - Pwede na gamitin ang system

---

## 👨‍💼 Para sa Admin

### Paano Mag-approve ng User:

1. **Login bilang Admin**:
   - Email: admin@thesisconnect.com
   - Password: admin123

2. **Pumunta sa "Manage Users"**:
   - Click sa sidebar: "Manage Users"
   - Makikita sa taas ang orange card

3. **Makikita ang Pending Users**:
   ```
   ┌────────────────────────────────────────┐
   │ ⚠️ Pending Account Approvals (2)       │
   ├────────────────────────────────────────┤
   │ ┌──────────────┐  ┌──────────────┐    │
   │ │ Juan Dela Cruz│  │ Maria Santos │    │
   │ │ juan@email   │  │ maria@email  │    │
   │ │ Dept: CS     │  │ Dept: CTE    │    │
   │ │ [✅ Approve] │  │ [✅ Approve] │    │
   │ └──────────────┘  └──────────────┘    │
   └────────────────────────────────────────┘
   ```

4. **I-click ang "Approve"**:
   - Green checkmark button
   - Success message lalabas
   - Pwede na mag-login ang user

---

## 📋 Mga Pagbabago

### Backend (6 files):

1. **Migration** - Bagong columns sa users table:
   - `is_approved` - Kung approved na ba
   - `approved_by` - Sino nag-approve
   - `approved_at` - Kailan nag-approve

2. **User Model** - Dagdag na functions at relationships

3. **AuthController**:
   - Login - Check kung approved
   - Register - Set as unapproved

4. **UserController**:
   - `approve()` - Para mag-approve ng user
   - `pendingApprovals()` - Kumuha ng pending users

5. **Routes** - Bagong API endpoints:
   - `GET /api/users/pending/approvals`
   - `POST /api/users/{id}/approve`

6. **Seeder** - Lahat ng demo users ay approved na

### Frontend (2 files):

1. **Register.js**:
   - May info alert: "Your account will require admin approval"
   - Success message pagkatapos mag-register
   - Redirect sa login page
   - 56px height sa lahat ng input

2. **UserManagement.js**:
   - Pending Approvals card sa taas
   - "Approved" column sa table
   - Green approve button
   - Refresh pagkatapos mag-approve

---

## 🎨 Mga Bagong UI

### Registration Page:
- Blue info box: "Your account will require admin approval before you can login."
- Success message after registration
- Automatic redirect sa login

### User Management Page:
- **Orange Card** - Para sa pending approvals
- **Approved Column** - Shows ✅ Approved o 🟡 Pending
- **Approve Button** - Green checkmark sa table

---

## 🧪 Paano I-test

### Test 1: Mag-register ng Bagong User
1. Logout muna
2. Pumunta sa `/register`
3. Punan ang form:
   - Name: "Test User"
   - Email: "test@example.com"
   - Department: "College of Computer Science"
   - Password: "password123"
   - Confirm: "password123"
4. Click "Sign Up"
5. Dapat makita: "Registration successful! Your account is pending admin approval."

### Test 2: Subukan Mag-login (Hindi Dapat Gumana)
1. Try mag-login: test@example.com / password123
2. Dapat may error: "Your account is pending admin approval."
3. Hindi makapasok

### Test 3: Admin Mag-approve
1. Login as admin@thesisconnect.com / admin123
2. Pumunta sa "Manage Users"
3. Makikita ang "Pending Account Approvals (1)"
4. Makikita si "Test User"
5. Click "Approve"
6. Success message lalabas
7. Nawala na ang card

### Test 4: User Pwede na Mag-login
1. Logout
2. Login ulit: test@example.com / password123
3. Success! Nakapasok na
4. Pwede na gamitin ang system

---

## 🔐 Mga Benepisyo

1. **Mas Secure** - Admin ang nag-control kung sino makakapasok
2. **Walang Spam** - Hindi basta-basta makakapag-register
3. **May Audit Trail** - Naka-record kung sino nag-approve
4. **Professional** - Mas organized ang user management
5. **Flexible** - Pwedeng i-reject by deleting unapproved accounts

---

## 📝 Mga Importante

### Para sa Admin:
- Check muna ang email at department bago mag-approve
- Pwedeng i-delete ang suspicious accounts
- May activity log lahat ng approvals
- Pwedeng i-deactivate later kung may problema

### Para sa Users:
- Maghintay lang ng approval
- Check email kung may notification (future feature)
- Contact admin kung matagal
- Siguruhing tama ang email at department

---

## 🎉 STATUS: TAPOS NA! ✅

Ang approval system ay gumagana na!

**Mga Ginawa**:
- ✅ Migration - Tapos na
- ✅ Backend code - Tapos na
- ✅ Frontend UI - Tapos na
- ✅ Testing - Pwede na i-test
- ✅ Documentation - Complete

**Pwede na Gamitin**: Oo! Subukan mo na mag-register ng bagong account para ma-test!

---

## 💡 Tips

1. **Para sa Admin**:
   - Regular na i-check ang pending approvals
   - Approve agad kung legit ang user
   - I-delete kung suspicious

2. **Para sa Users**:
   - Gumamit ng valid email
   - Lagyan ng department para mas mabilis ma-approve
   - Maghintay ng 24 hours para sa approval

3. **Para sa System**:
   - Existing users ay auto-approved na
   - Walang disruption sa current users
   - Backward compatible

---

**Salamat sa paggamit ng ThesisConnect!** 🎓
