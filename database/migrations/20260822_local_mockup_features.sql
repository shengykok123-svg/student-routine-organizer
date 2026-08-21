-- Local mockup features: import once after the base schema.
USE student_routine_organizer;

SET @database_name := DATABASE();
SET @add_full_name := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@database_name AND TABLE_NAME='users' AND COLUMN_NAME='full_name')=0,'ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NULL AFTER email','SELECT 1');
PREPARE feature_statement FROM @add_full_name; EXECUTE feature_statement; DEALLOCATE PREPARE feature_statement;
SET @add_terms := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@database_name AND TABLE_NAME='users' AND COLUMN_NAME='terms_accepted_at')=0,'ALTER TABLE users ADD COLUMN terms_accepted_at TIMESTAMP NULL AFTER role','SELECT 1');
PREPARE feature_statement FROM @add_terms; EXECUTE feature_statement; DEALLOCATE PREPARE feature_statement;
SET @add_schedule_time := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@database_name AND TABLE_NAME='habits' AND COLUMN_NAME='schedule_time')=0,'ALTER TABLE habits ADD COLUMN schedule_time TIME NULL AFTER streak','SELECT 1');
PREPARE feature_statement FROM @add_schedule_time; EXECUTE feature_statement; DEALLOCATE PREPARE feature_statement;
SET @add_schedule_day := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@database_name AND TABLE_NAME='habits' AND COLUMN_NAME='schedule_day')=0,'ALTER TABLE habits ADD COLUMN schedule_day TINYINT UNSIGNED NULL AFTER schedule_time','SELECT 1');
PREPARE feature_statement FROM @add_schedule_day; EXECUTE feature_statement; DEALLOCATE PREPARE feature_statement;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT UNSIGNED PRIMARY KEY,
    in_app_notifications TINYINT(1) NOT NULL DEFAULT 1,
    email_notifications TINYINT(1) NOT NULL DEFAULT 0,
    reminder_time TIME NULL,
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
