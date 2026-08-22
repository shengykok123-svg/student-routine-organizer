<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App;
use App\Models\User;

/** Provides authentication and current-user helpers. */
final class Auth
{
    public function __construct(private readonly User $users)
    {
    }

    public function check(): bool
    {
        return isset($_SESSION["user_id"]) &&
            filter_var($_SESSION["user_id"], FILTER_VALIDATE_INT) !== false;
    }

    public function id(): ?int
    {
        return $this->check() ? (int) $_SESSION["user_id"] : null;
    }

    public function username(): string
    {
        return (string) ($_SESSION["username"] ?? "");
    }

    public function role(): string
    {
        return (string) ($_SESSION["role"] ?? "");
    }

    public function profileImagePath(): ?string
    {
        $path = $_SESSION["profile_image_path"] ?? null;
        return is_string($path) && $path !== "" ? $path : null;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        $_SESSION["user_id"] = (int) $user["user_id"];
        $_SESSION["username"] = (string) $user["username"];
        $_SESSION["role"] = (string) $user["role"];
        $_SESSION["profile_image_path"] = $user["profile_image_path"] ?? null;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function requireLogin(): int
    {
        $id = $this->id();
        if ($id === null) {
            Flash::add("error", "Please sign in to continue.");
            header("Location: " . App::url("login"), true, 303);
            exit();
        }
        return $id;
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();
        if ($this->role() !== "Admin") {
            Flash::add(
                "error",
                "You do not have permission to view that page.",
            );
            header("Location: " . App::url("dashboard"), true, 303);
            exit();
        }
    }

    public function restore(int $userId): bool
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return false;
        }
        $this->login($user);
        return true;
    }
}
