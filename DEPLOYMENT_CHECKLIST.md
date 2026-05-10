# ThesisConnect v2.0 - Deployment Checklist

## 📋 Pre-Deployment Checklist

### 1. Backup Current System
```bash
# Backup database
cp thesis-system/backend/database/database.sqlite thesis-system/backend/database/database.backup.$(date +%Y%m%d).sqlite

# Backup uploaded files
cp -r thesis-system/backend/storage/app/public/documents thesis-system/backend/storage/app/public/documents.backup.$(date +%Y%m%d)

# Backup .env file
cp thesis-system/backend/.env thesis-system/backend/.env.backup
```

### 2. Verify System Requirements
- [ ] PHP 8.2 or higher installed
- [ ] Composer installed
- [ ] Node.js 18+ and npm installed
- [ ] SQLite extension enabled (or MySQL/PostgreSQL configured)
- [ ] Sufficient disk space (minimum 1GB free)
- [ ] Write permissions on storage directories

### 3. Review Configuration
- [ ] Check `.env` file settings
- [ ] Verify database connection
- [ ] Confirm CORS settings
- [ ] Review file upload limits
- [ ] Check cache configuration

---

## 🚀 Deployment Steps

### Step 1: Update Backend

```bash
cd thesis-system/backend

# Pull latest code (if using git)
# git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev

# Run new migrations
php artisan migrate

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage link exists
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

### Step 2: Update Frontend

```bash
cd thesis-system/frontend

# Install/update dependencies
npm install

# Build for production
npm run build

# The build folder can be served by any static file server
```

### Step 3: Verify Migrations

```bash
cd thesis-system/backend

# Check migration status
php artisan migrate:status

# Expected new tables:
# - activity_logs
# - authors
# - author_thesis
# - thesis_reviews
# - thesis_revisions
# - thesis_views
# - thesis_downloads
# - user_favorites
```

### Step 4: Test New Features

```bash
# Start backend
php artisan serve

# In another terminal, start frontend
cd thesis-system/frontend
npm start

# Run through test checklist (see below)
```

---

## ✅ Post-Deployment Testing

### Critical Features Test (5 minutes)

1. **Login Test**
   - [ ] Admin login works
   - [ ] Librarian login works
   - [ ] Student login works

2. **Basic Operations**
   - [ ] View thesis list
   - [ ] Search theses
   - [ ] View thesis details
   - [ ] Download PDF

3. **New Features Quick Test**
   - [ ] Activity logs page loads
   - [ ] View count increments
   - [ ] Download tracking works
   - [ ] Recommendations appear

### Full Feature Test (30 minutes)

#### Performance
- [ ] Page loads in < 2 seconds
- [ ] Search responds in < 500ms
- [ ] Dashboard loads in < 2 seconds
- [ ] No console errors

#### Author Management
- [ ] Create author with standard format
- [ ] Search authors works
- [ ] Autocomplete suggestions work
- [ ] Author profile displays

#### Activity Logs
- [ ] Logs are being created
- [ ] Filter by date works
- [ ] Export to CSV works
- [ ] User attribution correct

#### Review System
- [ ] Assign reviewer works
- [ ] Submit review works
- [ ] Request revision works
- [ ] Status updates correctly

#### Tracking
- [ ] View count increments
- [ ] Download count increments
- [ ] User attribution works
- [ ] IP addresses logged

#### PDF Viewer
- [ ] Opens in browser
- [ ] Zoom controls work
- [ ] Download button works
- [ ] Print functionality works

#### Recommendations
- [ ] Personalized recommendations show
- [ ] Trending theses display
- [ ] Related theses appear
- [ ] Recently viewed tracked

#### Favorites
- [ ] Add to favorites works
- [ ] Remove from favorites works
- [ ] Favorites list displays

---

## 🔍 Database Verification

```bash
# Access database
sqlite3 thesis-system/backend/database/database.sqlite

# Verify new tables exist
.tables

# Check activity logs
SELECT COUNT(*) FROM activity_logs;

# Check authors
SELECT COUNT(*) FROM authors;

# Check views
SELECT COUNT(*) FROM thesis_views;

# Check downloads
SELECT COUNT(*) FROM thesis_downloads;

# Exit
.quit
```

---

## 🐛 Troubleshooting

### Issue: Migrations Fail

**Symptoms:**
- Error when running `php artisan migrate`
- Tables not created

**Solutions:**
```bash
# Option 1: Fresh migration (WARNING: Deletes all data)
php artisan migrate:fresh --seed

# Option 2: Rollback and re-run
php artisan migrate:rollback
php artisan migrate

# Option 3: Check specific migration
php artisan migrate --path=/database/migrations/2026_03_25_000001_create_activity_logs_table.php
```

### Issue: Cache Not Working

**Symptoms:**
- Old data still showing
- Changes not reflected

**Solutions:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart server
php artisan serve
```

### Issue: Activity Logs Not Appearing

**Symptoms:**
- No logs in activity_logs table
- Activity logs page empty

**Solutions:**
```bash
# Check if table exists
php artisan migrate:status

# Check if ActivityLog is imported in controllers
# Should see: use App\Models\ActivityLog;

# Test manually
php artisan tinker
>>> App\Models\ActivityLog::count()
>>> exit
```

### Issue: Performance Not Improved

**Symptoms:**
- Still slow page loads
- High query counts

**Solutions:**
```bash
# Clear cache
php artisan cache:clear

# Check if indexes were created
sqlite3 database/database.sqlite
.schema theses
# Should see multiple INDEX statements

# Enable query logging temporarily
# In .env: DB_LOG_QUERIES=true
```

### Issue: Frontend Not Loading

**Symptoms:**
- Blank page
- Console errors

**Solutions:**
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Check API connection
# In browser console:
fetch('http://localhost:8000/api/theses')
  .then(r => r.json())
  .then(console.log)

# Check CORS settings
# backend/config/cors.php should allow localhost:3000
```

---

## 📊 Performance Monitoring

### Monitor These Metrics

```bash
# Backend response times
# Check Laravel logs
tail -f thesis-system/backend/storage/logs/laravel.log

# Database query count
# Enable query logging in .env
DB_LOG_QUERIES=true

# Cache hit rate
# Check cache statistics
php artisan cache:stats
```

### Expected Performance

| Metric | Target | Alert If |
|--------|--------|----------|
| Page Load | < 2s | > 3s |
| API Response | < 500ms | > 1s |
| Search | < 500ms | > 1s |
| Dashboard | < 2s | > 3s |
| DB Queries/Request | < 10 | > 15 |

---

## 🔒 Security Checklist

### Post-Deployment Security

- [ ] Change default admin password
- [ ] Review user permissions
- [ ] Check file upload restrictions
- [ ] Verify CORS settings
- [ ] Enable HTTPS (production)
- [ ] Set up regular backups
- [ ] Review activity logs regularly
- [ ] Set up log retention policy

### Recommended Security Settings

```bash
# In .env file:
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

---

## 📈 Monitoring & Maintenance

### Daily Tasks
- [ ] Check error logs
- [ ] Review activity logs for suspicious activity
- [ ] Monitor disk space

### Weekly Tasks
- [ ] Review performance metrics
- [ ] Check for failed jobs
- [ ] Backup database
- [ ] Update dependencies (if needed)

### Monthly Tasks
- [ ] Security audit
- [ ] Performance optimization review
- [ ] User feedback review
- [ ] Clean old activity logs (if needed)

---

## 📞 Support Contacts

### If Issues Arise

1. **Check Documentation**
   - SETUP_AND_TEST_GUIDE.md
   - IMPLEMENTATION_STATUS.md
   - QUICK_REFERENCE.md

2. **Check Logs**
   ```bash
   # Backend logs
   tail -f thesis-system/backend/storage/logs/laravel.log
   
   # Frontend console
   # Open browser DevTools → Console
   ```

3. **Database Issues**
   ```bash
   # Check database
   php artisan migrate:status
   
   # Check connections
   php artisan tinker
   >>> DB::connection()->getPdo()
   ```

---

## ✅ Deployment Sign-Off

### Pre-Production Checklist

- [ ] All migrations run successfully
- [ ] All tests passed
- [ ] Performance metrics acceptable
- [ ] Security review completed
- [ ] Backup created
- [ ] Documentation updated
- [ ] Team trained on new features
- [ ] Rollback plan prepared

### Production Deployment

- [ ] Scheduled maintenance window
- [ ] Users notified
- [ ] Backup verified
- [ ] Deployment executed
- [ ] Post-deployment tests passed
- [ ] Monitoring enabled
- [ ] Users notified of completion

---

## 🎉 Success Criteria

### Deployment is Successful When:

✅ All new tables created
✅ All migrations completed
✅ No errors in logs
✅ All features working
✅ Performance improved
✅ Activity logs recording
✅ Users can login
✅ Theses can be viewed/downloaded
✅ Search works correctly
✅ Recommendations appear
✅ PDF viewer functional

---

## 📝 Deployment Log Template

```
Deployment Date: _______________
Deployed By: _______________
Version: 2.0.0

Pre-Deployment:
[ ] Backup created
[ ] Requirements verified
[ ] Configuration reviewed

Deployment:
[ ] Backend updated
[ ] Frontend updated
[ ] Migrations run
[ ] Caches cleared

Post-Deployment:
[ ] Critical tests passed
[ ] Full tests passed
[ ] Performance verified
[ ] No errors in logs

Issues Encountered:
_________________________________
_________________________________

Resolution:
_________________________________
_________________________________

Sign-Off:
Developer: _______________
QA: _______________
Admin: _______________
```

---

## 🚨 Rollback Plan

### If Deployment Fails

```bash
# 1. Restore database
cp thesis-system/backend/database/database.backup.YYYYMMDD.sqlite thesis-system/backend/database/database.sqlite

# 2. Restore files
cp -r thesis-system/backend/storage/app/public/documents.backup.YYYYMMDD/* thesis-system/backend/storage/app/public/documents/

# 3. Restore .env
cp thesis-system/backend/.env.backup thesis-system/backend/.env

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. Restart services
php artisan serve
```

---

**Deployment Version**: 2.0.0
**Last Updated**: March 25, 2026
**Status**: Ready for Deployment
**Estimated Deployment Time**: 30 minutes
**Estimated Testing Time**: 30 minutes
**Total Time**: 1 hour
