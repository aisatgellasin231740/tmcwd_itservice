# Database SQL Dump

This folder contains the latest exported database for the TMCWD IT Request Service System.

## File
- `tmcwd_itservice.sql` — full database dump including structure and sample data

## How to import on a new computer

### Option A — phpMyAdmin (easiest)
1. Open `http://localhost/phpmyadmin`
2. Create a new database named `tmcwd_itservice` (utf8mb4_unicode_ci)
3. Click the database → **Import** tab
4. Choose `tmcwd_itservice.sql` → click **Go**

### Option B — Command line
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS tmcwd_itservice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
C:\xampp\mysql\bin\mysql.exe -u root tmcwd_itservice < database/sql/tmcwd_itservice.sql
```

## After importing
No need to run `php artisan migrate --seed` — the database is already complete.
Just configure your `.env` with the correct DB credentials and run the app.

## Keeping it updated
Whenever significant changes are made to the database (new migrations, new data),
re-export and commit the updated SQL file:
```bash
C:\xampp\mysql\bin\mysqldump.exe -u root tmcwd_itservice > database/sql/tmcwd_itservice.sql
git add database/sql/tmcwd_itservice.sql
git commit -m "Update database dump"
git push
```
