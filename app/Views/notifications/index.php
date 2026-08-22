<?php use App\Core\Csrf;
use App\Core\View;

?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Account</p>
            <h1>Notifications</h1>
                <p class="page-subtitle">Your latest local account and routine updates.</p>
                </div>
                <?php if ($notifications): ?>
                <form method="post" action="<?= View::e(
                    $baseUrl,
                ) ?>/notifications/read">
                    <input type="hidden" name="_csrf" value="<?= View::e(
                        Csrf::token(),
                    ) ?>">
                    <input type="hidden" name="all" value="1">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-check2-all">
                        </i> Mark all read</button>
                    </form>
                    <?php endif; ?>
                </section>
                <section class="content-card p-0 overflow-hidden">
                    <?php if ($notifications): ?>
                    <ul class="activity-list px-4">
                        <?php foreach ($notifications as $notification): ?>
                        <li class="activity-item <?= empty(
                            $notification["read_at"]
                        )
                            ? "fw-semibold"
                            : "" ?>">
                            <span class="activity-symbol activity-<?= $notification[
                                "notification_type"
                            ] === "success"
                                ? "habit"
                                : "diary" ?>">
                                <i class="bi <?= $notification[
                                    "notification_type"
                                ] === "success"
                                    ? "bi-check2-circle"
                                    : "bi-bell" ?>">
                                </i>
                            </span>
                            <div class="activity-copy">
                                <strong>
                                    <?= View::e($notification["title"]) ?>
                                </strong>
                                <small>
                                    <?= View::e($notification["body"]) ?>
                                </small>
                                <?php if ($notification["link_url"]): ?>
                                <a class="small" href="<?= View::e(
                                    $baseUrl,
                                ) ?>/<?= View::e(
                                    $notification["link_url"],
                                ) ?>">Open</a>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="activity-date d-block mb-2">
                                        <?= View::e(
                                            date(
                                                "d M Y",
                                                strtotime(
                                                    $notification["created_at"],
                                                ),
                                            ),
                                        ) ?>
                                    </span>
                                    <?php if (
                                        empty($notification["read_at"])
                                    ): ?>
                                    <form method="post" action="<?= View::e(
                                        $baseUrl,
                                    ) ?>/notifications/read">
                                        <input type="hidden" name="_csrf" value="<?= View::e(
                                            Csrf::token(),
                                        ) ?>">
                                        <input type="hidden" name="notification_id" value="<?= (int) $notification[
                                            "notification_id"
                                        ] ?>">
                                        <button class="btn btn-sm btn-outline-primary">Read</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="bi bi-bell">
                                    </i>
                                </div>
                                <h3>No notifications</h3>
                                    <p>Local account updates will appear here.</p>
                                    </div>
                                    <?php endif; ?>
                                </section>
