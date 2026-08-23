<?php use App\Core\View;

$canViewAllAuditLogs = $canViewAllAuditLogs ?? false;
$filters = $filters ?? ["search" => "", "action" => "", "admin_id" => null, "start_date" => "", "end_date" => ""];
$actions = $actions ?? [];
$administrators = $administrators ?? [];
$exportParams = array_filter($filters, static fn (mixed $value): bool => $value !== "" && $value !== null);
$exportUrl = View::e($baseUrl) . "/admin/audit/export" . ($exportParams ? "?" . http_build_query($exportParams) : "");
$formatAction = static fn (string $action): string => ucwords(str_replace("_", " ", $action));
?>
<section class="page-heading">
    <div><p class="page-eyebrow"><?= $canViewAllAuditLogs ? "Super Admin" : "Administrator" ?></p><h1>Admin Audit Log</h1><p class="page-subtitle"><?= $canViewAllAuditLogs ? "Review all Super Admin and Administrator actions." : "Review your protected management actions." ?></p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?= $exportUrl ?>"><i class="bi bi-download"></i> Export CSV</a><a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> Back</a></div>
</section>
<section class="filter-panel mb-4">
    <form class="row g-3 align-items-end" method="get" action="<?= View::e($baseUrl) ?>/admin/audit">
        <div class="col-md-4">
            <label class="form-label" for="audit-search">Search</label>
            <input class="form-control" id="audit-search" name="search" value="<?= View::e($filters["search"]) ?>" placeholder="Admin, target, action, details, or IP">
        </div>
        <?php if ($canViewAllAuditLogs): ?>
            <div class="col-md-2">
                <label class="form-label" for="audit-admin">Administrator</label>
                <select class="form-select" id="audit-admin" name="admin_id"><option value="">All administrators</option><?php foreach ($administrators as $administrator): ?><option value="<?= (int) $administrator["user_id"] ?>" <?= $filters["admin_id"] === (int) $administrator["user_id"] ? "selected" : "" ?>><?= View::e($administrator["username"]) ?></option><?php endforeach; ?></select>
            </div>
        <?php endif; ?>
        <div class="col-md-2">
            <label class="form-label" for="audit-action">Action</label>
            <select class="form-select" id="audit-action" name="action"><option value="">All actions</option><?php foreach ($actions as $action): ?><option value="<?= View::e($action) ?>" <?= $filters["action"] === $action ? "selected" : "" ?>><?= View::e($formatAction($action)) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="audit-start-date">From</label>
            <input class="form-control" id="audit-start-date" type="date" name="start_date" value="<?= View::e($filters["start_date"]) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="audit-end-date">To</label>
            <input class="form-control" id="audit-end-date" type="date" name="end_date" value="<?= View::e($filters["end_date"]) ?>">
        </div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-funnel"></i> Apply Filters</button><a class="btn btn-link" href="<?= View::e($baseUrl) ?>/admin/audit">Clear</a></div>
    </form>
</section>
<section class="content-card p-0 overflow-hidden">
    <div class="card-section-heading p-3 mb-0"><div><p class="card-kicker"><?= $canViewAllAuditLogs ? "Latest 150 matching events across all administrators" : "Your latest 150 matching events" ?></p><h2>Security and Management Activity</h2></div><span class="record-count"><?= count($logs) ?> shown</span></div>
    <div class="table-responsive"><table class="table app-table mb-0"><thead><tr><th>When</th><th>Administrator</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr></thead><tbody>
        <?php foreach ($logs as $log): ?><tr><td><?= View::e(date("d M Y H:i", strtotime($log["created_at"]))) ?></td><td><?= View::e($log["admin_username"]) ?></td><td><span class="status-pill status-active"><?= View::e($formatAction($log["action_name"])) ?></span></td><td><?= View::e($log["target_username"] ?? "—") ?></td><td><?= View::e($log["details"] ?? "—") ?></td><td><?= View::e($log["ip_address"] ?? "—") ?></td></tr><?php endforeach; ?>
        <?php if (!$logs): ?><tr><td colspan="6" class="text-muted">No administrator actions have been recorded.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
