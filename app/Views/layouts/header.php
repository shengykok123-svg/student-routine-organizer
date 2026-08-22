<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

$authenticated = isset($auth) && $auth instanceof Auth && $auth->check();
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$relativePath = $baseUrl !== '' && str_starts_with($requestPath, $baseUrl) ? substr($requestPath, strlen($baseUrl)) : $requestPath;
$relativePath = trim($relativePath, '/');
$activeModule = match (true) {
    str_starts_with($relativePath, 'exercise') => 'exercise', str_starts_with($relativePath, 'diary') => 'diary',
    str_starts_with($relativePath, 'money') => 'money', str_starts_with($relativePath, 'habit') => 'habit',
    str_starts_with($relativePath, 'admin') => 'admin', str_starts_with($relativePath, 'profile') => 'profile',
    str_starts_with($relativePath, 'settings') => 'settings', default => 'dashboard',
};
$imageBase = View::e($baseUrl) . '/assets/images';
$unreadNotificationCount = max(0, (int) ($unreadNotificationCount ?? 0));
$notificationBadge = $unreadNotificationCount > 99 ? '99+' : (string) $unreadNotificationCount;
$isAdmin = $authenticated && $auth->role() === 'Admin';
$themePreference = $authenticated ? $auth->themePreference() : 'system';
$homePath = $isAdmin ? 'admin' : 'dashboard';
$navigation = $isAdmin ? [
    ['key' => 'admin-system', 'path' => 'admin', 'label' => 'System Administration', 'icon' => 'sidebar-admin-default.png'],
    ['key' => 'admin-dashboard', 'path' => 'admin/dashboard', 'label' => 'Dashboard', 'icon' => 'sidebar-dashboard-default.png'],
    ['key' => 'admin-users', 'path' => 'admin/users', 'label' => 'User Management', 'icon' => 'sidebar-exercise-default.png'],
    ['key' => 'admin-announcements', 'path' => 'admin/announcements', 'label' => 'Announcements', 'icon' => 'sidebar-diary-default.png'],
    ['key' => 'admin-audit', 'path' => 'admin/audit', 'label' => 'Audit Log', 'icon' => 'sidebar-habit-default.png'],
    ['key' => 'admin-maintenance', 'path' => 'admin/maintenance', 'label' => 'Data Maintenance', 'icon' => 'sidebar-money-default.png'],
] : [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'sidebar-dashboard-default.png'],
    ['key' => 'exercise', 'label' => 'Exercise Tracker', 'icon' => 'sidebar-exercise-default.png', 'activeIcon' => 'sidebar-exercise-active.png'],
    ['key' => 'diary', 'label' => 'Diary Journal', 'icon' => 'sidebar-diary-default.png', 'activeIcon' => 'sidebar-diary-active.png'],
    ['key' => 'money', 'label' => 'Money Tracker', 'icon' => 'sidebar-money-default.png', 'activeIcon' => 'sidebar-money-active.png'],
    ['key' => 'habit', 'label' => 'Habit Tracker', 'icon' => 'sidebar-habit-default.png', 'activeIcon' => 'sidebar-habit-active.png'],
];
?>
<!doctype html>
<html lang="en" data-theme-preference="<?= View::e($themePreference) ?>" data-authenticated="<?= $authenticated ? '1' : '0' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e(($pageTitle ?? '') . ' | ' . $appName) ?></title>
    <script>
        (() => {
            const root = document.documentElement;
            const preference = root.dataset.authenticated === '1' ? root.dataset.themePreference : (localStorage.getItem('sro-theme-preference') || root.dataset.themePreference || 'system');
            const theme = preference === 'system' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : preference;
            root.dataset.themePreference = preference;
            root.dataset.theme = theme;
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/assets.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/readability.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/auth-polish.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/illustrations.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/register-polish.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/dashboard-polish.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/landing-redesign.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/diary-reader.css">
    <link rel="stylesheet" href="<?= View::e($baseUrl) ?>/assets/css/theme-overrides.css">
</head>
<body class="<?= $authenticated ? 'app-body' : 'auth-body' ?>" data-theme-endpoint="<?= $authenticated ? View::e($baseUrl) . '/settings/theme' : '' ?>" data-theme-csrf="<?= $authenticated ? View::e(Csrf::token()) : '' ?>">
<?php if ($authenticated): ?>
<div class="app-shell">
    <aside class="app-sidebar d-none d-lg-flex">
        <a class="brand-lockup" href="<?= View::e($baseUrl) ?>/<?= $homePath ?>" aria-label="Student Routine Organizer dashboard"><img class="brand-logo" src="<?= $imageBase ?>/branding/logo-app-64.png" alt=""><span>SRO</span></a>
        <nav class="sidebar-nav" aria-label="Primary navigation">
            <?php if ($isAdmin): ?><div class="sidebar-section-label">Administration</div><?php endif; ?>
            <?php foreach ($navigation as $item): $isActive = isset($item['path']) ? ($item['path'] === 'admin' ? $relativePath === 'admin' : str_starts_with($relativePath, $item['path'])) : $activeModule === $item['key'];
                $icon = $isActive ? ($item['activeIcon'] ?? $item['icon']) : $item['icon']; ?>
                <a class="sidebar-link <?= $isActive ? 'is-active' : '' ?>" href="<?= View::e($baseUrl) ?>/<?= $item['key'] === 'dashboard' ? 'dashboard' : ($item['path'] ?? $item['key']) ?>"><img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/<?= View::e($icon) ?>" alt=""><span><?= View::e($item['label']) ?></span></a>
            <?php endforeach; ?>
            <div class="sidebar-section-label">Account</div><a class="sidebar-link <?= $activeModule === 'profile' ? 'is-active' : '' ?>" href="<?= View::e($baseUrl) ?>/profile"><i class="bi bi-person"></i><span>Profile</span></a><a class="sidebar-link <?= $activeModule === 'settings' ? 'is-active' : '' ?>" href="<?= View::e($baseUrl) ?>/settings"><i class="bi bi-gear"></i><span>Settings</span></a>
        </nav>
        <form class="sidebar-logout" method="post" action="<?= View::e($baseUrl) ?>/logout"><input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><button type="submit"><img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-logout-default.png" alt=""><span>Logout</span></button></form>
    </aside>
    <div class="app-main">
        <header class="app-topbar"><button class="btn menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavigation" aria-label="Open navigation"><i class="bi bi-list"></i></button><div class="topbar-spacer"></div><div class="topbar-controls"><?php require __DIR__ . "/../partials/theme_toggle.php"; ?><a class="topbar-bell" href="<?= View::e($baseUrl) ?>/notifications" aria-label="Notifications<?= $unreadNotificationCount ? ': ' . View::e($notificationBadge) . ' unread' : '' ?>"><i class="bi bi-bell"></i><?php if ($unreadNotificationCount > 0): ?><span class="notification-count" aria-hidden="true"><?= View::e($notificationBadge) ?></span><?php endif; ?></a></div><div class="user-summary"><div><strong>Hello, <?= View::e($auth->username()) ?></strong><small><?= View::e($auth->role()) ?></small></div><?php if ($auth->profileImagePath()): ?><img class="user-avatar user-avatar-image" src="<?= View::e($baseUrl) ?>/profile/photo" alt="Profile picture"><?php else: ?><span class="user-avatar"><?= View::e(strtoupper(substr($auth->username(), 0, 1))) ?></span><?php endif; ?></div></header>
        <main class="app-content">
<?php else: ?>
<main class="auth-main <?= ($pageTitle ?? '') === 'Welcome' ? 'landing-main' : '' ?>">
<?php endif; ?>
<?php foreach ($flashes as $flash): ?><div class="app-flash flash-<?= View::e($flash['type']) ?>"><i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i><span><?= View::e($flash['message']) ?></span></div><?php endforeach; ?>
<?php if ($authenticated): ?>
    <div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileNavigation"><div class="offcanvas-header"><a class="brand-lockup" href="<?= View::e($baseUrl) ?>/<?= $homePath ?>" aria-label="Student Routine Organizer dashboard"><img class="brand-logo" src="<?= $imageBase ?>/branding/logo-app-64.png" alt=""><span>SRO</span></a><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button></div><div class="offcanvas-body d-flex flex-column"><nav class="sidebar-nav"><?php if ($isAdmin): ?><div class="sidebar-section-label">Administration</div><?php endif; ?><?php foreach ($navigation as $item): $isActive = isset($item['path']) ? ($item['path'] === 'admin' ? $relativePath === 'admin' : str_starts_with($relativePath, $item['path'])) : $activeModule === $item['key'];
        $icon = $isActive ? ($item['activeIcon'] ?? $item['icon']) : $item['icon']; ?><a class="sidebar-link <?= $isActive ? 'is-active' : '' ?>" href="<?= View::e($baseUrl) ?>/<?= $item['key'] === 'dashboard' ? 'dashboard' : ($item['path'] ?? $item['key']) ?>"><img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/<?= View::e($icon) ?>" alt=""><span><?= View::e($item['label']) ?></span></a><?php endforeach; ?><div class="sidebar-section-label">Account</div><?php foreach ([['profile','Profile','bi-person'],['settings','Settings','bi-gear']] as [$key,$label,$icon]): ?><a class="sidebar-link <?= $activeModule === $key ? 'is-active' : '' ?>" href="<?= View::e($baseUrl) ?>/<?= $key ?>"><i class="bi <?= $icon ?>"></i><span><?= $label ?></span></a><?php endforeach; ?></nav><form class="sidebar-logout" method="post" action="<?= View::e($baseUrl) ?>/logout"><input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><button type="submit"><img class="sidebar-icon" src="<?= $imageBase ?>/sidebar/sidebar-logout-default.png" alt=""><span>Logout</span></button></form></div></div>
<?php endif; ?>
