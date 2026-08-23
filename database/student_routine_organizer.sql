-- Student Routine Organizer - final integrated database
-- Import this file with phpMyAdmin before opening the application.

CREATE DATABASE IF NOT EXISTS student_routine_organizer
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_routine_organizer;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(190) NOT NULL,
    full_name VARCHAR(100) NULL,
    profile_image_path VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Student', 'Admin', 'Super Admin') NOT NULL DEFAULT 'Student',
    account_status ENUM('Active', 'Suspended') NOT NULL DEFAULT 'Active',
    terms_accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The only account permitted to create administrator accounts.
-- Password: SROAdmin!2026 (stored as a bcrypt hash).
INSERT INTO users (username, email, full_name, password, role, terms_accepted_at)
VALUES ('sro_admin', 'sro.admin@studentroutine.local', 'SRO Super Admin', '$2y$10$XTPw411sciLT2g4qRRvAFurtO7Gy1Me3C/laeMzKWLLoTGiredB6i', 'Super Admin', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE role = 'Super Admin', account_status = 'Active';

CREATE TABLE IF NOT EXISTS exercises (
    exercise_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    calories_burned DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    exercise_date DATE NOT NULL,
    notes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exercises_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_exercises_user_date (user_id, exercise_date, exercise_id),
    KEY idx_exercises_user_activity (user_id, activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exercise_attachments (
    attachment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT UNSIGNED NOT NULL,
    stored_name CHAR(44) NOT NULL,
    original_name VARCHAR(180) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exercise_attachments_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id) ON DELETE CASCADE,
    UNIQUE KEY uq_exercise_attachment_exercise (exercise_id),
    UNIQUE KEY uq_exercise_attachment_stored_name (stored_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diary_entries (
    entry_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    mood ENUM('Happy','Sad','Excited','Anxious','Calm','Angry','Grateful','Tired','Stressed','Neutral') NOT NULL DEFAULT 'Neutral',
    mood_score TINYINT UNSIGNED NOT NULL DEFAULT 5,
    image_path VARCHAR(255) NULL,
    is_favorite TINYINT(1) NOT NULL DEFAULT 0,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_diary_entries_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_diary_user_date (user_id, entry_date, entry_id),
    KEY idx_diary_user_favorite (user_id, is_favorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS money_records (
    record_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    transaction_type ENUM('Income','Expense') NOT NULL,
    transaction_date DATE NOT NULL,
    receipt_path VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_money_records_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_money_user_date (user_id, transaction_date, record_id),
    KEY idx_money_user_type (user_id, transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS money_budgets (
    budget_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category VARCHAR(50) NOT NULL,
    monthly_limit DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_money_budgets_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_money_budgets_user_category (user_id, category),
    KEY idx_money_budgets_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS habits (
    habit_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    habit_name VARCHAR(255) NOT NULL,
    frequency ENUM('Daily','Weekly','Monthly') NOT NULL DEFAULT 'Daily',
    status ENUM('Active','Completed') NOT NULL DEFAULT 'Active',
    streak INT UNSIGNED NOT NULL DEFAULT 0,
    schedule_time TIME NULL,
    schedule_day TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_habits_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_habits_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT UNSIGNED PRIMARY KEY,
    in_app_notifications TINYINT(1) NOT NULL DEFAULT 1,
    email_notifications TINYINT(1) NOT NULL DEFAULT 0,
    reminder_time TIME NULL,
    theme_preference ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'system',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    notification_type ENUM('info','success','warning') NOT NULL DEFAULT 'info',
    title VARCHAR(150) NOT NULL,
    body VARCHAR(500) NOT NULL,
    link_url VARCHAR(255) NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_notifications_user_read (user_id, read_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    body VARCHAR(500) NOT NULL,
    audience ENUM('all','students','admins') NOT NULL DEFAULT 'all',
    recipient_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_announcements_author FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    KEY idx_announcements_created (created_at, announcement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_audit_logs (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT UNSIGNED NOT NULL,
    target_user_id INT UNSIGNED NULL,
    action_name VARCHAR(80) NOT NULL,
    details VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_admin FOREIGN KEY (admin_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_audit_target FOREIGN KEY (target_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    KEY idx_audit_created (created_at, audit_id),
    KEY idx_audit_target (target_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS habit_logs (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    habit_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    check_in_date DATE NOT NULL,
    sleep_quality ENUM('Poor','Fair','Good','Excellent') NULL,
    diet_adherence ENUM('Poor','Fair','Good','Excellent') NULL,
    stress_level ENUM('Low','Moderate','High','Severe') NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_habit_logs_habit FOREIGN KEY (habit_id) REFERENCES habits(habit_id) ON DELETE CASCADE,
    CONSTRAINT fk_habit_logs_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_habit_daily_log (habit_id, check_in_date),
    KEY idx_habit_logs_user_date (user_id, check_in_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
