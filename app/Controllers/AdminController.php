<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\AdminAuditLog;
use App\Models\Announcement;
use App\Models\User;
use App\Services\AdminMaintenanceService;
use PDO;

/** Manages administrator-only accounts, announcements, logs, and maintenance. */
final class AdminController extends Controller
{
    public function __construct(
        private readonly Auth $auth,
        private readonly User $users,
        private readonly PDO $pdo,
        private readonly AdminAuditLog $audit,
        private readonly Announcement $announcements,
        private readonly AdminMaintenanceService $maintenance,
    ) {
    }

    public function index(): void
    {
        $this->auth->requireAdmin();
        $this->view("admin/index", [
            "pageTitle" => "System Administration",
            "summary" => $this->summary(),
        ]);
    }

    public function dashboard(): void
    {
        $this->auth->requireAdmin();
        $range = (string) ($_GET["range"] ?? "30");
        if (!in_array($range, ["7", "30", "90", "all"], true)) {
            $range = "30";
        }

        $metrics = $this->dashboardMetrics($range);
        $charts = $this->dashboardCharts($range, $metrics);
        $this->view("admin/dashboard", [
            "pageTitle" => "Admin Dashboard",
            "charts" => $charts,
            "metrics" => $metrics,
            "periodLabel" => $metrics["period_label"],
            "range" => $range,
            "recentAudit" => $this->audit->recent(8),
            "recentAnnouncements" => $this->announcements->recent(4),
        ]);
    }

    public function users(): void
    {
        $this->auth->requireAdmin();
        $filters = $this->userFilters();
        $this->view("admin/users", [
            "pageTitle" => "User Management",
            "users" => $this->users->all(...$filters),
            "filters" => array_combine(["search", "role", "status"], $filters),
        ]);
    }

    public function createForm(): void
    {
        $this->auth->requireAdmin();
        $this->view("admin/user_form", ["pageTitle" => "Add User", "user" => null, "errors" => []]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $data = $this->valid($_POST, true);
        if ($data["errors"]) {
            $this->view("admin/user_form", ["pageTitle" => "Add User", "user" => $_POST, "errors" => $data["errors"]]);
            return;
        }
        $id = $this->users->create($data["username"], $data["email"], $data["password"], $data["full_name"], $data["role"]);
        $this->audit->record($adminId, "user_created", $id, $data["role"]);
        Flash::add("success", "User created.");
        $this->redirect("admin/users");
    }

    public function editForm(): void
    {
        $this->auth->requireAdmin();
        $user = $this->users->findById((int) ($_GET["id"] ?? 0));
        if (!$user) {
            Flash::add("error", "User not found.");
            $this->redirect("admin/users");
        }
        $this->view("admin/user_form", ["pageTitle" => "Edit User", "user" => $user, "errors" => []]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $id = (int) ($_POST["user_id"] ?? 0);
        $existing = $this->users->findById($id);
        if (!$existing) {
            Flash::add("error", "User not found.");
            $this->redirect("admin/users");
        }
        $data = $this->valid($_POST, false, $id);
        if ($id === $adminId && $data["role"] !== $existing["role"]) {
            $data["errors"][] = "You cannot change your own administrator role.";
        }
        if ($existing["role"] === "Admin" && $data["role"] !== "Admin") {
            $data["errors"][] = "Administrator accounts cannot be demoted because their audit history must remain traceable.";
        }
        if ($data["errors"]) {
            $_POST["user_id"] = $id;
            $this->view("admin/user_form", ["pageTitle" => "Edit User", "user" => $_POST, "errors" => $data["errors"]]);
            return;
        }
        $this->users->updateProfile($id, $data["full_name"], $data["username"], $data["email"]);
        $this->users->updateRole($id, $data["role"]);
        if ($data["password"] !== "") {
            $this->users->updatePassword($id, $data["password"]);
        }
        $this->audit->record($adminId, "user_updated", $id, $data["role"]);
        Flash::add("success", "User updated.");
        $this->redirect("admin/users");
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $id = (int) ($_POST["user_id"] ?? 0);
        $user = $this->users->findById($id);
        if (!$user) {
            Flash::add("error", "User not found.");
        } elseif ($id === $adminId) {
            Flash::add("error", "You cannot delete your own account.");
        } elseif ($user["role"] === "Admin") {
            Flash::add("error", "Administrator accounts cannot be deleted; suspend access instead.");
        } elseif ($this->users->delete($id)) {
            $this->audit->record($adminId, "user_deleted", $id, $user["username"]);
            Flash::add("success", "User deleted.");
        }
        $this->redirect("admin/users");
    }

    public function suspend(): void
    {
        $this->changeAccountStatus("Suspended");
    }

    public function resume(): void
    {
        $this->changeAccountStatus("Active");
    }

    public function announcements(): void
    {
        $this->auth->requireAdmin();
        $this->view("admin/announcements", ["pageTitle" => "Announcement Centre", "announcements" => $this->announcements->recent(), "errors" => []]);
    }

    public function storeAnnouncement(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $title = trim((string) ($_POST["title"] ?? ""));
        $body = trim((string) ($_POST["body"] ?? ""));
        $audience = (string) ($_POST["audience"] ?? "all");
        $errors = [];
        if ($title === "" || mb_strlen($title) > 150) {
            $errors[] = "Title is required and must be 150 characters or fewer.";
        }
        if ($body === "" || mb_strlen($body) > 500) {
            $errors[] = "Message is required and must be 500 characters or fewer.";
        }
        if (!in_array($audience, ["all", "students", "admins"], true)) {
            $errors[] = "Choose a valid audience.";
        }
        if ($errors) {
            $this->view("admin/announcements", ["pageTitle" => "Announcement Centre", "announcements" => $this->announcements->recent(), "errors" => $errors]);
            return;
        }
        $id = $this->announcements->create($adminId, $title, $body, $audience);
        $count = $this->announcements->deliver($id, $title, $body, $audience);
        $this->audit->record($adminId, "announcement_published", null, "{$title} ({$count} recipients)");
        Flash::add("success", "Announcement sent to {$count} active account(s).");
        $this->redirect("admin/announcements");
    }

    public function audit(): void
    {
        $this->auth->requireAdmin();
        $this->view("admin/audit", ["pageTitle" => "Admin Audit Log", "logs" => $this->audit->recent(150)]);
    }

    public function maintenance(): void
    {
        $this->auth->requireAdmin();
        $this->view("admin/maintenance", ["pageTitle" => "Data Maintenance", "summary" => $this->summary(), "storage" => $this->maintenance->storageSummary()]);
    }

    public function exportSummary(): void
    {
        $adminId = $this->adminId();
        $this->audit->record($adminId, "system_summary_exported");
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=sro-system-summary.csv");
        $output = fopen("php://output", "wb");
        fputcsv($output, ["Metric", "Value"]);
        foreach ($this->summary() as $label => $value) {
            fputcsv($output, [ucwords(str_replace("_", " ", $label)), $value]);
        }
        fclose($output);
        exit();
    }

    public function cleanUploads(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $removed = $this->maintenance->cleanOrphans();
        $this->audit->record($adminId, "orphan_uploads_cleaned", null, "{$removed} file(s) removed");
        Flash::add("success", "Removed {$removed} orphan upload(s) older than 24 hours.");
        $this->redirect("admin/maintenance");
    }

    private function adminId(): int
    {
        $this->auth->requireAdmin();
        return (int) $this->auth->id();
    }

    private function changeAccountStatus(string $status): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $adminId = $this->adminId();
        $id = (int) ($_POST["user_id"] ?? 0);
        $user = $this->users->findById($id);
        if (!$user) {
            Flash::add("error", "User not found.");
        } elseif ($id === $adminId) {
            Flash::add("error", "You cannot change your own account status.");
        } elseif ($status === "Suspended" && $user["role"] === "Admin" && $user["account_status"] === "Active" && $this->users->countAdmins() <= 1) {
            Flash::add("error", "The last active administrator cannot be suspended.");
        } else {
            $this->users->setAccountStatus($id, $status);
            $this->audit->record($adminId, "account_" . strtolower($status), $id, $user["username"]);
            Flash::add("success", "Account {$status}.");
        }
        $this->redirect("admin/users");
    }

    private function summary(): array
    {
        $summary = [];
        foreach (["users", "exercises", "diary_entries", "money_records", "habits"] as $table) {
            $summary[$table] = (int) $this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        }
        $summary["students"] = (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Student'")->fetchColumn();
        $summary["active_users"] = (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE account_status = 'Active'")->fetchColumn();
        $summary["suspended_users"] = (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE account_status = 'Suspended'")->fetchColumn();
        return $summary;
    }

    /** Returns aggregate, non-identifying metrics for the selected dashboard period. */
    private function dashboardMetrics(string $range): array
    {
        $period = $this->dashboardPeriod($range);
        $start = $period["start"];

        $count = function (string $table, string $dateColumn) use ($start): int {
            $sql = "SELECT COUNT(*) FROM {$table}";
            if ($start !== null) {
                $sql .= " WHERE {$dateColumn} >= ?";
            }
            $statement = $this->pdo->prepare($sql);
            $statement->execute($start === null ? [] : [$start]);
            return (int) $statement->fetchColumn();
        };

        $expenseSql = "SELECT COALESCE(SUM(amount), 0) FROM money_records WHERE transaction_type = 'Expense'";
        if ($start !== null) {
            $expenseSql .= " AND transaction_date >= ?";
        }
        $expenses = $this->pdo->prepare($expenseSql);
        $expenses->execute($start === null ? [] : [$start]);

        return [
            "period_label" => $period["label"],
            "new_users" => $count("users", "created_at"),
            "exercises" => $count("exercises", "exercise_date"),
            "diary_entries" => $count("diary_entries", "entry_date"),
            "money_records" => $count("money_records", "transaction_date"),
            "habit_checkins" => $count("habit_logs", "check_in_date"),
            "expenses" => (float) $expenses->fetchColumn(),
            "announcements" => $count("announcements", "created_at"),
            "audit_actions" => $count("admin_audit_logs", "created_at"),
        ];
    }

    /** Builds the chart datasets used by the aggregate administrator dashboard. */
    private function dashboardCharts(string $range, array $metrics): array
    {
        $period = $this->dashboardPeriod($range);

        return [
            "module_usage" => [
                "labels" => ["Exercises", "Diary", "Money", "Habit check-ins"],
                "values" => [
                    $metrics["exercises"],
                    $metrics["diary_entries"],
                    $metrics["money_records"],
                    $metrics["habit_checkins"],
                ],
            ],
            "user_status" => $this->dashboardUserStatus($period["start"]),
            "activity_trend" => $this->dashboardActivityTrend($period["start"], $range),
        ];
    }

    /** Groups new accounts by their current role and account status. */
    private function dashboardUserStatus(?string $start): array
    {
        $labels = ["Active Students", "Suspended Students", "Active Admins", "Suspended Admins"];
        $values = array_fill_keys($labels, 0);
        $sql = "SELECT role, account_status, COUNT(*) AS total FROM users";
        if ($start !== null) {
            $sql .= " WHERE created_at >= ?";
        }
        $sql .= " GROUP BY role, account_status";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($start === null ? [] : [$start]);
        foreach ($statement->fetchAll() as $row) {
            $label = ($row["account_status"] === "Suspended" ? "Suspended" : "Active") . " " . ($row["role"] === "Admin" ? "Admins" : "Students");
            if (isset($values[$label])) {
                $values[$label] = (int) $row["total"];
            }
        }

        return ["labels" => $labels, "values" => array_values($values)];
    }

    /** Groups module records into daily, weekly, or monthly activity chart points. */
    private function dashboardActivityTrend(?string $start, string $range): array
    {
        $modules = [
            "Exercises" => ["exercises", "exercise_date"],
            "Diary" => ["diary_entries", "entry_date"],
            "Money" => ["money_records", "transaction_date"],
            "Habit check-ins" => ["habit_logs", "check_in_date"],
        ];
        $bucketTemplate = match ($range) {
            "7" => "%s",
            "all" => "DATE_FORMAT(%s, '%%Y-%%m-01')",
            default => "DATE_SUB(%s, INTERVAL WEEKDAY(%s) DAY)",
        };
        $buckets = [];
        $records = array_fill_keys(array_keys($modules), []);

        foreach ($modules as $label => [$table, $dateColumn]) {
            $bucket = $range === "30" || $range === "90"
                ? sprintf($bucketTemplate, $dateColumn, $dateColumn)
                : sprintf($bucketTemplate, $dateColumn);
            $sql = "SELECT {$bucket} AS period_date, COUNT(*) AS total FROM {$table}";
            if ($start !== null) {
                $sql .= " WHERE {$dateColumn} >= ?";
            }
            $sql .= " GROUP BY period_date ORDER BY period_date";

            $statement = $this->pdo->prepare($sql);
            $statement->execute($start === null ? [] : [$start]);
            foreach ($statement->fetchAll() as $row) {
                $periodDate = (string) $row["period_date"];
                $buckets[$periodDate] = $this->trendLabel($periodDate, $range);
                $records[$label][$periodDate] = (int) $row["total"];
            }
        }

        ksort($buckets);
        $datasets = [];
        foreach ($records as $label => $totals) {
            $datasets[$label] = array_map(
                static fn (string $date): int => $totals[$date] ?? 0,
                array_keys($buckets),
            );
        }

        return ["labels" => array_values($buckets), "datasets" => $datasets];
    }

    /** Selects a bounded time period for dashboard queries. */
    private function dashboardPeriod(string $range): array
    {
        return match ($range) {
            "7" => ["start" => date("Y-m-d", strtotime("-6 days")), "label" => "Recent 7 Days"],
            "90" => ["start" => date("Y-m-d", strtotime("-89 days")), "label" => "Recent 90 Days"],
            "all" => ["start" => null, "label" => "All Time"],
            default => ["start" => date("Y-m-d", strtotime("-29 days")), "label" => "Recent 30 Days"],
        };
    }

    /** Formats database grouping dates for readable chart labels. */
    private function trendLabel(string $date, string $range): string
    {
        return match ($range) {
            "7" => date("d M", strtotime($date)),
            "all" => date("M Y", strtotime($date)),
            default => "Week of " . date("d M", strtotime($date)),
        };
    }

    /** @return array{0:string,1:string,2:string} */
    private function userFilters(): array
    {
        return [trim((string) ($_GET["search"] ?? "")), (string) ($_GET["role"] ?? ""), (string) ($_GET["status"] ?? "")];
    }

    private function valid(array $input, bool $passwordRequired, ?int $except = null): array
    {
        $data = [
            "full_name" => trim((string) ($input["full_name"] ?? "")),
            "username" => trim((string) ($input["username"] ?? "")),
            "email" => trim((string) ($input["email"] ?? "")),
            "role" => (string) ($input["role"] ?? "Student"),
            "password" => (string) ($input["password"] ?? ""),
        ];
        $errors = [];
        if ($data["full_name"] === "" || mb_strlen($data["full_name"]) > 100) {
            $errors[] = "Full name is required and must be 100 characters or fewer.";
        }
        if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $data["username"])) {
            $errors[] = "Username must be 4-30 letters, numbers, or underscores.";
        }
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Enter a valid email address.";
        }
        if (!in_array($data["role"], ["Student", "Admin"], true)) {
            $errors[] = "Choose a valid role.";
        }
        if (($passwordRequired && strlen($data["password"]) < 6) || (!$passwordRequired && $data["password"] !== "" && strlen($data["password"]) < 6)) {
            $errors[] = "Password must be at least 6 characters.";
        }
        if (!$errors && $this->users->usernameOrEmailExists($data["username"], $data["email"], $except)) {
            $errors[] = "That username or email is already registered.";
        }
        return $data + ["errors" => $errors];
    }
}
