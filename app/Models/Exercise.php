<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for student exercise records. */
final class Exercise
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{records:list<array<string,mixed>>,total:int} */
    public function list(int $userId, array $filters): array
    {
        $where = ["user_id = :user"];
        $params = [":user" => $userId];
        if (($filters["search"] ?? "") !== "") {
            $where[] = "activity_type LIKE :search";
            $params[":search"] = "%" . $filters["search"] . "%";
        }
        if (($filters["activity"] ?? "") === "Others") {
            $standardActivities = [
                "Walking",
                "Jogging",
                "Cycling",
                "Gym",
                "Swimming",
                "Badminton",
            ];
            $placeholders = [];
            foreach ($standardActivities as $index => $activity) {
                $placeholder = ":standard_activity_" . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $activity;
            }
            $where[] = "activity_type NOT IN (" . implode(", ", $placeholders) . ")";
        } elseif (($filters["activity"] ?? "") !== "") {
            $where[] = "activity_type = :activity";
            $params[":activity"] = $filters["activity"];
        }
        $order =
            [
                "newest" => "exercise_date DESC, exercise_id DESC",
                "oldest" => "exercise_date ASC, exercise_id ASC",
                "duration" => "duration_minutes DESC, exercise_id DESC",
                "calories" => "calories_burned DESC, exercise_id DESC",
            ][$filters["sort"] ?? "newest"] ?? "exercise_date DESC";
        $sqlWhere = implode(" AND ", $where);
        $count = $this->pdo->prepare(
            "SELECT COUNT(*) FROM exercises WHERE " . $sqlWhere,
        );
        $count->execute($params);
        $statement = $this->pdo->prepare(
            "SELECT * FROM exercises WHERE " .
                $sqlWhere .
                " ORDER BY " .
                $order,
        );
        $statement->execute($params);
        return [
            "records" => $statement->fetchAll(),
            "total" => (int) $count->fetchColumn(),
        ];
    }

    // Load one record only when it belongs to the current user.
    public function findOwned(int $id, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM exercises WHERE exercise_id = ? AND user_id = ?",
        );
        $statement->execute([$id, $userId]);
        return $statement->fetch() ?: null;
    }

    // Exercise CRUD operations.
    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO exercises (user_id, activity_type, duration_minutes, calories_burned, exercise_date, notes) VALUES (?, ?, ?, ?, ?, ?)",
        );
        $statement->execute([
            $userId,
            $data["activity_type"],
            $data["duration_minutes"],
            $data["calories_burned"],
            $data["exercise_date"],
            $data["notes"],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE exercises SET activity_type = ?, duration_minutes = ?, calories_burned = ?, exercise_date = ?, notes = ? WHERE exercise_id = ? AND user_id = ?",
        );
        $statement->execute([
            $data["activity_type"],
            $data["duration_minutes"],
            $data["calories_burned"],
            $data["exercise_date"],
            $data["notes"],
            $id,
            $userId,
        ]);
        return $statement->rowCount() === 1;
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM exercises WHERE exercise_id = ? AND user_id = ?",
        );
        $statement->execute([$id, $userId]);
        return $statement->rowCount() === 1;
    }

    // Detect a matching workout before the controller requests confirmation.
    public function duplicate(
        int $userId,
        array $data,
        ?int $except = null,
    ): ?array {
        $sql =
            "SELECT * FROM exercises WHERE user_id = ? AND activity_type = ? AND duration_minutes = ? AND calories_burned = ? AND exercise_date = ?";
        $arguments = [
            $userId,
            $data["activity_type"],
            $data["duration_minutes"],
            $data["calories_burned"],
            $data["exercise_date"],
        ];
        if ($except !== null) {
            $sql .= " AND exercise_id <> ?";
            $arguments[] = $except;
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute($arguments);
        return $statement->fetch() ?: null;
    }

    // Summary values for the Exercise Tracker header.
    public function analytics(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) records, COALESCE(SUM(duration_minutes), 0) minutes, COALESCE(SUM(calories_burned), 0) calories FROM exercises WHERE user_id = ?",
        );
        $statement->execute([$userId]);
        return $statement->fetch() ?: [
                "records" => 0,
                "minutes" => 0,
                "calories" => 0,
            ];
    }

    // Reuse the filtered listing for CSV export.
    public function export(int $userId, array $filters): array
    {
        return $this->list($userId, $filters)["records"];
    }
}
