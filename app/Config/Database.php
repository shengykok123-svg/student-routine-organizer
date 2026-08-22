<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/** Creates the shared database connection. */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv("SRO_DB_HOST") ?: "127.0.0.1";
        $name = getenv("SRO_DB_NAME") ?: "student_routine_organizer";
        $user = getenv("SRO_DB_USER") ?: "root";
        $password = getenv("SRO_DB_PASSWORD");
        $password = is_string($password) ? $password : "";

        try {
            self::$connection = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
            return self::$connection;
        } catch (PDOException $exception) {
            error_log("[SRO database] " . $exception->getMessage());
            http_response_code(500);
            exit(
                "The system database is unavailable. Check the XAMPP MySQL service and database configuration."
            );
        }
    }
}
