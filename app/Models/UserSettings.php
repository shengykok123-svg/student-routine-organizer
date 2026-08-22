<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for per-user display preferences. */
final class UserSettings
{
    public function __construct(private readonly PDO $pdo)
    {
    }
    public function get(int $userId): array
    {
        $this->pdo
            ->prepare(
                "INSERT INTO user_settings (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id)",
            )
            ->execute([$userId]);
        $s = $this->pdo->prepare("SELECT * FROM user_settings WHERE user_id=?");
        $s->execute([$userId]);
        return $s->fetch();
    }
    public function update(
        int $userId,
        bool $inApp,
        bool $email,
        ?string $time,
    ): void {
        $this->pdo
            ->prepare(
                "INSERT INTO user_settings (user_id,in_app_notifications,email_notifications,reminder_time) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE in_app_notifications=VALUES(in_app_notifications),email_notifications=VALUES(email_notifications),reminder_time=VALUES(reminder_time)",
            )
            ->execute([$userId, $inApp ? 1 : 0, $email ? 1 : 0, $time]);
    }
}
