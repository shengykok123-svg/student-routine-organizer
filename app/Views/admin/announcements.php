<?php

use App\Core\Csrf;
use App\Core\View;

?>
<section class="page-heading">
    <div><p class="page-eyebrow">Administrator only</p><h1>Announcement Centre</h1><p class="page-subtitle">Publish an in-app message to active users without creating student records.</p></div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/admin"><i class="bi bi-arrow-left"></i> Back</a>
</section>
<?php foreach ($errors as $error): ?><div class="app-flash flash-error"><i class="bi bi-exclamation-circle-fill"></i> <?= View::e($error) ?></div><?php endforeach; ?>
<section class="dashboard-grid">
    <article class="form-panel form-panel-wide">
        <div class="card-section-heading"><div><p class="card-kicker">New announcement</p><h2>Send an in-app message</h2></div></div>
        <form method="post" action="<?= View::e($baseUrl) ?>/admin/announcements">
            <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
            <div class="row g-3">
                <div class="col-12"><label class="form-label" for="title">Title</label><input class="form-control" id="title" name="title" maxlength="150" required></div>
                <div class="col-md-5"><label class="form-label" for="audience">Audience</label><select class="form-select" id="audience" name="audience"><option value="all">All active accounts</option><option value="students">Students only</option><option value="admins">Administrators only</option></select></div>
                <div class="col-12"><label class="form-label" for="body">Message</label><textarea class="form-control" id="body" name="body" rows="6" maxlength="500" required></textarea><div class="form-text">Delivered as an in-app notification. No email is sent.</div></div>
            </div>
            <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-send"></i> Publish Announcement</button></div>
        </form>
    </article>
    <article class="content-card">
        <p class="card-kicker">Guideline</p><h2>Use announcements for system communication</h2><p class="text-muted">Examples: planned maintenance, new features, and campus-wide reminders. Student exercise, diary, money, and habit records are not created or edited here.</p>
    </article>
</section>
<section class="content-card mt-4 p-0 overflow-hidden">
    <div class="card-section-heading p-3 mb-0"><div><p class="card-kicker">Published messages</p><h2>Announcement History</h2></div></div>
    <div class="table-responsive"><table class="table app-table mb-0"><thead><tr><th>Title</th><th>Audience</th><th>Recipients</th><th>Published by</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($announcements as $announcement): ?><tr><td><strong><?= View::e($announcement["title"]) ?></strong><small class="d-block text-muted"><?= View::e($announcement["body"]) ?></small></td><td><?= View::e(ucfirst($announcement["audience"])) ?></td><td><?= (int) $announcement["recipient_count"] ?></td><td><?= View::e($announcement["author_username"]) ?></td><td><?= View::e(date("d M Y H:i", strtotime($announcement["created_at"]))) ?></td></tr><?php endforeach; ?>
        <?php if (!$announcements): ?><tr><td colspan="5" class="text-muted">No announcements have been published.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
