# ThesisConnect - Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Prerequisites
- PHP 8.2+ installed
- Composer installed
- Node.js 16+ and npm installed

---

## 📦 Installation (Already Done!)

The system is already set up and running. Both servers are active:

✅ **Backend API**: http://127.0.0.1:8000
✅ **Frontend App**: http://localhost:3000

---

## 🔑 Demo Accounts

Use these accounts to test the system:

### 1. Administrator (Full Access)
```
Email: admin@thesisconnect.com
Password: admin123
```
**Can do:**
- Manage all users
- Approve/reject theses
- Upload theses
- View all statistics
- Full system control

### 2. Library Staff (Thesis Management)
```
Email: librarian@thesisconnect.com
Password: librarian123
```
**Can do:**
- Approve/reject theses
- Upload theses
- Edit metadata
- View reports

### 3. Student (Browse & Download)
```
Email: student@thesisconnect.com
Password: student123
```
**Can do:**
- Browse theses
- Search and filter
- View details
- Download approved documents

---

## 🎯 Key Features to Test

### 1. Browse Theses
1. Go to http://localhost:3000
2. Click "Browse Theses" in the sidebar (hamburger menu)
3. Use search and filters
4. Click on any thesis to view details

### 2. Upload a Thesis
1. Login as Admin or Library Staff
2. Click hamburger menu → "Upload Thesis"
3. Fill in the form:
   - **Title**: Enter thesis title
   - **Authors**: Add one or more authors
   - **Year**: Select year
   - **Adviser**: Enter adviser name
   - **Department**: Select from dropdown (e.g., College of Engineering and Technology)
   - **Program**: Select from dropdown (e.g., Bachelor of Science in Information Technology)
   - **Academic Level**: Undergraduate or Graduate
   - **Document Type**: Student Thesis or Faculty Research
   - **Abstract**: Write a summary
   - **Keywords**: Add relevant keywords
   - **Upload PDF**: Click the upload box and select a PDF file
4. Click "Submit Thesis"

### 3. Approve/Reject Theses (Admin/Library Staff)
1. Login as Admin or Library Staff
2. Go to "Browse Theses"
3. Find pending theses
4. Click on a thesis
5. Use the approve/reject buttons

### 4. View Dashboard
1. Login with any account
2. Click "Dashboard" in sidebar
3. View your statistics
4. See recent activity

### 5. Search & Filter
1. Go to "Browse Theses"
2. Use the search box to search by:
   - Title
   - Author
   - Abstract
   - Keywords
3. Apply filters:
   - Year
   - Department
   - Program
   - Academic Level
   - Document Type
4. Click "Search"

---

## 🎨 UI Features

### Collapsible Sidebar
- **Open**: Click hamburger menu (☰) or swipe from left
- **Close**: Click outside, press ESC, or swipe left
- **Auto-close**: Automatically closes when you navigate

### Responsive Design
- **Mobile**: Optimized for phones
- **Tablet**: Perfect for tablets
- **Desktop**: Full-featured experience

### Professional Theme
- Green color scheme (MSU-inspired)
- Smooth animations
- Modern card designs
- Clean typography

---

## 📋 MSU Bongabong Campus Programs

### Departments Available:
1. College of Fisheries
2. College of Education
3. College of Criminal Justice Education
4. College of Engineering and Technology
5. College of Hospitality Management and Tourism
6. College of Business and Entrepreneurship
7. College of Arts and Sciences

### Programs Available:
1. Bachelor of Science in Fisheries
2. Bachelor of Elementary Education
3. Bachelor of Secondary Education
4. Bachelor of Science in Criminology
5. Bachelor of Science in Computer Engineering
6. Bachelor of Science in Information Technology
7. Bachelor of Science in Hospitality Management
8. Bachelor of Science in Tourism Management
9. Bachelor of Science in Entrepreneurship
10. Bachelor of Arts in Political Science

---

## 🔧 If Servers Are Not Running

### Start Backend:
```bash
cd thesis-system/backend
php artisan serve
```

### Start Frontend:
```bash
cd thesis-system/frontend
npm start
```

---

## 📱 Access Points

### Main Application
- **URL**: http://localhost:3000
- **Login**: Click "Login" button in top-right
- **Register**: Click "Register" button

### API Endpoints
- **Base URL**: http://127.0.0.1:8000/api
- **Login**: POST /api/login
- **Theses**: GET /api/theses
- **Upload**: POST /api/theses

---

## 🎓 Common Tasks

### As Admin/Library Staff:

**1. Manage Users**
- Sidebar → "Manage Users"
- View all users
- Create new users
- Edit user roles

**2. Approve Theses**
- Sidebar → "Browse Theses"
- Filter by status: "Pending"
- Click thesis → Click "Approve"

**3. View Statistics**
- Sidebar → "Dashboard"
- See system-wide statistics
- View recent activity

### As Faculty:

**1. Upload Research**
- Sidebar → "Upload Thesis"
- Select "Faculty Research" as document type
- Fill in your research details
- Upload PDF

**2. View My Theses**
- Sidebar → "My Theses"
- See all your submissions
- Edit or delete your theses

### As Student/Researcher:

**1. Search Theses**
- Sidebar → "Browse Theses"
- Use search and filters
- Find relevant research

**2. Download Documents**
- Click on any thesis
- Click "Download PDF" button
- PDF opens in new tab

---

## 💡 Tips & Tricks

### Search Tips:
- Search by author name to find all their work
- Use keywords for topic-based search
- Combine filters for precise results
- Use year filter for recent research

### Upload Tips:
- Prepare your PDF before starting (max 10MB)
- Have all metadata ready (authors, keywords, etc.)
- Use descriptive keywords for better discoverability
- Write a clear, concise abstract

### Navigation Tips:
- Use hamburger menu (☰) to access all features
- Sidebar auto-closes after navigation
- Swipe from left edge on mobile to open sidebar
- Press ESC to close sidebar

---

## 🐛 Troubleshooting

### Can't Login?
- Check if you're using the correct email and password
- Try the demo accounts listed above
- Make sure backend server is running

### Can't Upload PDF?
- Check file size (must be under 10MB)
- Ensure file is PDF format
- Check if you have upload permissions

### Sidebar Won't Open?
- Click the hamburger menu (☰) in top-left
- Try swiping from left edge
- Refresh the page if needed

### Search Not Working?
- Make sure you clicked "Search" button
- Try clearing filters
- Check if backend server is running

---

## 📞 Need Help?

### Documentation:
- **README.md** - Setup instructions
- **IMPLEMENTATION_SUMMARY.md** - Feature list
- **THEME_UPDATE.md** - Design details
- **FINAL_IMPROVEMENTS.md** - Complete overview

### Check Server Status:
```bash
# Backend should show: Server running on [http://127.0.0.1:8000]
# Frontend should show: webpack compiled successfully
```

---

## ✅ System Status

Current Status: **✅ FULLY OPERATIONAL**

- ✅ Backend API running
- ✅ Frontend app running
- ✅ Database configured
- ✅ Sample data loaded
- ✅ All features working
- ✅ Ready for use

---

## 🎉 Enjoy ThesisConnect!

The system is ready to use. Start by logging in with one of the demo accounts and explore all the features!

**Happy researching! 📚**

---

**Mindoro State University - Bongabong Campus**
**ThesisConnect - MBC Library Digital Thesis Database**