<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Data access for user accounts and profiles. */
final class User
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->find("user_id = ?", [$id]);
    }
    public function findByUsername(string $username): ?array
    {
        return $this->find("username = ?", [$username]);
    }

    private function find(string $where, array $values): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT user_id, username, email, full_name, profile_image_path, password, role, terms_accepted_at, created_at FROM users WHERE " .
                $where,
        );
        $statement->execute($values);
        return $statement->fetch() ?: null;
    }

    public function usernameOrEmailExists(
        string $username,
        string $email,
        ?int $exceptUserId = null,
    ): bool {
        $sql = "SELECT user_id FROM users WHERE (username = ? OR email = ?)";
        $values = [$username, $email];
        if ($exceptUserId !== null) {
            $sql .= " AND user_id <> ?";
            $values[] = $exceptUserId;
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        return (bool) $statement->fetch();
    }

    public function create(
        string $username,
        string $email,
        string $password,
        string $fullName = "",
        string $role = "Student",
    ): int {
        $statement = $this->pdo->prepare(
            "INSERT INTO users (username, email, full_name, password, role, terms_accepted_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
        );
        $statement->execute([
            $username,
            $email,
            $fullName !== "" ? $fullName : null,
            password_hash($password, PASSWORD_DEFAULT),
            $role,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(
        int $id,
        string $fullName,
        string $username,
        string $email,
    ): bool {
        $statement = $this->pdo->prepare(
            "UPDATE users SET full_name = ?, username = ?, email = ? WHERE user_id = ?",
        );
        $statement->execute([
            $fullName !== "" ? $fullName : null,
            $username,
            $email,
            $id,
        ]);
        return true;
    }

    public function updateProfileImage(int $id, ?string $path): void
    {
        $this->pdo
            ->prepare(
                "UPDATE users SET profile_image_path = ? WHERE user_id = ?",
            )
            ->execute([$path, $id]);
    }

    public function updateRole(int $id, string $role): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE users SET role = ? WHERE user_id = ?",
        );
        $statement->execute([$role, $id]);
        return true;
    }
    public function updatePassword(int $id, string $password): void
    {
        $this->pdo
            ->prepare("UPDATE users SET password = ? WHERE user_id = ?")
            ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $statement->execute([$id]);
        return $statement->rowCount() === 1;
    }
    public function all(): array
    {
        return $this->pdo
            ->query(
                "SELECT user_id, username, email, full_name, role, created_at FROM users ORDER BY user_id DESC",
            )
            ->fetchAll();
    }
}
