<?php

use App\Core\View;

$stats = [
    ["Accounts", $summary["users"], "bi-people"],
    ["Active", $summary["active_users"], "bi-person-check"],
    ["Suspended", $summary["suspended_users"], "bi-person-slash"],
    ["Student records", $summary["exercises"] + $summary["diary_entries"] + $summary["money_records"] + $summary["habits"], "bi-database"],
];
$tools = [
    ["Admin Dashboard", "View aggregate usage and system activity.", "bi-grid-1x2", "admin/dashboard"],
    ["User Management", "Search, update, suspend, or manage registered accounts.", "bi-people", "admin/users"],
    ["Announcements", "Send a system message to active accounts.", "bi-megaphone", "admin/announcements"],
    ["Audit Log", "Review protected administrator actions.", "bi-shield-check", "admin/audit"],
    ["Data Maintenance", "Export summary data and clean orphan uploads.", "bi-database-gear", "admin/maintenance"],
];
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Administrator only</p>
        <h1>System Administration</h1>
        <p class="page-subtitle">A concise overview of the system, with direct access to each management area.</p>
    </div>
</section>

<section class="admin-summary mb-4">
    <?php foreach ($stats as [$label, $value, $icon]): ?>
        <article class="content-card admin-stat"><i class="bi <?= $icon ?> text-primary fs-5"></i><strong><?= (int) $value ?></strong><span><?= View::e($label) ?></span></article>
    <?php endforeach; ?>
</section>

<section class="content-card">
    <div class="card-section-heading"><div><p class="card-kicker">Management areas</p><h2>Open a Detailed Page</h2></div></div>
    <div class="row g-3">
        <?php foreach ($tools as [$label, $description, $icon, $path]): ?>
            <div class="col-md-6 col-xl-4">
                <a class="content-card h-100 d-block text-decoration-none" href="<?= View::e($baseUrl) ?>/<?= View::e($path) ?>">
                    <i class="bi <?= View::e($icon) ?> text-primary fs-4"></i>
                    <h3 class="h5 mt-3 mb-2"><?= View::e($label) ?></h3>
                    <p class="text-muted mb-0"><?= View::e($description) ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
