<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;

/** Issues and verifies persistent login tokens. */
final class RememberMeService
{
    private const COOKIE = "sro_remember";
    private const LIFETIME = 2592000;

    public function issue(int $userId): void
    {
        $expires = time() + self::LIFETIME;
        $payload = $userId . "|" . $expires;
        $value =
            $payload .
            "|" .
            hash_hmac("sha256", $payload, App::rememberSecret());
        $this->set($value, $expires);
    }

    public function userIdFromCookie(): ?int
    {
        $value = $_COOKIE[self::COOKIE] ?? null;
        if (!is_string($value)) {
            return null;
        }

        $parts = explode("|", $value);
        if (
            count($parts) !== 3 ||
            !ctype_digit($parts[0]) ||
            !ctype_digit($parts[1])
        ) {
            $this->clear();
            return null;
        }

        [$userId, $expires, $signature] = $parts;
        $payload = $userId . "|" . $expires;
        if (
            (int) $expires < time() ||
            !hash_equals(
                hash_hmac("sha256", $payload, App::rememberSecret()),
                $signature,
            )
        ) {
            $this->clear();
            return null;
        }
        return (int) $userId;
    }

    public function clear(): void
    {
        $this->set("", time() - 3600);
    }

    private function set(string $value, int $expires): void
    {
        $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
        setcookie(self::COOKIE, $value, [
            "expires" => $expires,
            "path" => App::cookiePath(),
            "secure" => $https,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
    }
}
