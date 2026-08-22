<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for user notifications. */
final class Notification
{
    public function __construct(private readonly PDO $pdo)
    {
    }
    public function create(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
    ): void {
        $this->pdo
            ->prepare(
                "INSERT INTO notifications (user_id,notification_type,title,body,link_url) VALUES (?,?,?,?,?)",
            )
            ->execute([$userId, $type, $title, $body, $link]);
    }
    public function list(int $userId): array
    {
        $s = $this->pdo->prepare(
            "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC,notification_id DESC",
        );
        $s->execute([$userId]);
        return $s->fetchAll();
    }
    public function unreadCount(int $userId): int
    {
        $s = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id=? AND read_at IS NULL",
        );
        $s->execute([$userId]);
        return (int) $s->fetchColumn();
    }
    public function markRead(int $id, int $userId): void
    {
        $this->pdo
            ->prepare(
                "UPDATE notifications SET read_at=COALESCE(read_at,CURRENT_TIMESTAMP) WHERE notification_id=? AND user_id=?",
            )
            ->execute([$id, $userId]);
    }
    public function markAllRead(int $userId): void
    {
        $this->pdo
            ->prepare(
                "UPDATE notifications SET read_at=CURRENT_TIMESTAMP WHERE user_id=? AND read_at IS NULL",
            )
            ->execute([$userId]);
    }
}
