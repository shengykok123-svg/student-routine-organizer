<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App;

/** Base helpers shared by HTTP controllers. */
abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    protected function redirect(string $path): never
    {
        header("Location: " . App::url($path), true, 303);
        exit();
    }

    protected function requirePost(): void
    {
        if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
            http_response_code(405);
            header("Allow: POST");
            exit("This action accepts POST requests only.");
        }
    }

    protected function requireCsrf(): void
    {
        if (!Csrf::valid($_POST["_csrf"] ?? null)) {
            http_response_code(403);
            exit(
                "Your form session has expired. Please return to the form and try again."
            );
        }
    }
}
