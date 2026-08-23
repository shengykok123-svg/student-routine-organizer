<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for student habit definitions. */
final class Habit
{
    public function __construct(private readonly PDO $pdo)
    {
    }
    public function list(int $user, string $search, string $status): array
    {
        $sql = "SELECT * FROM habits WHERE user_id=?";
        $args = [$user];
        if ($search !== "") {
            $sql .= " AND habit_name LIKE ?";
            $args[] = "%" . $search . "%";
        }
        if ($status !== "") {
            $sql .= " AND status=?";
            $args[] = $status;
        }
        $sql .= " ORDER BY created_at DESC";
        $s = $this->pdo->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }
    public function findOwned(int $id, int $user): ?array
    {
        $s = $this->pdo->prepare(
            "SELECT * FROM habits WHERE habit_id=? AND user_id=?",
        );
        $s->execute([$id, $user]);
        return $s->fetch() ?: null;
    }
    public function create(int $user, array $d): int
    {
        $s = $this->pdo->prepare(
            "INSERT INTO habits (user_id,habit_name,frequency,status,schedule_time,schedule_day) VALUES (?,?,?,?,?,?)",
        );
        $s->execute([
            $user,
            $d["habit_name"],
            $d["frequency"],
            $d["status"],
            $d["schedule_time"],
            $d["schedule_day"],
        ]);
        return (int) $this->pdo->lastInsertId();
    }
    public function update(int $id, int $user, array $d): bool
    {
        $s = $this->pdo->prepare(
            "UPDATE habits SET habit_name=?,frequency=?,status=?,schedule_time=?,schedule_day=? WHERE habit_id=? AND user_id=?",
        );
        $s->execute([
            $d["habit_name"],
            $d["frequency"],
            $d["status"],
            $d["schedule_time"],
            $d["schedule_day"],
            $id,
            $user,
        ]);
        return (bool) $s->rowCount();
    }
    public function delete(int $id, int $user): bool
    {
        $s = $this->pdo->prepare(
            "DELETE FROM habits WHERE habit_id=? AND user_id=?",
        );
        $s->execute([$id, $user]);
        return (bool) $s->rowCount();
    }
    public function updateStreak(int $id, int $user, int $streak): void
    {
        $this->pdo
            ->prepare(
                "UPDATE habits SET streak=? WHERE habit_id=? AND user_id=?",
            )
            ->execute([$streak, $id, $user]);
    }
    public function getMaxStreak(int $user): int
    {
        $s = $this->pdo->prepare("SELECT MAX(streak) FROM habits WHERE user_id=?");
        $s->execute([$user]);
        return (int) $s->fetchColumn();
    }

    public function getTotalHabits(int $user): int
    {
        $s = $this->pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id=?");
        $s->execute([$user]);
        return (int) $s->fetchColumn();
    }
}
