# ThesisConnect CMS - Quick Reference Card

## 🚀 Start Commands

```bash
# Backend
cd thesis-system/backend
php artisan serve

# Frontend
cd thesis-system/frontend
npm start
```

## 🔑 Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@mbc.edu.ph | admin123 |
| Librarian | librarian@mbc.edu.ph | librarian123 |
| Faculty | faculty@mbc.edu.ph | faculty123 |

## 📍 CMS Pages

| Feature | URL | Access |
|---------|-----|--------|
| User Management | `/admin/users` | Admin only |
| Author Management | `/admin/authors` | Admin only |
| Category Management | `/admin/categories` | Admin only |
| Thesis Reviews | `/admin/reviews` | Admin/Librarian |
| Tracking & Monitoring | `/admin/tracking` | Admin only |
| Activity Logs | `/admin/activity-logs` | Admin only |
| Analytics | `/admin/analytics` | Admin only |

## 🎯 Quick Actions

### Add New User
1. Go to `/admin/users`
2. Click "Add User"
3. Fill form (name, email, password, role)
4. Click "Create"

### Add New Author
1. Go to `/admin/authors`
2. Click "Add Author"
3. Enter name: "Last Name, First Name M."
4. Click "Create"

### Add New Category
1. Go to `/admin/categories`
2. Click "Add Category"
3. Enter name and description
4. Click "Create"

### Review a Thesis
1. Go to `/admin/reviews`
2. Click "Assign Reviewer" on pending thesis
3. Select reviewer
4. Reviewer opens review
5. Provide feedback and decision
6. Click "Submit Review"

### View Statistics
1. Go to `/admin/tracking`
2. View dashboard metrics
3. Check top 10 theses
4. Filter recent activity

## 🎓 Departments

1. College of Computer Science
2. College of Teacher Education
3. College of Criminal Justice
4. College of Art and Sciences
5. College of Business Management
6. Institute of Fisheries

## 🎨 Design Standards

- Input Height: **56px**
- Primary Color: **#2e7d32** (Green)
- Button Height: **48-56px**
- Font Size: **1rem**

## ✅ Status: ALL COMPLETE

All CMS features are implemented and ready to use!
