<?php

declare(strict_types=1);

namespace App\Config;

final class App
{
    public const NAME = 'Student Routine Organizer';
    public const TIMEZONE = 'Asia/Kuala_Lumpur';

    public static function baseUrl(): string
    {
        $configured = getenv('SRO_BASE_URL');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/student-routine-organizer/public/index.php');
        return rtrim(preg_replace('#/index\\.php$#', '', $script) ?: '', '/');
    }

    public static function url(string $path = ''): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }

    public static function cookiePath(): string
    {
        $baseUrl = self::baseUrl();

        // RFC-compliant cookie paths cannot contain commas. The project folder
        // currently has commas in its XAMPP URL, so use the safe site-wide path.
        return str_contains($baseUrl, ',') ? '/' : $baseUrl . '/';
    }

    public static function rememberSecret(): string
    {
        $secret = getenv('SRO_REMEMBER_SECRET');
        return is_string($secret) && strlen($secret) >= 32
            ? $secret
            : 'replace-this-demo-secret-with-a-long-random-value-before-production';
    }
}
