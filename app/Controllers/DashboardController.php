<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use PDO;

/** Prepares the student overview and chart data. */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly Auth $auth,
        private readonly PDO $pdo,
    ) {
    }
    public function index(): void
    {
        $id = $this->auth->requireLogin();
        if ($this->auth->isAdmin()) {
            $this->redirect("admin");
        }

        $range = (string) ($_GET["range"] ?? "month");
        if (!in_array($range, ["month", "quarter", "all", "custom"], true)) {
            $range = "month";
        }
        $customStart = (string) ($_GET["start_date"] ?? "");
        $customEnd = (string) ($_GET["end_date"] ?? "");
        $error = null;
        if ($range === "custom") {
            $startDate = \DateTimeImmutable::createFromFormat(
                "!Y-m-d",
                $customStart,
            );
            $endDate = \DateTimeImmutable::createFromFormat(
                "!Y-m-d",
                $customEnd,
            );
            if (
                !$startDate ||
                !$endDate ||
                $startDate->format("Y-m-d") !== $customStart ||
                $endDate->format("Y-m-d") !== $customEnd ||
                $startDate > $endDate
            ) {
                [$start, $end, $label, $mode] = [
                    date("Y-m-01"),
                    date("Y-m-01", strtotime("+1 month")),
                    "Choose custom dates",
                    "month",
                ];
                if ($customStart !== "" || $customEnd !== "") {
                    $error = "Choose a valid start and end date.";
                }
            } else {
                $start = $startDate->format("Y-m-d");
                $end = $endDate->modify("+1 day")->format("Y-m-d");
                $label =
                    $startDate->format("d M Y") .
                    " – " .
                    $endDate->format("d M Y");
                $mode =
                    $startDate->diff($endDate)->days <= 31 ? "month" : "custom";
            }
        } elseif ($range === "quarter") {
            [$start, $end, $label, $mode] = [
                date("Y-m-01", strtotime("-2 months")),
                date("Y-m-01", strtotime("+1 month")),
                "Recent 3 Months",
                "custom",
            ];
        } elseif ($range === "all") {
            [$start, $end, $label, $mode] = [
                "1000-01-01",
                "9999-12-31",
                "All Time",
                "custom",
            ];
        } else {
            [$start, $end, $label, $mode] = [
                date("Y-m-01"),
                date("Y-m-01", strtotime("+1 month")),
                "Recent 1 Month",
                "month",
            ];
        }
        $params = [$id, $start, $end];
        $scalar = function (
            string $table,
            string $date,
            string $aggregate = "COUNT(*)",
        ) use ($params) {
            $s = $this->pdo->prepare(
                "SELECT " .
                    $aggregate .
                    " FROM " .
                    $table .
                    " WHERE user_id=? AND " .
                    $date .
                    ">=? AND " .
                    $date .
                    "<?",
            );
            $s->execute($params);
            return $s->fetchColumn() + 0;
        };
        $summary = [
            "exercises" => $scalar("exercises", "exercise_date"),
            "diary_entries" => $scalar("diary_entries", "entry_date"),
            "expenses" => $scalar(
                "money_records",
                "transaction_date",
                "COALESCE(SUM(CASE WHEN transaction_type='Expense' THEN amount ELSE 0 END),0)",
            ),
        ];
        $s = $this->pdo->prepare(
            "SELECT COUNT(*) workouts,COALESCE(SUM(duration_minutes),0) minutes,COALESCE(SUM(calories_burned),0) calories FROM exercises WHERE user_id=? AND exercise_date>=? AND exercise_date<?",
        );
        $s->execute([$id, $start, $end]);
        $exerciseSnapshot = $s->fetch() ?: [
            "workouts" => 0,
            "minutes" => 0,
            "calories" => 0,
        ];
        $s = $this->pdo->prepare(
            "SELECT activity_type,COUNT(*) total FROM exercises WHERE user_id=? AND exercise_date>=? AND exercise_date<? GROUP BY activity_type ORDER BY total DESC,activity_type LIMIT 1",
        );
        $s->execute([$id, $start, $end]);
        $exerciseSnapshot["top_activity"] = $s->fetchColumn() ?: null;
        $s = $this->pdo->prepare(
            "SELECT activity_type,duration_minutes,calories_burned,exercise_date FROM exercises WHERE user_id=? AND exercise_date>=? AND exercise_date<? ORDER BY exercise_date DESC,exercise_id DESC LIMIT 1",
        );
        $s->execute([$id, $start, $end]);
        $exerciseSnapshot["latest"] = $s->fetch() ?: null;
        $s = $this->pdo->prepare(
            'SELECT COUNT(*) total,COALESCE(SUM(status=\'Completed\'),0) completed FROM habits WHERE user_id=?',
        );
        $s->execute([$id]);
        $habits = $s->fetch() ?: ["total" => 0, "completed" => 0];
        $this->view("dashboard/index", [
            "pageTitle" => "Dashboard",
            "username" => $this->auth->username(),
            "role" => $this->auth->role(),
            "monthly" => $summary,
            "exerciseSnapshot" => $exerciseSnapshot,
            "habitTotals" => [
                "total" => (int) $habits["total"],
                "completed" => (int) $habits["completed"],
            ],
            "recentActivities" => $this->recent($id, $start, $end),
            "chart" => $this->chart($id, $mode, $start, $end),
            "categories" => $this->categories($id, $start, $end),
            "habitPreview" => $this->habitPreview($id),
            "range" => $range,
            "periodLabel" => $label,
            "customStart" => $customStart,
            "customEnd" => $customEnd,
            "filterError" => $error,
        ]);
    }
    private function recent(int $id, string $start, string $end): array
    {
        $data = [];
        $queries = [
            [
                "exercise",
                "exercises",
                "exercise_date",
                "exercise_id",
                "activity_type",
                'CONCAT(duration_minutes,\' min · \',FORMAT(calories_burned,0),\' kcal\')',
                "exercise/view?id=",
            ],
            [
                "diary",
                "diary_entries",
                "entry_date",
                "entry_id",
                'CONCAT(\'Diary: \',title)',
                'CONCAT(mood,\' · \',mood_score,\'/10\')',
                "diary/view?id=",
            ],
            [
                "money",
                "money_records",
                "transaction_date",
                "record_id",
                "category",
                'CONCAT(transaction_type,\' · RM \',FORMAT(amount,2))',
                "money/edit?id=",
            ],
            [
                "habit",
                "habits",
                "created_at",
                "habit_id",
                "habit_name",
                'CONCAT(frequency,\' · \',streak,\' day streak\')',
                "habit/edit?id=",
            ],
        ];
        foreach (
            $queries as [$module, $table, $date, $key, $title, $detail, $url]
        ) {
            $sql = "SELECT {$key} id,{$title} title,{$detail} detail,DATE({$date}) record_date FROM {$table} WHERE user_id=? AND {$date}>=? AND {$date}<? ORDER BY {$date} DESC,{$key} DESC LIMIT 5";
            $s = $this->pdo->prepare($sql);
            $s->execute([$id, $start, $end]);
            foreach ($s->fetchAll() as $row) {
                $data[] = [
                    "module" => $module,
                    "title" => $row["title"],
                    "detail" => $row["detail"],
                    "date" => $row["record_date"],
                    "url" => $url . (int) $row["id"],
                ];
            }
        }
        usort($data, static fn ($a, $b) => strcmp($b["date"], $a["date"]));
        return array_slice($data, 0, 5);
    }
    private function chart(
        int $id,
        string $mode,
        string $start,
        string $end,
    ): array {
        $weekly = $mode === "month";
        $sources = [
            ["exercises", "exercises", "exercise_date", "COUNT(*)"],
            [
                "calories",
                "exercises",
                "exercise_date",
                "COALESCE(SUM(calories_burned),0)",
            ],
            ["diary", "diary_entries", "entry_date", "COUNT(*)"],
            [
                "expenses",
                "money_records",
                "transaction_date",
                "COALESCE(SUM(CASE WHEN transaction_type='Expense' THEN amount ELSE 0 END),0)",
            ],
        ];
        $rows = [];
        $buckets = [];
        foreach ($sources as [$key, $table, $date, $aggregate]) {
            $group = $weekly
                ? "FLOOR((DAYOFMONTH({$date})-1)/7)+1"
                : "DATE_FORMAT({$date},'%Y-%m')";
            $s = $this->pdo->prepare(
                "SELECT {$group} bucket,{$aggregate} value FROM {$table} WHERE user_id=? AND {$date}>=? AND {$date}<? GROUP BY bucket ORDER BY bucket",
            );
            $s->execute([$id, $start, $end]);
            $rows[$key] = $s->fetchAll();
            foreach ($rows[$key] as $row) {
                $buckets[(string) $row["bucket"]] = true;
            }
        }
        if ($weekly) {
            $buckets = [
                "1" => true,
                "2" => true,
                "3" => true,
                "4" => true,
                "5" => true,
            ];
        }
        ksort($buckets);
        $keys = array_keys($buckets);
        $sets = [
            "exercises" => [],
            "calories" => [],
            "diary" => [],
            "expenses" => [],
        ];
        foreach ($sources as [$key]) {
            $map = [];
            foreach ($rows[$key] as $row) {
                $map[(string) $row["bucket"]] = (float) $row["value"];
            }
            foreach ($keys as $bucket) {
                $sets[$key][] = $map[$bucket] ?? 0;
            }
        }
        return [
            "labels" => $weekly
                ? array_map(static fn ($n) => "Week " . $n, $keys)
                : array_map(
                    static fn ($b) => date("M Y", strtotime($b . "-01")),
                    $keys,
                ),
        ] + $sets;
    }
    private function categories(int $id, string $start, string $end): array
    {
        $s = $this->pdo->prepare(
            "SELECT category,SUM(amount) amount FROM money_records WHERE user_id=? AND transaction_type='Expense' AND transaction_date>=? AND transaction_date<? GROUP BY category ORDER BY amount DESC",
        );
        $s->execute([$id, $start, $end]);
        return $s->fetchAll();
    }
    private function habitPreview(int $id): array
    {
        $s = $this->pdo->prepare(
            "SELECT habit_id,habit_name,frequency,status,streak,schedule_time,schedule_day FROM habits WHERE user_id=? ORDER BY schedule_time IS NULL,schedule_time,streak DESC LIMIT 4",
        );
        $s->execute([$id]);
        return $s->fetchAll();
    }
}
