<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App;

/** Renders view templates with escaped data. */
final class View
{
    /** @param array<string,mixed> $data */
    public static function render(string $template, array $data = []): void
    {
        $file = dirname(__DIR__) . "/Views/" . $template . ".php";
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: " . $template);
        }

        $data["baseUrl"] = App::baseUrl();
        $data["appName"] = App::NAME;
        $data["flashes"] = Flash::consume();
        $data["auth"] = $GLOBALS["sro_auth"] ?? null;
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . "/Views/layouts/header.php";
        require $file;
        require dirname(__DIR__) . "/Views/layouts/footer.php";
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8",
        );
    }
}
