<?php

declare(strict_types=1);

use App\Config\App;
use App\Config\Database;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Notification;
use App\Models\User;
use App\Services\RememberMeService;

define("SRO_ROOT", dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, "App\\")) {
        return;
    }
    $relativeClass = substr($class, strlen("App\\"));
    $file = __DIR__ . "/" . str_replace("\\", "/", $relativeClass) . ".php";
    if (is_file($file)) {
        require $file;
    }
});

date_default_timezone_set(App::TIMEZONE);
ini_set("display_errors", "0");
error_reporting(E_ALL);

Session::start();
$pdo = Database::connection();
$users = new User($pdo);
$notifications = new Notification($pdo);
$auth = new Auth($users);
$rememberMe = new RememberMeService();
$GLOBALS["sro_auth"] = $auth;
$GLOBALS["sro_notifications"] = $notifications;

if (!$auth->check()) {
    $rememberedId = $rememberMe->userIdFromCookie();
    if ($rememberedId !== null && !$auth->restore($rememberedId)) {
        $rememberMe->clear();
    }
}
