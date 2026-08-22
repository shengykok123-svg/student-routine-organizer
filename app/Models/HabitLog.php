<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for daily habit completion records. */
final class HabitLog
{
    public function __construct(private readonly PDO $pdo)
    {
    }
    public function save(int $habit, int $user, array $d): void
    {
        $s = $this->pdo->prepare(
            "INSERT INTO habit_logs (habit_id,user_id,check_in_date,sleep_quality,diet_adherence,stress_level,notes) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE sleep_quality=VALUES(sleep_quality),diet_adherence=VALUES(diet_adherence),stress_level=VALUES(stress_level),notes=VALUES(notes)",
        );
        $s->execute([
            $habit,
            $user,
            date("Y-m-d"),
            $d["sleep_quality"],
            $d["diet_adherence"],
            $d["stress_level"],
            $d["notes"],
        ]);
    }
    public function latestDates(int $habit, int $user): array
    {
        $s = $this->pdo->prepare(
            "SELECT check_in_date FROM habit_logs WHERE habit_id=? AND user_id=? ORDER BY check_in_date DESC LIMIT 2",
        );
        $s->execute([$habit, $user]);
        return array_column($s->fetchAll(), "check_in_date");
    }
    public function weeklyCounts(int $user): array
    {
        $s = $this->pdo->prepare(
            "SELECT check_in_date,COUNT(*) total FROM habit_logs WHERE user_id=? AND check_in_date>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY check_in_date",
        );
        $s->execute([$user]);
        $counts = [];
        foreach ($s->fetchAll() as $row) {
            $counts[$row["check_in_date"]] = (int) $row["total"];
        }
        return $counts;
    }
}
