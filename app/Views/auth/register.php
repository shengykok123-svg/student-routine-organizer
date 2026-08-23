<?php use App\Core\Csrf;
use App\Core\View;

$assetBase = View::e($baseUrl) . "/assets/images";
?>
<div class="auth-theme-control">
    <?php $themeToggleClass = "auth-theme-toggle"; require __DIR__ . "/../partials/theme_toggle.php"; ?>
</div>
<section class="auth-register-layout">
    <aside class="register-visual">
        <div class="register-visual-copy">
            <p class="auth-kicker">Your routine starts here</p>
                <h2>Build a better day,<br>one habit at a time.</h2>
                    <p>Create your account to bring your exercise, reflections, money and goals into one organised space.</p>
                    </div>
                    <div class="register-illustration-frame">
                        <img class="register-illustration" src="<?= $assetBase ?>/illustrations/illustration-register-welcome.png" alt="Students creating a daily routine">
                    </div>
                    <p class="register-visual-caption">Start small. Stay consistent. Celebrate your progress.</p>
                    </aside>
                    <section class="auth-register-card">
                        <div class="auth-brand">
                            <img class="auth-brand-logo" src="<?= $assetBase ?>/branding/logo-app-64.png" alt=""> STUDENT ROUTINE ORGANIZER</div>
                            <h1>Create Your Account</h1>
                                <p class="subtitle">Join now and start organising your routine.</p>
                                    <form method="post" action="<?= View::e(
                                        $baseUrl,
                                    ) ?>/register">
                                        <input type="hidden" name="_csrf" value="<?= View::e(
                                            Csrf::token(),
                                        ) ?>">
                                        <div class="mb-3">
                                            <label class="form-label" for="full_name">Full Name</label>
                                                <input class="form-control" id="full_name" name="full_name" required autocomplete="name" placeholder="Enter your full name">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="username">Username</label>
                                                    <input class="form-control" id="username" name="username" required autocomplete="username" placeholder="Choose a username">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Email</label>
                                                        <input class="form-control" id="email" type="email" name="email" required autocomplete="email" placeholder="Enter your email address">
                                                    </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label" for="password">Password</label>
                                                                        <input class="form-control" id="password" type="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Create a password">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="confirm_password">Confirm Password</label>
                                                                            <input class="form-control" id="confirm_password" type="password" name="confirm_password" required minlength="6" autocomplete="new-password" placeholder="Confirm your password">
                                                                        </div>
                                                                        <div class="form-check mb-4">
                                                                            <input class="form-check-input" id="accept_terms" type="checkbox" name="accept_terms" value="1">
                                                                            <label class="form-check-label small" for="accept_terms">I agree to the Terms &amp; Conditions.</label>
                                                                            </div>
                                                                            <button class="btn btn-primary w-100" type="submit">
                                                                                <i class="bi bi-person-plus">
                                                                                </i> Register</button>
                                                                            </form>
                                                                            <p class="auth-footer-note">Already have an account? <a href="<?= View::e(
                                                                                $baseUrl,
                                                                            ) ?>/login">Login here</a>
                                                                            </p>
                                                                        </section>
                                                                    </section>
