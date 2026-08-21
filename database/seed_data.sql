-- Optional demo data for Student Routine Organizer.
-- Import this AFTER database/student_routine_organizer.sql.
-- All records below belong only to demo_student; newly registered accounts start empty.
-- The password is stored as a bcrypt hash, never as plaintext.
-- Local demonstration login: username demo_student / password Demo@12345.

USE student_routine_organizer;

INSERT INTO users (username, email, password, role)
VALUES ('demo_student', 'demo_student@sro.local', '$2y$10$3Ew2UP669d8hwE94zTFA0OhFVXhyRuHpiwJqJOU6XWS2J/cg3GEKi', 'Student')
ON DUPLICATE KEY UPDATE user_id = LAST_INSERT_ID(user_id);

SET @demo_user_id = LAST_INSERT_ID();

-- 10 Exercise Tracker records
INSERT INTO exercises (user_id, activity_type, duration_minutes, calories_burned, exercise_date, notes)
SELECT @demo_user_id, seed.activity_type, seed.duration_minutes, seed.calories_burned, seed.exercise_date, seed.notes
FROM (
    SELECT 'Jogging' activity_type, 35 duration_minutes, 290.00 calories_burned, CURDATE() - INTERVAL 1 DAY exercise_date, 'Evening run around campus.' notes UNION ALL
    SELECT 'Badminton', 75, 510.00, CURDATE() - INTERVAL 3 DAY, 'Doubles practice with classmates.' UNION ALL
    SELECT 'Walking', 40, 170.00, CURDATE() - INTERVAL 5 DAY, 'Walked to and from the library.' UNION ALL
    SELECT 'Cycling', 50, 380.00, CURDATE() - INTERVAL 7 DAY, 'Weekend park cycling.' UNION ALL
    SELECT 'Gym', 60, 430.00, CURDATE() - INTERVAL 9 DAY, 'Upper body strength session.' UNION ALL
    SELECT 'Swimming', 45, 360.00, CURDATE() - INTERVAL 11 DAY, 'Relaxed laps at the pool.' UNION ALL
    SELECT 'Jogging', 30, 250.00, CURDATE() - INTERVAL 13 DAY, 'Morning jog before class.' UNION ALL
    SELECT 'Walking', 55, 220.00, CURDATE() - INTERVAL 15 DAY, 'Explored the campus trail.' UNION ALL
    SELECT 'Gym', 50, 390.00, CURDATE() - INTERVAL 17 DAY, 'Leg day workout.' UNION ALL
    SELECT 'Badminton', 90, 620.00, CURDATE() - INTERVAL 19 DAY, 'Club training session.'
) AS seed
LEFT JOIN exercises existing ON existing.user_id = @demo_user_id AND existing.activity_type = seed.activity_type AND existing.exercise_date = seed.exercise_date
WHERE existing.exercise_id IS NULL;

-- 10 Diary Journal records
INSERT INTO diary_entries (user_id, title, content, mood, mood_score, is_favorite, entry_date)
SELECT @demo_user_id, seed.title, seed.content, seed.mood, seed.mood_score, seed.is_favorite, seed.entry_date
FROM (
    SELECT 'A productive study session' title, 'Completed my revision plan and felt more confident about the assignment.' content, 'Happy' mood, 8 mood_score, 1 is_favorite, CURDATE() - INTERVAL 1 DAY entry_date UNION ALL
    SELECT 'Planning the week', 'I listed my priorities and left time for rest, exercise, and friends.', 'Calm', 7, 0, CURDATE() - INTERVAL 3 DAY UNION ALL
    SELECT 'Group discussion', 'Our group made good progress and divided the integration tasks clearly.', 'Excited', 8, 0, CURDATE() - INTERVAL 5 DAY UNION ALL
    SELECT 'Busy but manageable', 'Several deadlines are close, but breaking work into smaller tasks helped.', 'Stressed', 6, 0, CURDATE() - INTERVAL 7 DAY UNION ALL
    SELECT 'A quiet evening', 'I enjoyed reading and taking a break from my phone after dinner.', 'Calm', 7, 1, CURDATE() - INTERVAL 9 DAY UNION ALL
    SELECT 'Morning motivation', 'Starting early made the day feel much less rushed.', 'Grateful', 8, 0, CURDATE() - INTERVAL 11 DAY UNION ALL
    SELECT 'Presentation practice', 'I was nervous at first, but practice made my explanation clearer.', 'Anxious', 6, 0, CURDATE() - INTERVAL 13 DAY UNION ALL
    SELECT 'A small win', 'I finished an exercise feature that had been difficult yesterday.', 'Happy', 9, 1, CURDATE() - INTERVAL 15 DAY UNION ALL
    SELECT 'Weekend recharge', 'Met friends for lunch and gave myself time to rest.', 'Happy', 8, 0, CURDATE() - INTERVAL 17 DAY UNION ALL
    SELECT 'Reflecting on progress', 'The semester is demanding, but I can see steady improvement.', 'Neutral', 6, 0, CURDATE() - INTERVAL 19 DAY
) AS seed
LEFT JOIN diary_entries existing ON existing.user_id = @demo_user_id AND existing.title = seed.title
WHERE existing.entry_id IS NULL;

-- 10 Money Tracker records
INSERT INTO money_records (user_id, amount, category, description, transaction_type, transaction_date)
SELECT @demo_user_id, seed.amount, seed.category, seed.description, seed.transaction_type, seed.transaction_date
FROM (
    SELECT 250.00 amount, 'Allowance' category, 'Monthly allowance' description, 'Income' transaction_type, CURDATE() - INTERVAL 20 DAY transaction_date UNION ALL
    SELECT 12.50, 'Food', 'Lunch on campus', 'Expense', CURDATE() - INTERVAL 1 DAY UNION ALL
    SELECT 4.00, 'Transport', 'Campus bus', 'Expense', CURDATE() - INTERVAL 2 DAY UNION ALL
    SELECT 18.90, 'Food', 'Dinner with classmates', 'Expense', CURDATE() - INTERVAL 4 DAY UNION ALL
    SELECT 25.00, 'Study', 'Printing and stationery', 'Expense', CURDATE() - INTERVAL 6 DAY UNION ALL
    SELECT 8.50, 'Transport', 'E-hailing to campus', 'Expense', CURDATE() - INTERVAL 8 DAY UNION ALL
    SELECT 35.00, 'Entertainment', 'Movie ticket and snack', 'Expense', CURDATE() - INTERVAL 10 DAY UNION ALL
    SELECT 50.00, 'Other', 'Freelance design task', 'Income', CURDATE() - INTERVAL 12 DAY UNION ALL
    SELECT 16.00, 'Health', 'Sports drink and supplements', 'Expense', CURDATE() - INTERVAL 14 DAY UNION ALL
    SELECT 42.30, 'Food', 'Weekly groceries', 'Expense', CURDATE() - INTERVAL 16 DAY
) AS seed
LEFT JOIN money_records existing ON existing.user_id = @demo_user_id AND existing.category = seed.category AND existing.description = seed.description AND existing.transaction_date = seed.transaction_date
WHERE existing.record_id IS NULL;

-- 10 Habit Tracker records
INSERT INTO habits (user_id, habit_name, frequency, status, streak)
SELECT @demo_user_id, seed.habit_name, seed.frequency, seed.status, seed.streak
FROM (
    SELECT 'Morning reading' habit_name, 'Daily' frequency, 'Active' status, 4 streak UNION ALL
    SELECT 'Drink enough water', 'Daily', 'Active', 2 UNION ALL
    SELECT 'Review lecture notes', 'Daily', 'Active', 5 UNION ALL
    SELECT 'Sleep before midnight', 'Daily', 'Active', 3 UNION ALL
    SELECT 'Walk 8,000 steps', 'Daily', 'Active', 6 UNION ALL
    SELECT 'Plan tomorrow', 'Daily', 'Active', 4 UNION ALL
    SELECT 'Call family', 'Weekly', 'Active', 2 UNION ALL
    SELECT 'Clean study desk', 'Weekly', 'Active', 1 UNION ALL
    SELECT 'Save part of allowance', 'Monthly', 'Active', 1 UNION ALL
    SELECT 'Complete assignment milestone', 'Weekly', 'Completed', 3
) AS seed
LEFT JOIN habits existing ON existing.user_id = @demo_user_id AND existing.habit_name = seed.habit_name
WHERE existing.habit_id IS NULL;

-- One valid daily check-in for every seeded habit (10 Habit Log records).
INSERT INTO habit_logs (habit_id, user_id, check_in_date, sleep_quality, diet_adherence, stress_level, notes)
SELECT habit_id, @demo_user_id, CURDATE(), 'Good', 'Good', 'Low', CONCAT('Demo check-in for ', habit_name)
FROM habits
WHERE user_id = @demo_user_id
ON DUPLICATE KEY UPDATE sleep_quality = VALUES(sleep_quality), diet_adherence = VALUES(diet_adherence), stress_level = VALUES(stress_level), notes = VALUES(notes);
