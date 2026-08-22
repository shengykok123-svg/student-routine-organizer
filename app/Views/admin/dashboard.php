<?php

use App\Core\View;

$stats = [
    ["New Accounts", $metrics["new_users"], "bi-person-plus", "text-primary"],
    ["Exercise Records", $metrics["exercises"], "bi-activity", "text-primary"],
    ["Diary Entries", $metrics["diary_entries"], "bi-journal-bookmark", "text-primary"],
    ["Money Records", $metrics["money_records"], "bi-cash-stack", "text-success"],
    ["Habit Check-ins", $metrics["habit_checkins"], "bi-check2-circle", "text-warning"],
    ["Expenses", "RM " . number_format((float) $metrics["expenses"], 2), "bi-wallet2", "text-danger"],
    ["Announcements", $metrics["announcements"], "bi-megaphone", "text-info"],
    ["Admin Actions", $metrics["audit_actions"], "bi-shield-check", "text-secondary"],
];
?>
<section class="page-heading">
    <div><p class="page-eyebrow">Administrator only</p><h1>Admin Dashboard</h1><p class="page-subtitle">Aggregate system activity only. Student records cannot be added or edited here.</p></div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> System Administration</a>
</section>
<section class="filter-panel mb-4">
    <form class="row align-items-end g-3" method="get" action="<?= View::e($baseUrl) ?>/admin/dashboard">
        <div class="col-sm-6 col-lg-4">
            <label class="form-label" for="dashboard-range">Dashboard period</label>
            <select class="form-select" id="dashboard-range" name="range">
                <option value="7" <?= $range === "7" ? "selected" : "" ?>>Recent 7 Days</option>
                <option value="30" <?= $range === "30" ? "selected" : "" ?>>Recent 30 Days</option>
                <option value="90" <?= $range === "90" ? "selected" : "" ?>>Recent 90 Days</option>
                <option value="all" <?= $range === "all" ? "selected" : "" ?>>All Time</option>
            </select>
        </div>
        <div class="col-sm-auto"><button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Apply Filter</button></div>
        <div class="col-12 col-lg"><p class="text-muted mb-0"><i class="bi bi-info-circle"></i> Showing <?= View::e($periodLabel) ?> aggregate activity.</p></div>
    </form>
</section>
<section class="admin-summary admin-dashboard-summary mb-4">
    <?php foreach ($stats as [$label, $value, $icon, $color]): ?><article class="content-card admin-stat"><i class="bi <?= $icon ?> <?= $color ?> fs-5"></i><strong><?= View::e((string) $value) ?></strong><span><?= View::e($label) ?></span></article><?php endforeach; ?>
</section>
<div id="adminCharts" data-chart="<?= View::e((string) json_encode($charts, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>">
    <section class="dashboard-grid mb-4">
        <article class="content-card">
            <div class="card-section-heading">
                <div>
                    <p class="card-kicker"><?= View::e($periodLabel) ?></p>
                    <h2>Module Usage</h2>
                </div>
                <i class="bi bi-bar-chart-line text-primary fs-4"></i>
            </div>
            <div class="chart-area">
                <canvas id="adminModuleUsageChart" aria-label="Module record counts"></canvas>
            </div>
        </article>
        <article class="content-card">
            <div class="card-section-heading">
                <div>
                    <p class="card-kicker"><?= View::e($periodLabel) ?></p>
                    <h2>New Account Status</h2>
                </div>
                <i class="bi bi-people text-primary fs-4"></i>
            </div>
            <?php if (array_sum($charts["user_status"]["values"]) > 0): ?>
                <div class="chart-area">
                    <canvas id="adminUserStatusChart" aria-label="New accounts by current status"></canvas>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No accounts were created in this period.</p>
            <?php endif; ?>
        </article>
    </section>
    <section class="content-card mb-4">
        <div class="card-section-heading">
            <div>
                <p class="card-kicker"><?= View::e($periodLabel) ?></p>
                <h2>System Activity Trend</h2>
            </div>
            <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
        </div>
        <?php if ($charts["activity_trend"]["labels"]): ?>
            <div class="chart-area">
                <canvas id="adminActivityTrendChart" aria-label="System activity over time"></canvas>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">No module activity was recorded in this period.</p>
        <?php endif; ?>
    </section>
</div>
<section class="dashboard-grid">
    <article class="content-card">
        <div class="card-section-heading"><div><p class="card-kicker">Latest activity</p><h2>Audit Snapshot</h2></div><a class="small" href="<?= View::e($baseUrl) ?>/admin/audit">View all</a></div>
        <?php foreach ($recentAudit as $log): ?><div class="habit-preview-row"><i class="bi bi-shield-check"></i><div><strong><?= View::e(ucwords(str_replace("_", " ", $log["action_name"]))) ?></strong><small><?= View::e($log["admin_username"]) ?> · <?= View::e(date("d M H:i", strtotime($log["created_at"]))) ?></small></div></div><?php endforeach; ?>
        <?php if (!$recentAudit): ?><p class="text-muted mb-0">No administrator actions have been recorded yet.</p><?php endif; ?>
    </article>
    <article class="content-card">
        <div class="card-section-heading"><div><p class="card-kicker">Communication</p><h2>Recent Announcements</h2></div><a class="small" href="<?= View::e($baseUrl) ?>/admin/announcements">Open page</a></div>
        <?php foreach ($recentAnnouncements as $announcement): ?><div class="habit-preview-row"><i class="bi bi-megaphone"></i><div><strong><?= View::e($announcement["title"]) ?></strong><small><?= (int) $announcement["recipient_count"] ?> recipients · <?= View::e(date("d M Y", strtotime($announcement["created_at"]))) ?></small></div></div><?php endforeach; ?>
        <?php if (!$recentAnnouncements): ?><p class="text-muted mb-0">No announcements have been published yet.</p><?php endif; ?>
    </article>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="<?= View::e($baseUrl) ?>/assets/js/admin-dashboard-charts.js" defer></script>
