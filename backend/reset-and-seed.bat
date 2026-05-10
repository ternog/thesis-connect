@echo off
echo Resetting and seeding ThesisConnect database...
echo.

php artisan migrate:fresh --seed

echo.
echo Database reset and seeded successfully!
echo.
echo Demo Accounts:
echo ================================================================
echo Admin:
echo   Email: admin@thesisconnect.com
echo   Password: admin123
echo.
echo Librarian:
echo   Email: librarian@thesisconnect.com
echo   Password: librarian123
echo.
echo Student:
echo   Email: student@thesisconnect.com
echo   Password: student123
echo ================================================================
echo.
echo You can now login with these credentials!
pause
