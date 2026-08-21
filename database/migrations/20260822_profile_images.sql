-- Student profile images: import once after the base schema.
USE student_routine_organizer;

SET @database_name := DATABASE();
SET @add_profile_image_path := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_image_path') = 0,
    'ALTER TABLE users ADD COLUMN profile_image_path VARCHAR(255) NULL AFTER full_name',
    'SELECT 1'
);
PREPARE profile_image_statement FROM @add_profile_image_path;
EXECUTE profile_image_statement;
DEALLOCATE PREPARE profile_image_statement;
