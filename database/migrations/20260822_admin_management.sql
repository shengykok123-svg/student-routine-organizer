-- Admin management features: import once after the base schema.
USE student_routine_organizer;

SET @database_name := DATABASE();
SET @add_account_status := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@database_name AND TABLE_NAME='users' AND COLUMN_NAME='account_status')=0,'ALTER TABLE users ADD COLUMN account_status ENUM(''Active'', ''Suspended'') NOT NULL DEFAULT ''Active'' AFTER role','SELECT 1');
PREPARE admin_feature_statement FROM @add_account_status; EXECUTE admin_feature_statement; DEALLOCATE PREPARE admin_feature_statement;

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
