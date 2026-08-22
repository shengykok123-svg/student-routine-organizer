<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

$authenticated = isset($auth) && $auth instanceof Auth && $auth->check();
$requestPath = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?: "/";
$relativePath =
    $baseUrl !== "" && str_starts_with($requestPath, $baseUrl)
        ? substr($requestPath, strlen($baseUrl))
        : $requestPath;
$relativePath = trim($relativePath, "/");
$activeModule = match (true) {
    str_starts_with($relativePath, "exercise") => "exercise",
    str_starts_with($relativePath, "diary") => "diary",
    str_starts_with($relativePath, "money") => "money",
    str_starts_with($relativePath, "habit") => "habit",
    str_starts_with($relativePath, "admin") => "admin",
    str_starts_with($relativePath, "profile") => "profile",
    str_starts_with($relativePath, "settings") => "settings",
    default => "dashboard",
};
$imageBase = View::e($baseUrl) . "/assets/images";
$unreadNotificationCount = max(0, (int) ($unreadNotificationCount ?? 0));
$notificationBadge = $unreadNotificationCount > 99 ? "99+" : (string) $unreadNotificationCount;
$navigation = [
    [
        "key" => "dashboard",
        "label" => "Dashboard",
        "icon" => "sidebar-dashboard-default.png",
    ],
    [
        "key" => "exercise",
        "label" => "Exercise Tracker",
        "icon" => "sidebar-exercise-default.png",
        "activeIcon" => "sidebar-exercise-active.png",
    ],
    [
        "key" => "diary",
        "label" => "Diary Journal",
        "icon" => "sidebar-diary-default.png",
        "activeIcon" => "sidebar-diary-active.png",
    ],
    [
        "key" => "money",
        "label" => "Money Tracker",
        "icon" => "sidebar-money-default.png",
        "activeIcon" => "sidebar-money-active.png",
    ],
    [
        "key" => "habit",
        "label" => "Habit Tracker",
        "icon" => "sidebar-habit-default.png",
        "activeIcon" => "sidebar-habit-active.png",
    ],
];
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>
            <?= View::e(($pageTitle ?? "") . " | " . $appName) ?>
        </title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/global.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/assets.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/readability.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/auth-polish.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/illustrations.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/register-polish.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/dashboard-polish.css">
        <link rel="stylesheet" href="<?= View::e(
            $baseUrl,
        ) ?>/assets/css/landing-redesign.css">
    </head>
    <body class="<?= $authenticated ? "app-body" : "auth-body" ?>">
        <?php if ($authenticated): ?>
        <div class="app-shell">
            <aside class="app-sidebar d-none d-lg-flex">
                <a class="brand-lockup" href="<?= View::e(
                    $baseUrl,
                ) ?>/dashboard" aria-label="Student Routine Organizer dashboard">
                    <img class="brand-logo" src="<?= $imageBase ?>/branding/logo-app-64.png" alt="">
                    <span>SRO</span>
                    </a>
                    <nav class="sidebar-nav" aria-label="Primary navigation">
                        <?php foreach ($navigation as $item):

                            $isActive = $activeModule === $item["key"];
                            $icon = $isActive
                                ? $item["activeIcon"] ?? $item["icon"]
                                : $item["icon"];
                            ?>
                        <a class="sidebar-link <?= $isActive
                            ? "is-active"
                            : "" ?>" href="<?= View::e($baseUrl) ?>/<?= $item[
    "key"
] === "dashboard"
    ? "dashboard"
    : $item["key"] ?>">
                            <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/<?= View::e(
                                $icon,
                            ) ?>" alt="">
                            <span>
                                <?= View::e($item["label"]) ?>
                            </span>
                        </a>
                        <?php
                        endforeach; ?>
                        <?php if ($auth->role() === "Admin"): ?>
                        <div class="sidebar-section-label">Administration</div>
                            <a class="sidebar-link <?= $activeModule === "admin"
                                ? "is-active"
                                : "" ?>" href="<?= View::e($baseUrl) ?>/admin">
                                <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-admin-default.png" alt="">
                                <span>System Administration</span>
                                </a>
                                <?php endif; ?>
                                <div class="sidebar-section-label">Account</div>
                                    <a class="sidebar-link <?= $activeModule ===
                                    "profile"
                                        ? "is-active"
                                        : "" ?>" href="<?= View::e(
                                            $baseUrl,
                                        ) ?>/profile">
                                        <i class="bi bi-person">
                                        </i>
                                        <span>Profile</span>
                                        </a>
                                        <a class="sidebar-link <?= $activeModule ===
                                        "settings"
                                            ? "is-active"
                                            : "" ?>" href="<?= View::e(
                                                $baseUrl,
                                            ) ?>/settings">
                                            <i class="bi bi-gear">
                                            </i>
                                            <span>Settings</span>
                                            </a>
                                        </nav>
                                        <form class="sidebar-logout" method="post" action="<?= View::e(
                                            $baseUrl,
                                        ) ?>/logout">
                                            <input type="hidden" name="_csrf" value="<?= View::e(
                                                Csrf::token(),
                                            ) ?>">
                                            <button type="submit">
                                                <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-logout-default.png" alt="">
                                                <span>Logout</span>
                                                </button>
                                            </form>
                                        </aside>
                                        <div class="app-main">
                                            <header class="app-topbar">
                                                <button class="btn menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavigation" aria-label="Open navigation">
                                                    <i class="bi bi-list">
                                                    </i>
                                                </button>
                                                <div class="topbar-spacer">
                                                </div>
                                                <a class="topbar-bell" href="<?= View::e(
                                                    $baseUrl,
                                                ) ?>/notifications" aria-label="Notifications<?= $unreadNotificationCount ? ": " . $notificationBadge . " unread" : "" ?>">
                                                    <i class="bi bi-bell">
                                                    </i>
                                                    <?php if ($unreadNotificationCount > 0): ?>
                                                        <span class="notification-count" aria-hidden="true"><?= View::e($notificationBadge) ?></span>
                                                    <?php endif; ?>
                                                </a>
                                                <div class="user-summary">
                                                    <div>
                                                        <strong>Hello, <?= View::e(
                                                            $auth->username(),
                                                        ) ?>
                                                        </strong>
                                                        <small>
                                                            <?= View::e(
                                                                $auth->role(),
                                                            ) ?>
                                                        </small>
                                                    </div>
                                                    <?php if (
                                                        $auth->profileImagePath()
                                                    ): ?>
                                                    <img class="user-avatar user-avatar-image" src="<?= View::e(
                                                        $baseUrl,
                                                    ) ?>/profile/photo" alt="Profile picture">
                                                    <?php else: ?>
                                                    <span class="user-avatar">
                                                        <?= View::e(
                                                            strtoupper(
                                                                substr(
                                                                    $auth->username(),
                                                                    0,
                                                                    1,
                                                                ),
                                                            ),
                                                        ) ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </header>
                                            <main class="app-content">
                                                <?php else: ?>
                                                <main class="auth-main <?= ($pageTitle ??
                                                    "") ===
                                                "Welcome"
                                                    ? "landing-main"
                                                    : "" ?>">
                                                    <?php endif; ?>
                                                    <?php foreach (
                                                        $flashes as $flash
                                                    ): ?>
                                                    <div class="app-flash flash-<?= View::e(
                                                        $flash["type"],
                                                    ) ?>">
                                                        <i class="bi <?= $flash[
                                                            "type"
                                                        ] === "success"
                                                            ? "bi-check-circle-fill"
                                                            : "bi-exclamation-circle-fill" ?>">
                                                        </i>
                                                        <span>
                                                            <?= View::e(
                                                                $flash[
                                                                    "message"
                                                                ],
                                                            ) ?>
                                                        </span>
                                                    </div>
                                                    <?php endforeach; ?>
                                                    <?php if (
                                                        $authenticated
                                                    ): ?>
                                                    <div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileNavigation">
                                                        <div class="offcanvas-header">
                                                            <a class="brand-lockup" href="<?= View::e(
                                                                $baseUrl,
                                                            ) ?>/dashboard" aria-label="Student Routine Organizer dashboard">
                                                                <img class="brand-logo" src="<?= $imageBase ?>/branding/logo-app-64.png" alt="">
                                                                <span>SRO</span>
                                                                </a>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close">
                                                                </button>
                                                            </div>
                                                            <div class="offcanvas-body d-flex flex-column">
                                                                <nav class="sidebar-nav">
                                                                    <?php
                                                                    foreach (
                                                                        $navigation as $item
                                                                    ):

                                                                        $isActive =
                                                                            $activeModule ===
                                                                            $item[
                                                                                "key"
                                                                            ];
                                                                        $icon = $isActive
                                                                            ? $item[
                                                                                    "activeIcon"
                                                                                ] ??
                                                                                $item[
                                                                                    "icon"
                                                                                ]
                                                                            : $item[
                                                                                "icon"
                                                                            ];
                                                                        ?>
                                                                    <a class="sidebar-link <?= $isActive
                                                                        ? "is-active"
                                                                        : "" ?>" href="<?= View::e(
                                                                            $baseUrl,
                                                                        ) ?>/<?= $item["key"] === "dashboard" ? "dashboard" : $item["key"] ?>">
                                                                        <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/<?= View::e(
                                                                            $icon,
                                                                        ) ?>" alt="">
                                                                        <span>
                                                                            <?= View::e(
                                                                                $item[
                                                                                    "label"
                                                                                ],
                                                                            ) ?>
                                                                        </span>
                                                                    </a>
                                                                    <?php
                                                                    endforeach;
                                                        if (
                                                            $auth->role() ===
                                                            "Admin"
                                                        ): ?>
                                                                    <div class="sidebar-section-label">Administration</div>
                                                                        <a class="sidebar-link <?= $activeModule ===
                                                            "admin"
                                                                ? "is-active"
                                                                : "" ?>" href="<?= View::e(
                                                                    $baseUrl,
                                                                ) ?>/admin">
                                                                            <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-admin-default.png" alt="">
                                                                            <span>System Administration</span>
                                                                            </a>
                                                                            <?php endif;
                                                        ?>
                                                                            <div class="sidebar-section-label">Account</div>
                                                                                <?php foreach (
                                                                                    [
                                                                                        [
                                                                                            "profile",
                                                                                            "Profile",
                                                                                            "bi-person",
                                                                                        ],
                                                                                        [
                                                                                            "settings",
                                                                                            "Settings",
                                                                                            "bi-gear",
                                                                                        ],
                                                                                    ] as [
                                                                                        $key,
                                                                                        $label,
                                                                                        $icon,
                                                                                    ]
                                                                                ): ?>
                                                                                <a class="sidebar-link <?= $activeModule ===
                                                                                $key
                                                                                    ? "is-active"
                                                                                    : "" ?>" href="<?= View::e(
                                                                                        $baseUrl,
                                                                                    ) ?>/<?= $key ?>">
                                                                                    <i class="bi <?= $icon ?>">
                                                                                    </i>
                                                                                    <span>
                                                                                        <?= $label ?>
                                                                                    </span>
                                                                                </a>
                                                                                <?php endforeach; ?>
                                                                            </nav>
                                                                            <form class="sidebar-logout" method="post" action="<?= View::e(
                                                                                $baseUrl,
                                                                            ) ?>/logout">
                                                                                <input type="hidden" name="_csrf" value="<?= View::e(
                                                                                    Csrf::token(),
                                                                                ) ?>">
                                                                                <button type="submit">
                                                                                    <img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-logout-default.png" alt="">
                                                                                    <span>Logout</span>
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
