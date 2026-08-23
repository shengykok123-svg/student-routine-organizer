<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Stores administrator announcements and delivers them in-app. */
final class Announcement
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(
        int $adminId,
        string $title,
        string $body,
        string $audience,
    ): int {
        $statement = $this->pdo->prepare(
            "INSERT INTO announcements (created_by, title, body, audience) VALUES (?, ?, ?, ?)",
        );
        $statement->execute([$adminId, $title, $body, $audience]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deliver(int $announcementId, string $title, string $body, string $audience): int
    {
        $roles = $audience === "admins"
            ? ["Admin", "Super Admin"]
            : ($audience === "students" ? ["Student"] : ["Admin", "Super Admin", "Student"]);
        $placeholders = implode(",", array_fill(0, count($roles), "?"));
        $recipients = $this->pdo->prepare(
            "SELECT user_id FROM users WHERE account_status = 'Active' AND role IN ({$placeholders})",
        );
        $recipients->execute($roles);
        $insert = $this->pdo->prepare(
            "INSERT INTO notifications (user_id, notification_type, title, body, link_url) VALUES (?, 'info', ?, ?, NULL)",
        );
        $count = 0;
        foreach ($recipients->fetchAll() as $recipient) {
            $insert->execute([(int) $recipient["user_id"], $title, $body]);
            $count++;
        }
        $this->pdo
            ->prepare("UPDATE announcements SET recipient_count = ? WHERE announcement_id = ?")
            ->execute([$count, $announcementId]);
        return $count;
    }

    public function recent(int $limit = 12): array
    {
        $statement = $this->pdo->prepare(
            "SELECT announcements.*, users.username AS author_username
             FROM announcements
             INNER JOIN users ON users.user_id = announcements.created_by
             ORDER BY announcements.created_at DESC, announcements.announcement_id DESC
             LIMIT ?",
        );
        $statement->bindValue(1, max(1, min($limit, 100)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
