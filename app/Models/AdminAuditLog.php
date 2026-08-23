<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Records security-relevant administrator actions. */
final class AdminAuditLog
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function record(
        int $adminId,
        string $action,
        ?int $targetUserId = null,
        ?string $details = null,
    ): void {
        $this->pdo
            ->prepare(
                "INSERT INTO admin_audit_logs (admin_user_id, target_user_id, action_name, details, ip_address) VALUES (?, ?, ?, ?, ?)",
            )
            ->execute([
                $adminId,
                $targetUserId,
                $action,
                $details,
                substr((string) ($_SERVER["REMOTE_ADDR"] ?? ""), 0, 45) ?: null,
            ]);
    }

    /**
     * Returns all administrator activity, or only one administrator's activity when scoped.
     */
    public function recent(int $limit = 40, ?int $adminUserId = null): array
    {
        return $this->search([], $adminUserId, $limit);
    }

    /** Returns audit records matching the supplied filters and permission scope. */
    public function search(array $filters, ?int $adminUserId = null, ?int $limit = 150): array
    {
        $sql =
            "SELECT logs.*, admin.username AS admin_username, target.username AS target_username
             FROM admin_audit_logs logs
             INNER JOIN users admin ON admin.user_id = logs.admin_user_id
             LEFT JOIN users target ON target.user_id = logs.target_user_id";
        [$where, $values] = $this->where($filters, $adminUserId);
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY logs.created_at DESC, logs.audit_id DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
        }

        $statement = $this->pdo->prepare($sql);
        $parameter = 1;
        foreach ($values as [$value, $type]) {
            $statement->bindValue($parameter++, $value, $type);
        }
        if ($limit !== null) {
            $statement->bindValue($parameter, max(1, min($limit, 200)), PDO::PARAM_INT);
        }
        $statement->execute();
        return $statement->fetchAll();
    }

    /** Lists the administrator accounts that have matching audit entries. */
    public function administrators(?int $adminUserId = null): array
    {
        $sql =
            "SELECT DISTINCT admin.user_id, admin.username
             FROM admin_audit_logs logs
             INNER JOIN users admin ON admin.user_id = logs.admin_user_id";
        if ($adminUserId !== null) {
            $sql .= " WHERE logs.admin_user_id = ?";
        }
        $sql .= " ORDER BY admin.username";

        $statement = $this->pdo->prepare($sql);
        if ($adminUserId !== null) {
            $statement->bindValue(1, $adminUserId, PDO::PARAM_INT);
        }
        $statement->execute();
        return $statement->fetchAll();
    }

    /** Lists available action filters within the supplied permission scope. */
    public function actions(?int $adminUserId = null): array
    {
        $sql = "SELECT DISTINCT action_name FROM admin_audit_logs";
        if ($adminUserId !== null) {
            $sql .= " WHERE admin_user_id = ?";
        }
        $sql .= " ORDER BY action_name";

        $statement = $this->pdo->prepare($sql);
        if ($adminUserId !== null) {
            $statement->bindValue(1, $adminUserId, PDO::PARAM_INT);
        }
        $statement->execute();
        return array_column($statement->fetchAll(), "action_name");
    }

    /** Builds parameterized filters shared by on-screen search and CSV export. */
    private function where(array $filters, ?int $adminUserId): array
    {
        $where = [];
        $values = [];
        if ($adminUserId !== null) {
            $where[] = "logs.admin_user_id = ?";
            $values[] = [$adminUserId, PDO::PARAM_INT];
        }
        if (($filters["admin_id"] ?? null) !== null) {
            $where[] = "logs.admin_user_id = ?";
            $values[] = [(int) $filters["admin_id"], PDO::PARAM_INT];
        }
        if (($filters["action"] ?? "") !== "") {
            $where[] = "logs.action_name = ?";
            $values[] = [$filters["action"], PDO::PARAM_STR];
        }
        if (($filters["search"] ?? "") !== "") {
            $where[] = "(admin.username LIKE ? OR target.username LIKE ? OR logs.action_name LIKE ? OR logs.details LIKE ? OR logs.ip_address LIKE ?)";
            $term = "%" . $filters["search"] . "%";
            foreach (range(1, 5) as $_) {
                $values[] = [$term, PDO::PARAM_STR];
            }
        }
        if (($filters["start_date"] ?? "") !== "") {
            $where[] = "logs.created_at >= ?";
            $values[] = [$filters["start_date"] . " 00:00:00", PDO::PARAM_STR];
        }
        if (($filters["end_date"] ?? "") !== "") {
            $where[] = "logs.created_at < DATE_ADD(?, INTERVAL 1 DAY)";
            $values[] = [$filters["end_date"] . " 00:00:00", PDO::PARAM_STR];
        }
        return [$where, $values];
    }
}
