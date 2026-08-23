# Student Routine Organizer

One PHP/MySQL application that combines Exercise, Diary, Money, and Habit tracking under one Student/Admin login and dashboard. It uses a simple MVC-style structure: Views are the presentation tier, Controllers/Core/Services are the application tier, and PDO Models/MySQL are the data tier.

## Requirements

- XAMPP with Apache, MySQL, PHP 8.1+, and `pdo_mysql`
- phpMyAdmin

## XAMPP setup

1. Copy the `student-routine-organizer` folder into `C:\xampp\htdocs`.
2. Start Apache and MySQL in XAMPP.
3. In phpMyAdmin, import `database/student_routine_organizer.sql`. It creates the required `student_routine_organizer` database and all tables.
   For an existing database created before the Super Admin change, import `database/migrations/20260823_super_admin.sql` instead; it promotes the existing `sro_admin` account without changing its password.
4. Optional: import `database/seed_data.sql` after the schema to create the `demo_student` account plus 10 Exercise, 10 Diary, 10 Money, 10 Habit, and 10 Habit Log records. Newly registered users start with no seed records; re-importing does not duplicate the demo records.
5. Open `http://localhost/student-routine-organizer/`. The root entry redirects to `public/`; no VirtualHost is needed.
6. Register a Student account, then sign in. Registration always creates a Student account.

The default database connection is XAMPP's `127.0.0.1`, database `student_routine_organizer`, user `root`, and an empty password. If your local MySQL differs, set `SRO_DB_HOST`, `SRO_DB_NAME`, `SRO_DB_USER`, and `SRO_DB_PASSWORD` in the web-server environment. Set `SRO_BASE_URL` only if the folder is hosted at a non-standard URL.

Before deployment, set a long random `SRO_REMEMBER_SECRET`. The built-in fallback only exists to make the class project runnable locally.

## Administrator accounts

The built-in Super Admin account creates Administrator accounts through **System Administration → User Management**. Public registration can only create Student accounts.

- Username: `sro_admin`
- Password: `SROAdmin!2026`

Only the Super Admin can assign the Administrator role. Administrators retain access to the existing administration dashboard and can manage Student accounts, but cannot create or manage administrator accounts.

## Modules

- Exercise: CRUD, validation, duplicate confirmation, filtering/sorting, totals, CSV export, evidence upload, and user ownership.
- Diary: CRUD, mood score, favourites, search/filter/sort, calendar, insights, and photo upload.
- Account: Student profile details, secure profile-picture upload (JPG, PNG, or WebP up to 5 MB), password settings, and notification preferences.
- Money: income/expense CRUD; receipt image upload (JPG, PNG, or WebP up to 5 MB); live search across transaction descriptions and categories; automatic type/category/date-range filtering (Recent 1 Month, Recent 3 Months, All Time, or Custom Date Range); filtered income/expense totals and balance; and filtered CSV export.
- Habit: CRUD, frequency/status filtering, daily check-in logs, wellbeing notes, weekly consistency, and streak tracking.

## Manual test checklist

1. Register, log in with correct and incorrect passwords, log out, and test Remember Me.
2. Alter the Remember Me cookie and confirm it is cleared rather than restoring a session.
3. Create one record in every module as Student A.
4. Sign in as Student B, replace record IDs in URLs/forms, and confirm Student A's records cannot be viewed, edited, or deleted.
5. Upload a valid and invalid exercise/diary file; confirm only allowed files up to 5 MB are accepted.
6. Confirm delete actions require POST and a valid CSRF token.
7. Sign in as `sro_admin`, create an Administrator from User Management, and confirm the new Administrator can access `/admin` but cannot create or manage administrator accounts. Confirm a Student is redirected away from that route.
8. Confirm dashboard cards, return navigation, styling, and uploaded files work from the XAMPP URL.
9. In Money Tracker, change the search text, period, type, or category and confirm the table and totals update automatically without a Filter button. Export CSV and confirm it contains those same filtered records.
10. Add a Money transaction with a JPG, PNG, or WebP receipt; reject a non-image and an image over 5 MB; then confirm edit preserves, replaces, and removes the receipt as expected. Deleting the transaction must delete its receipt too.
11. Upload a profile picture, confirm it replaces the default top-right avatar, and confirm removal restores the default avatar. Reject an invalid or oversized image.

## Known limitations

- This is a class-project plain PHP application, not a production multi-device authentication system. Remember Me is a signed, expiring cookie and does not use a server-side token revocation table.
- Upload directories are browser-accessible as required for the XAMPP demo. PHP execution is disabled in them; files use validated MIME types and unpredictable generated names.
