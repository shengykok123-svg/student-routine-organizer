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

    public function recent(int $limit = 40): array
    {
        $statement = $this->pdo->prepare(
            "SELECT logs.*, admin.username AS admin_username, target.username AS target_username
             FROM admin_audit_logs logs
             INNER JOIN users admin ON admin.user_id = logs.admin_user_id
             LEFT JOIN users target ON target.user_id = logs.target_user_id
             ORDER BY logs.created_at DESC, logs.audit_id DESC
             LIMIT ?",
        );
        $statement->bindValue(1, max(1, min($limit, 200)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
