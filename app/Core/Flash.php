<?php

declare(strict_types=1);

namespace App\Core;

/** Stores one-time feedback messages in the session. */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION["_flash"][] = ["type" => $type, "message" => $message];
    }

    /** @return list<array{type:string,message:string}> */
    public static function consume(): array
    {
        $messages = $_SESSION["_flash"] ?? [];
        unset($_SESSION["_flash"]);
        return is_array($messages) ? $messages : [];
    }
}
