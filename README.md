# ThesisConnect - MBC Library Digital Thesis Database and PDF Repository System

A comprehensive web-based thesis repository system with role-based access control, advanced search capabilities, and document management features.

## Features

### Role-Based Access Control
- **Admin/Library Staff**: Full system management, user management, thesis approval
- **Faculty**: Upload own research/thesis
- **Researchers/Students**: Search, view, and download authorized documents

### Thesis Repository Features
- PDF document upload only
- Multiple authors support per thesis
- Comprehensive metadata fields (title, authors, adviser, year, department, program, etc.)
- Academic level classification (Undergraduate/Graduate)
- Document type classification (Student Thesis/Faculty Research)
- Keywords and abstract indexing for fast search
- Duplicate prevention based on title and authors
- Download tracking
- Version control for documents

### Search System
- Advanced search by title, author, abstract, keywords
- Filters: Year, Department, Program, Academic Level, Document Type
- Fast indexed search
- Pagination support

### Dashboard and Monitoring
- Admin dashboard with system statistics
- User-specific statistics
- Recent activity tracking
- Download count monitoring
- Thesis approval workflow

### Security
- Laravel Sanctum authentication
- Password hashing (bcrypt)
- Role-based access control
- File upload validation (PDF only, max 10MB)
- CSRF protection
- SQL injection prevention
- XSS protection

## Technology Stack

### Backend
- Laravel 11
- PHP 8.2+
- SQLite (can be switched to MySQL/PostgreSQL)
- Laravel Sanctum for API authentication

### Frontend
- React 19
- Material-UI (MUI)
- React Router
- Axios for API calls
- React Hook Form for form management

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 16+ and npm
- Git

### Backend Setup

1. Navigate to the backend directory:
```bash
cd thesis-system/backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. The database is already configured with SQLite. Migrations and seeders have been run.

4. Create storage link:
```bash
php artisan storage:link
```

5. Start the Laravel development server:
```bash
php artisan serve
```

The backend API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd thesis-system/frontend
```

2. Install Node dependencies:
```bash
npm install
```

3. Start the React development server:
```bash
npm start
```

The frontend will be available at `http://localhost:3000`

## Default User Accounts

The system comes with pre-seeded user accounts for testing:

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@thesisconnect.com | admin123 |
| Library Staff | librarian@thesisconnect.com | librarian123 |
| Student | student@thesisconnect.com | student123 |

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout
- `GET /api/me` - Get current user

### Theses
- `GET /api/theses` - List theses (with search and filters)
- `POST /api/theses` - Create new thesis
- `GET /api/theses/{id}` - Get thesis details
- `PUT /api/theses/{id}` - Update thesis
- `DELETE /api/theses/{id}` - Delete thesis
- `POST /api/theses/{id}/approve` - Approve thesis
- `POST /api/theses/{id}/reject` - Reject thesis

### Documents
- `POST /api/theses/{id}/documents` - Upload document
- `GET /api/documents/{id}/download` - Download document
- `DELETE /api/documents/{id}` - Delete document

### Categories
- `GET /api/categories` - List categories
- `POST /api/categories` - Create category (admin only)
- `PUT /api/categories/{id}` - Update category (admin only)
- `DELETE /api/categories/{id}` - Delete category (admin only)

### Users
- `GET /api/users` - List users (admin only)
- `POST /api/users` - Create user (admin only)
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user (admin only)

### Dashboard
- `GET /api/dashboard/stats` - System statistics
- `GET /api/dashboard/user-stats` - User statistics
- `GET /api/dashboard/activity` - Recent activity
- `GET /api/dashboard/charts` - Chart data

## Database Schema

### Main Tables
- `users` - User accounts with role assignments
- `roles` - User roles and permissions
- `theses` - Thesis metadata and information
- `documents` - PDF files linked to theses
- `categories` - Subject classifications
- `personal_access_tokens` - API authentication tokens

## File Storage

PDF documents are stored in `storage/app/public/documents/` with unique filenames to prevent conflicts.

## Security Features

1. **Authentication**: Token-based authentication using Laravel Sanctum
2. **Authorization**: Role-based access control with granular permissions
3. **File Validation**: Only PDF files up to 10MB are allowed
4. **Duplicate Prevention**: Hash-based duplicate file detection
5. **SQL Injection Protection**: Laravel's query builder and Eloquent ORM
6. **XSS Protection**: Input sanitization and output escaping
7. **CSRF Protection**: Built-in Laravel CSRF protection

## Development Notes

### Adding New Roles
Edit `database/seeders/RoleSeeder.php` and run:
```bash
php artisan db:seed --class=RoleSeeder
```

### Adding New Categories
Edit `database/seeders/CategorySeeder.php` and run:
```bash
php artisan db:seed --class=CategorySeeder
```

### Switching to MySQL
1. Update `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_connect
DB_USERNAME=root
DB_PASSWORD=your_password
```

2. Run migrations:
```bash
php artisan migrate:fresh --seed
```

## Troubleshooting

### CORS Issues
Make sure `FRONTEND_URL` in `.env` matches your React app URL (default: http://localhost:3000)

### File Upload Issues
Ensure the storage directory is writable:
```bash
chmod -R 775 storage
```

### Database Issues
Reset the database:
```bash
php artisan migrate:fresh --seed
```

## Future Enhancements

- Full-text search using Laravel Scout
- Email notifications for thesis approval
- Advanced analytics and reporting
- Batch document upload
- Document preview functionality
- Export functionality (CSV, Excel)
- Advanced user management interface
- Category management interface
- Activity logs and audit trails

## License

This project is developed for MBC Library.

## Support

For issues and questions, please contact the development team.