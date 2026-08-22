<?php

use App\Core\View;

$stats = [
    ["Exercises", $summary["exercises"], "bi-activity"],
    ["Diary Entries", $summary["diary_entries"], "bi-journal-bookmark"],
    ["Money Records", $summary["money_records"], "bi-cash-stack"],
    ["Habits", $summary["habits"], "bi-bullseye"],
];
?>
<section class="page-heading">
    <div><p class="page-eyebrow">Administrator only</p><h1>Admin Dashboard</h1><p class="page-subtitle">Aggregate system activity only. Student records cannot be added or edited here.</p></div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> System Administration</a>
</section>
<section class="admin-summary mb-4">
    <?php foreach ($stats as [$label, $value, $icon]): ?><article class="content-card admin-stat"><i class="bi <?= $icon ?> text-primary fs-5"></i><strong><?= (int) $value ?></strong><span><?= View::e($label) ?></span></article><?php endforeach; ?>
</section>
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
