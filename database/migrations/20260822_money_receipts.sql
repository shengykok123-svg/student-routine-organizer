-- Money receipt images: import once after the base schema.
USE student_routine_organizer;

SET @database_name := DATABASE();
SET @add_receipt_path := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'money_records' AND COLUMN_NAME = 'receipt_path') = 0,
    'ALTER TABLE money_records ADD COLUMN receipt_path VARCHAR(255) NULL AFTER transaction_date',
    'SELECT 1'
);
PREPARE money_receipt_statement FROM @add_receipt_path;
EXECUTE money_receipt_statement;
DEALLOCATE PREPARE money_receipt_statement;
