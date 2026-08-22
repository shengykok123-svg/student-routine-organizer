<?php

use App\Core\Csrf;
use App\Core\View;

?>
<section class="page-heading">
    <div><p class="page-eyebrow">Administrator only</p><h1>Data Maintenance</h1><p class="page-subtitle">Review system data health and safely maintain application uploads.</p></div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> Back</a>
</section>
<section class="admin-summary mb-4">
    <?php foreach (["users" => "Accounts", "exercises" => "Exercises", "diary_entries" => "Diary Entries", "money_records" => "Money Records", "habits" => "Habits"] as $key => $label): ?><article class="content-card admin-stat"><strong><?= (int) $summary[$key] ?></strong><span><?= View::e($label) ?></span></article><?php endforeach; ?>
</section>
<section class="dashboard-grid">
    <article class="content-card">
        <p class="card-kicker">Export</p><h2>System Summary</h2><p class="text-muted">Download non-personal aggregate counts as a CSV file.</p><a class="btn btn-outline-primary" href="<?= View::e($baseUrl) ?>/admin/maintenance/export"><i class="bi bi-download"></i> Export CSV</a>
    </article>
    <article class="content-card">
        <p class="card-kicker">Storage</p><h2>Upload Health</h2>
        <?php foreach ($storage as $label => $item): ?><div class="habit-preview-row"><i class="bi bi-folder2-open"></i><div><strong><?= View::e($label) ?></strong><small><?= (int) $item["files"] ?> files · <?= View::e(number_format($item["bytes"] / 1024 / 1024, 2)) ?> MB</small></div></div><?php endforeach; ?>
    </article>
</section>
<section class="content-card mt-4">
    <p class="card-kicker">Safe cleanup</p><h2>Orphan Uploads</h2><p class="text-muted">This removes only files older than 24 hours that are no longer referenced by a profile, diary entry, or money record. It does not delete any live student record.</p>
    <form method="post" action="<?= View::e($baseUrl) ?>/admin/maintenance/clean-uploads" onsubmit="return confirm('Remove identified orphan upload files?')"><input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><button class="btn btn-outline-danger"><i class="bi bi-trash3"></i> Clean Orphan Uploads</button></form>
</section>
