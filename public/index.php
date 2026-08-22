<?php

declare(strict_types=1);

require dirname(__DIR__) . "/app/bootstrap.php";

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ExerciseController;
use App\Controllers\DiaryController;
use App\Controllers\HabitController;
use App\Controllers\MoneyController;
use App\Controllers\AccountController;
use App\Controllers\NotificationController;
use App\Core\Router;

$router = new Router();
$notifications = new \App\Models\Notification($pdo);
$authController = new AuthController(
    $users,
    $auth,
    $rememberMe,
    $notifications,
);
$dashboardController = new DashboardController($auth, $pdo);
$adminController = new AdminController(
    $auth,
    $users,
    $pdo,
    new \App\Models\AdminAuditLog($pdo),
    new \App\Models\Announcement($pdo),
    new \App\Services\AdminMaintenanceService($pdo),
);
$exerciseController = new ExerciseController(
    $auth,
    new \App\Models\Exercise($pdo),
    new \App\Models\ExerciseAttachment($pdo),
    new \App\Services\ExerciseValidationService(),
    new \App\Services\ExerciseDuplicateConfirmationService(),
    new \App\Services\ExerciseAttachmentService(),
);
$diaryController = new DiaryController(
    $auth,
    new \App\Models\DiaryEntry($pdo),
    new \App\Services\DiaryUploadService(),
);
$habitController = new HabitController(
    $auth,
    new \App\Models\Habit($pdo),
    new \App\Models\HabitLog($pdo),
);
$moneyController = new MoneyController(
    $auth,
    new \App\Models\MoneyRecord($pdo),
    new \App\Services\MoneyReceiptUploadService(),
);
$accountController = new AccountController(
    $auth,
    $users,
    new \App\Models\UserSettings($pdo),
    new \App\Services\ProfileImageUploadService(),
);
$notificationController = new NotificationController($auth, $notifications);

$router->get("", [$authController, "home"]);
$router->get("login", [$authController, "loginForm"]);
$router->post("login", [$authController, "login"]);
$router->get("register", [$authController, "registerForm"]);
$router->post("register", [$authController, "register"]);
$router->post("logout", [$authController, "logout"]);
$router->get("dashboard", [$dashboardController, "index"]);
$router->get("admin", [$adminController, "index"]);
$router->get("admin/users/create", [$adminController, "createForm"]);
$router->post("admin/users/store", [$adminController, "store"]);
$router->get("admin/users/edit", [$adminController, "editForm"]);
$router->post("admin/users/update", [$adminController, "update"]);
$router->post("admin/users/delete", [$adminController, "delete"]);
$router->post("admin/users/suspend", [$adminController, "suspend"]);
$router->post("admin/users/resume", [$adminController, "resume"]);
$router->get("admin/announcements", [$adminController, "announcements"]);
$router->post("admin/announcements", [$adminController, "storeAnnouncement"]);
$router->get("admin/audit", [$adminController, "audit"]);
$router->get("admin/maintenance", [$adminController, "maintenance"]);
$router->get("admin/maintenance/export", [$adminController, "exportSummary"]);
$router->post("admin/maintenance/clean-uploads", [$adminController, "cleanUploads"]);
$router->get("profile", [$accountController, "profile"]);
$router->post("profile", [$accountController, "updateProfile"]);
$router->get("profile/photo", [$accountController, "profileImage"]);
$router->get("settings", [$accountController, "settings"]);
$router->post("settings", [$accountController, "updateSettings"]);
$router->get("notifications", [$notificationController, "index"]);
$router->post("notifications/read", [$notificationController, "read"]);
$router->get("exercise", [$exerciseController, "index"]);
$router->get("exercise/create", [$exerciseController, "createForm"]);
$router->post("exercise/store", [$exerciseController, "store"]);
$router->get("exercise/view", [$exerciseController, "viewRecord"]);
$router->get("exercise/edit", [$exerciseController, "editForm"]);
$router->post("exercise/update", [$exerciseController, "update"]);
$router->post("exercise/delete", [$exerciseController, "delete"]);
$router->post("exercise/attachment", [$exerciseController, "upload"]);
$router->post("exercise/attachment/delete", [
    $exerciseController,
    "deleteAttachment",
]);
$router->get("exercise/export", [$exerciseController, "export"]);
$router->get("diary", [$diaryController, "index"]);
$router->get("diary/create", [$diaryController, "createForm"]);
$router->post("diary/store", [$diaryController, "store"]);
$router->get("diary/view", [$diaryController, "viewEntry"]);
$router->get("diary/edit", [$diaryController, "editForm"]);
$router->post("diary/update", [$diaryController, "update"]);
$router->post("diary/delete", [$diaryController, "delete"]);
$router->get("diary/calendar", [$diaryController, "calendar"]);
$router->get("habit", [$habitController, "index"]);
$router->get("habit/create", [$habitController, "createForm"]);
$router->post("habit/store", [$habitController, "store"]);
$router->get("habit/edit", [$habitController, "editForm"]);
$router->post("habit/update", [$habitController, "update"]);
$router->post("habit/delete", [$habitController, "delete"]);
$router->post("habit/check-in", [$habitController, "checkIn"]);
$router->get("money", [$moneyController, "index"]);
$router->get("money/export", [$moneyController, "export"]);
$router->get("money/receipt", [$moneyController, "receipt"]);
$router->get("money/create", [$moneyController, "createForm"]);
$router->post("money/store", [$moneyController, "store"]);
$router->get("money/edit", [$moneyController, "editForm"]);
$router->post("money/update", [$moneyController, "update"]);
$router->post("money/delete", [$moneyController, "delete"]);

$path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?: "/";
$baseUrl = \App\Config\App::baseUrl();
if ($baseUrl !== "" && str_starts_with($path, $baseUrl)) {
    $path = substr($path, strlen($baseUrl));
}
$router->dispatch($_SERVER["REQUEST_METHOD"] ?? "GET", $path);
