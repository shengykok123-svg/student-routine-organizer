<?php

use App\Core\Csrf;
use App\Core\View;

$stats = [
    ["Active accounts", $summary["active_users"], "bi-person-check", "text-success"],
    ["Suspended accounts", $summary["suspended_users"], "bi-person-slash", "text-danger"],
    ["Student accounts", $summary["students"], "bi-people", "text-primary"],
    ["Module records", $summary["exercises"] + $summary["diary_entries"] + $summary["money_records"] + $summary["habits"], "bi-database", "text-warning"],
];
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Administrator only</p>
        <h1>System Administration</h1>
        <p class="page-subtitle">Manage accounts, communication, system health, and administrative activity.</p>
    </div>
    <a class="btn btn-primary" href="<?= View::e($baseUrl) ?>/admin/users/create">
        <i class="bi bi-person-plus"></i> Add User
    </a>
</section>

<section class="admin-summary mb-4">
    <?php foreach ($stats as [$label, $value, $icon, $color]): ?>
        <article class="content-card admin-stat">
            <i class="bi <?= $icon ?> <?= $color ?> fs-5"></i>
            <strong><?= (int) $value ?></strong>
            <span><?= View::e($label) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="content-card mb-4">
    <div class="card-section-heading">
        <div>
            <p class="card-kicker">Administration tools</p>
            <h2>Control Centre</h2>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= View::e($baseUrl) ?>/admin/announcements"><i class="bi bi-megaphone"></i> Announcement Centre</a>
        <a class="btn btn-outline-primary" href="<?= View::e($baseUrl) ?>/admin/audit"><i class="bi bi-clock-history"></i> Audit Log</a>
        <a class="btn btn-outline-primary" href="<?= View::e($baseUrl) ?>/admin/maintenance"><i class="bi bi-database-gear"></i> Data Maintenance</a>
    </div>
</section>

<section class="content-card p-0 overflow-hidden">
    <div class="card-section-heading p-3 mb-0">
        <div>
            <p class="card-kicker">Registered accounts</p>
            <h2>User Management</h2>
        </div>
        <span class="record-count"><?= count($users) ?> shown</span>
    </div>
    <form class="row g-2 px-3 pb-3" method="get">
        <div class="col-md-5"><input class="form-control" name="search" value="<?= View::e($filters["search"]) ?>" placeholder="Search name, username, or email"></div>
        <div class="col-md-2">
            <select class="form-select" name="role">
                <option value="">All roles</option>
                <option value="Student" <?= $filters["role"] === "Student" ? "selected" : "" ?>>Students</option>
                <option value="Admin" <?= $filters["role"] === "Admin" ? "selected" : "" ?>>Administrators</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                <option value="Active" <?= $filters["status"] === "Active" ? "selected" : "" ?>>Active</option>
                <option value="Suspended" <?= $filters["status"] === "Suspended" ? "selected" : "" ?>>Suspended</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">Filter</button><a class="btn btn-link" href="<?= View::e($baseUrl) ?>/admin">Clear</a></div>
    </form>
    <div class="table-responsive">
        <table class="table app-table align-middle mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= View::e($user["full_name"] ?: $user["username"]) ?></strong><small class="d-block text-muted">@<?= View::e($user["username"]) ?> · <?= View::e($user["email"]) ?></small></td>
                        <td><span class="status-pill <?= $user["role"] === "Admin" ? "status-active" : "status-completed" ?>"><?= View::e($user["role"]) ?></span></td>
                        <td><span class="status-pill <?= $user["account_status"] === "Active" ? "status-active" : "status-overdue" ?>"><?= View::e($user["account_status"]) ?></span></td>
                        <td><?= View::e(date("d M Y", strtotime($user["created_at"]))) ?></td>
                        <td><div class="table-actions justify-content-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= View::e($baseUrl) ?>/admin/users/edit?id=<?= (int) $user["user_id"] ?>" aria-label="Edit user"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= View::e($baseUrl) ?>/admin/users/<?= $user["account_status"] === "Active" ? "suspend" : "resume" ?>">
                                <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><input type="hidden" name="user_id" value="<?= (int) $user["user_id"] ?>">
                                <button class="btn btn-sm btn-outline-warning" title="<?= $user["account_status"] === "Active" ? "Suspend" : "Resume" ?>"><i class="bi <?= $user["account_status"] === "Active" ? "bi-pause-circle" : "bi-play-circle" ?>"></i></button>
                            </form>
                            <form method="post" action="<?= View::e($baseUrl) ?>/admin/users/delete" onsubmit="return confirm('Delete this user and all of their owned data?')">
                                <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><input type="hidden" name="user_id" value="<?= (int) $user["user_id"] ?>">
                                <button class="btn btn-sm btn-outline-danger" aria-label="Delete user"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?><tr><td colspan="5"><div class="empty-state">No user accounts match these filters.</div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="dashboard-grid mt-4">
    <article class="content-card">
        <div class="card-section-heading"><div><p class="card-kicker">Latest activity</p><h2>Audit Snapshot</h2></div><a class="small" href="<?= View::e($baseUrl) ?>/admin/audit">View all</a></div>
        <?php foreach ($recentAudit as $log): ?><div class="habit-preview-row"><i class="bi bi-shield-check"></i><div><strong><?= View::e(ucwords(str_replace("_", " ", $log["action_name"]))) ?></strong><small><?= View::e($log["admin_username"]) ?> · <?= View::e(date("d M H:i", strtotime($log["created_at"]))) ?></small></div></div><?php endforeach; ?>
        <?php if (!$recentAudit): ?><p class="text-muted mb-0">No administrator actions have been recorded yet.</p><?php endif; ?>
    </article>
    <article class="content-card">
        <div class="card-section-heading"><div><p class="card-kicker">Communication</p><h2>Recent Announcements</h2></div><a class="small" href="<?= View::e($baseUrl) ?>/admin/announcements">Open centre</a></div>
        <?php foreach ($recentAnnouncements as $announcement): ?><div class="habit-preview-row"><i class="bi bi-megaphone"></i><div><strong><?= View::e($announcement["title"]) ?></strong><small><?= (int) $announcement["recipient_count"] ?> recipients · <?= View::e(date("d M Y", strtotime($announcement["created_at"]))) ?></small></div></div><?php endforeach; ?>
        <?php if (!$recentAnnouncements): ?><p class="text-muted mb-0">No announcements have been published yet.</p><?php endif; ?>
    </article>
</section>
