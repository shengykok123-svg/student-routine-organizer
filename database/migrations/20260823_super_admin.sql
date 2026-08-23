-- Super Admin role and protected administrator-account creation.
-- Import once for an existing Student Routine Organizer database.
USE student_routine_organizer;

ALTER TABLE users
    MODIFY role ENUM('Student', 'Admin', 'Super Admin') NOT NULL DEFAULT 'Student';

-- Promote the previously created administrator without changing its password.
UPDATE users
SET role = 'Super Admin', account_status = 'Active'
WHERE username = 'sro_admin';

-- Create it only when it does not already exist. The password is SROAdmin!2026.
INSERT INTO users (username, email, full_name, password, role, terms_accepted_at)
SELECT 'sro_admin', 'sro.admin@studentroutine.local', 'SRO Super Admin', '$2y$10$XTPw411sciLT2g4qRRvAFurtO7Gy1Me3C/laeMzKWLLoTGiredB6i', 'Super Admin', CURRENT_TIMESTAMP
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'sro_admin');
