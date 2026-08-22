<?php use App\Core\View;

?>
<section class="page-heading">
    <div><p class="page-eyebrow">Administrator only</p><h1>Admin Audit Log</h1><p class="page-subtitle">Trace protected management actions for accountability.</p></div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> Back</a>
</section>
<section class="content-card p-0 overflow-hidden">
    <div class="card-section-heading p-3 mb-0"><div><p class="card-kicker">Latest 150 events</p><h2>Security and Management Activity</h2></div></div>
    <div class="table-responsive"><table class="table app-table mb-0"><thead><tr><th>When</th><th>Administrator</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr></thead><tbody>
        <?php foreach ($logs as $log): ?><tr><td><?= View::e(date("d M Y H:i", strtotime($log["created_at"]))) ?></td><td><?= View::e($log["admin_username"]) ?></td><td><span class="status-pill status-active"><?= View::e(ucwords(str_replace("_", " ", $log["action_name"]))) ?></span></td><td><?= View::e($log["target_username"] ?? "—") ?></td><td><?= View::e($log["details"] ?? "—") ?></td><td><?= View::e($log["ip_address"] ?? "—") ?></td></tr><?php endforeach; ?>
        <?php if (!$logs): ?><tr><td colspan="6" class="text-muted">No administrator actions have been recorded.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
