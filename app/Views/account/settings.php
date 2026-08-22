<?php use App\Core\Csrf;
use App\Core\View;

?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Account</p>
            <h1>Settings</h1>
                <p class="page-subtitle">Control reminders and keep your account secure.</p>
                </div>
            </section>
            <?php foreach ($errors as $error): ?>
            <div class="app-flash flash-error">
                <i class="bi bi-exclamation-circle-fill">
                </i>
                <?= View::e($error) ?>
            </div>
            <?php endforeach; ?>
            <section class="form-panel">
                <form method="post" action="<?= View::e($baseUrl) ?>/settings">
                    <input type="hidden" name="_csrf" value="<?= View::e(
                        Csrf::token(),
                    ) ?>">
                    <p class="card-kicker">Notifications</p>
                        <div class="form-check form-check-card mb-3">
                            <input class="form-check-input" id="in_app_notifications" type="checkbox" name="in_app_notifications" <?= !empty(
                                $settings["in_app_notifications"]
                            )
                                ? "checked"
                                : "" ?>>
                            <label class="form-check-label" for="in_app_notifications">Enable in-app notifications</label>
                            </div>
                            <div class="form-check form-check-card mb-3">
                                <input class="form-check-input" id="email_notifications" type="checkbox" name="email_notifications" <?= !empty(
                                    $settings["email_notifications"]
                                )
                                    ? "checked"
                                    : "" ?>>
                                <label class="form-check-label" for="email_notifications">Email notifications <span class="text-muted">(requires online mail configuration)</span>
                                </label>
                            </div>
                                <div class="mb-4">
                                <label class="form-label" for="reminder_time">Preferred reminder time</label>
                                    <input class="form-control" id="reminder_time" type="time" name="reminder_time" value="<?= View::e(
                                        $settings["reminder_time"] ?? "",
                                    ) ?>">
                                </div>
                                <p class="card-kicker">Appearance</p>
                                <div class="theme-setting-options mb-4" role="radiogroup" aria-label="Theme preference">
                                    <?php foreach (["light" => ["Light", "bi-sun"], "dark" => ["Dark", "bi-moon-stars"], "system" => ["System", "bi-display"]] as $value => [$label, $icon]): ?>
                                        <label class="theme-setting-option">
                                            <input type="radio" name="theme_preference" value="<?= View::e($value) ?>" <?= ($settings["theme_preference"] ?? "system") === $value ? "checked" : "" ?>>
                                            <i class="bi <?= View::e($icon) ?>"></i>
                                            <span><?= View::e($label) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <hr class="my-4">
                                <p class="card-kicker">Change password</p>
                                    <div class="mb-3">
                                        <label class="form-label" for="current_password">Current password</label>
                                            <input class="form-control" id="current_password" type="password" name="current_password" autocomplete="current-password">
                                        </div>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="form-label" for="new_password">New password</label>
                                                    <input class="form-control" id="new_password" type="password" name="new_password" minlength="6" autocomplete="new-password">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label" for="confirm_password">Confirm new password</label>
                                                        <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="6" autocomplete="new-password">
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <button class="btn btn-primary">
                                                        <i class="bi bi-check2-circle">
                                                        </i> Save Settings</button>
                                                    </div>
                                                </form>
                                            </section>
