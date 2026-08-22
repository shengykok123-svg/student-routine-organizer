-- Adds a persistent light, dark, or system theme choice for each account.

SET @database_name := DATABASE();
SET @add_theme_preference := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'user_settings' AND COLUMN_NAME = 'theme_preference') = 0,
    'ALTER TABLE user_settings ADD COLUMN theme_preference ENUM(''light'', ''dark'', ''system'') NOT NULL DEFAULT ''system'' AFTER reminder_time',
    'SELECT 1'
);
PREPARE theme_preference_statement FROM @add_theme_preference;
EXECUTE theme_preference_statement;
DEALLOCATE PREPARE theme_preference_statement;
