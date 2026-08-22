<?php

declare(strict_types=1);

namespace App\Core;

/** Maps HTTP requests to controller actions. */
final class Router
{
    /** @var array<string,callable> */
    private array $routes = [];

    public function get(string $path, callable $action): void
    {
        $this->routes["GET " . trim($path, "/")] = $action;
    }

    public function post(string $path, callable $action): void
    {
        $this->routes["POST " . trim($path, "/")] = $action;
    }

    public function dispatch(string $method, string $path): void
    {
        $key = strtoupper($method) . " " . trim($path, "/");
        $action = $this->routes[$key] ?? null;
        if ($action === null) {
            http_response_code(404);
            exit("Page not found.");
        }
        $action();
    }
}
