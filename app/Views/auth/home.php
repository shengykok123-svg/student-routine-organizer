<?php
use App\Core\View;

$assetBase = View::e($baseUrl) . "/assets/images";
$modules = [
    [
        "Exercise Tracker",
        "Track workouts, duration, calories burned, and progress towards your fitness goals.",
        "module-exercise-large.png",
        "landing-module-exercise",
    ],
    [
        "Diary Journal",
        "Write your thoughts, track moods, and keep a meaningful record of your journey.",
        "module-diary-large.png",
        "landing-module-diary",
    ],
    [
        "Money Tracker",
        "Track income and expenses, categorise transactions, and manage your budget wisely.",
        "module-money-large.png",
        "landing-module-money",
    ],
    [
        "Habit Tracker",
        "Build good habits, track your progress, and stay consistent every day.",
        "module-habit-large.png",
        "landing-module-habit",
    ],
];
$benefits = [
    [
        "bi-calendar2-week",
        "Stay Organized",
        "Keep everything in one place and never miss important things.",
    ],
    [
        "bi-rocket-takeoff",
        "Boost Productivity",
        "Track your activities and habits to build a more productive you.",
    ],
    [
        "bi-pie-chart",
        "Better Decisions",
        "Visual insights help you understand your progress and make better choices.",
    ],
    [
        "bi-trophy",
        "Achieve Goals",
        "Small daily actions lead to big achievements over time.",
    ],
];
?>
<section class="landing-page">
    <nav class="landing-nav" aria-label="Guest navigation">
        <a class="landing-brand" href="<?= View::e(
            $baseUrl,
        ) ?>/" aria-label="Student Routine Organizer home">
            <img class="landing-logo" src="<?= $assetBase ?>/branding/logo-app-64.png" alt="">
            <span>
                <strong>SRO</strong>
                    <small>Student Routine Organizer</small>
                    </span>
                </a>
                <div class="landing-nav-links">
                    <a class="is-active" data-landing-nav-link href="#home" aria-current="page">
                        <i class="bi bi-house-door">
                        </i> Home</a>
                        <a data-landing-nav-link href="#features">
                            <i class="bi bi-stars">
                            </i> Features</a>
                            <a data-landing-nav-link href="#about">
                                <i class="bi bi-info-circle">
                                </i> About</a>
                            </div>
                            <div class="landing-nav-actions">
                                <?php $themeToggleClass = "landing-theme-toggle"; require __DIR__ . "/../partials/theme_toggle.php"; ?>
                                <a class="btn btn-outline-light" href="<?= View::e(
                                    $baseUrl,
                                ) ?>/login">Login</a>
                                    <a class="btn btn-primary" href="<?= View::e(
                                        $baseUrl,
                                    ) ?>/register">Register</a>
                                    </div>
                                </nav>
                                <section class="landing-hero-band" id="home">
                                    <div class="landing-container landing-hero">
                                        <div class="landing-copy">
                                            <h1>Organize Your <em>Routine.</em>
                                                <br>Improve Your <em>Life.</em>
                                            </h1>
                                            <p>Student Routine Organizer helps you manage exercises, diary reflections, finances, and habits — all in one place.</p>
                                                <div class="landing-cta">
                                                    <a class="btn btn-primary btn-lg" href="<?= View::e(
                                                        $baseUrl,
                                                    ) ?>/register">
                                                        <i class="bi bi-person-plus">
                                                        </i> Get Started</a>
                                                        <a class="btn btn-outline-light btn-lg" href="<?= View::e(
                                                            $baseUrl,
                                                        ) ?>/login">
                                                            <i class="bi bi-box-arrow-in-right">
                                                            </i> Login Now</a>
                                                        </div>
                                                        <ul class="landing-trust-list" aria-label="Platform benefits">
                                                            <li>
                                                                <i class="bi bi-shield-check">
                                                                </i>
                                                                <span>
                                                                    <strong>Secure &amp; Private</strong>
                                                                        <small>Your data is safe</small>
                                                                        </span>
                                                                    </li>
                                                                    <li>
                                                                        <i class="bi bi-layers">
                                                                        </i>
                                                                        <span>
                                                                            <strong>All-in-One</strong>
                                                                                <small>4 modules in one app</small>
                                                                                </span>
                                                                            </li>
                                                                            <li>
                                                                                <i class="bi bi-lightning-charge">
                                                                                </i>
                                                                                <span>
                                                                                    <strong>Easy to Use</strong>
                                                                                        <small>Simple and intuitive</small>
                                                                                        </span>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                            <div class="landing-hero-art">
                                                                                <img class="landing-hero-illustration" src="<?= $assetBase ?>/illustrations/illustration-landing-hero.png" alt="Student using the exercise, diary, money, and habit modules">
                                                                            </div>
                                                                        </div>
                                                                    </section>
                                                                    <section class="landing-features" id="features">
                                                                        <div class="landing-container">
                                                                            <div class="landing-section-heading">
                                                                                <h2>Everything You Need, All in One Place</h2>
                                                                                    <p>Four powerful modules to help you build a better daily routine.</p>
                                                                                    </div>
                                                                                    <div class="landing-module-grid">
                                                                                        <?php foreach (
                                                                                            $modules as [
                                                                                                $name,
                                                                                                $description,
                                                                                                $image,
                                                                                                $class,
                                                                                            ]
                                                                                        ): ?>
                                                                                        <article class="landing-module-card <?= $class ?>">
                                                                                            <img src="<?= $assetBase ?>/modules/<?= View::e(
                                                                                                $image,
                                                                                            ) ?>" alt="">
                                                                                            <div>
                                                                                                <h3>
                                                                                                    <?= View::e(
                                                                                                        $name,
                                                                                                    ) ?>
                                                                                                </h3>
                                                                                                <p>
                                                                                                    <?= View::e(
                                                                                                        $description,
                                                                                                    ) ?>
                                                                                                </p>
                                                                                                <a href="#about">Learn more <i class="bi bi-arrow-right">
                                                                                                </i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </article>
                                                                                    <?php endforeach; ?>
                                                                                </div>
                                                                            </div>
                                                                        </section>
                                                                        <section class="landing-benefits" id="about">
                                                                            <div class="landing-container landing-benefits-layout">
                                                                                <div>
                                                                                    <div class="landing-section-heading landing-section-heading-left">
                                                                                        <h2>Why Students Love SRO</h2>
                                                                                            <p>One calm place to keep the important parts of student life moving forward.</p>
                                                                                            </div>
                                                                                            <div class="landing-benefit-grid">
                                                                                                <?php foreach (
                                                                                                    $benefits as [
                                                                                                        $icon,
                                                                                                        $title,
                                                                                                        $description,
                                                                                                    ]
                                                                                                ): ?>
                                                                                                <article>
                                                                                                    <i class="bi <?= View::e(
                                                                                                        $icon,
                                                                                                    ) ?>">
                                                                                                    </i>
                                                                                                    <div>
                                                                                                        <h3>
                                                                                                            <?= View::e(
                                                                                                                $title,
                                                                                                            ) ?>
                                                                                                        </h3>
                                                                                                        <p>
                                                                                                            <?= View::e(
                                                                                                                $description,
                                                                                                            ) ?>
                                                                                                        </p>
                                                                                                    </div>
                                                                                                </article>
                                                                                                <?php endforeach; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                        <aside class="landing-dashboard-preview" aria-label="Illustrative system dashboard preview">
                                                                                            <div class="preview-topbar">
                                                                                                <div class="preview-brand">
                                                                                                    <img src="<?= $assetBase ?>/branding/logo-app-64.png" alt="">
                                                                                                    <span>Dashboard</span>
                                                                                                    </div>
                                                                                                    <span class="preview-avatar" aria-label="Student profile picture">
                                                                                                        <i class="bi bi-person-fill">
                                                                                                        </i>
                                                                                                    </span>
                                                                                                </div>
                                                                                                <div class="preview-body">
                                                                                                    <aside class="preview-sidebar" aria-label="Dashboard navigation">
                                                                                                        <i class="bi bi-grid-1x2-fill is-active">
                                                                                                        </i>
                                                                                                        <i class="bi bi-activity">
                                                                                                        </i>
                                                                                                        <i class="bi bi-journal-bookmark">
                                                                                                        </i>
                                                                                                        <i class="bi bi-cash-stack">
                                                                                                        </i>
                                                                                                        <i class="bi bi-bullseye">
                                                                                                        </i>
                                                                                                    </aside>
                                                                                                    <div class="preview-content">
                                                                                                        <div class="preview-content-heading">
                                                                                                            <div>
                                                                                                                <p>STUDENT OVERVIEW</p>
                                                                                                                    <strong>My Dashboard</strong>
                                                                                                                    </div>
                                                                                                                    <i class="bi bi-bell">
                                                                                                                    </i>
                                                                                                                </div>
                                                                                                                <div class="preview-stats">
                                                                                                                    <span>
                                                                                                                        <b>10</b>
                                                                                                                            <small>Exercises</small>
                                                                                                                            </span>
                                                                                                                            <span>
                                                                                                                                <b>8</b>
                                                                                                                                    <small>Diary entries</small>
                                                                                                                                    </span>
                                                                                                                                    <span>
                                                                                                                                        <b>RM 262</b>
                                                                                                                                            <small>Expenses</small>
                                                                                                                                            </span>
                                                                                                                                        </div>
                                                                                                                                        <div class="preview-chart">
                                                                                                                                            <div class="preview-chart-label">
                                                                                                                                                <span>Activity overview</span>
                                                                                                                                                    <i class="bi bi-graph-up-arrow">
                                                                                                                                                    </i>
                                                                                                                                                </div>
                                                                                                                                                <svg viewBox="0 0 250 64" role="img" aria-label="Example activity trend">
                                                                                                                                                    <path d="M4 53 C32 43 41 46 67 33 S110 42 132 21 S170 18 191 33 S220 28 246 9" fill="none" stroke="#7657ec" stroke-width="4" stroke-linecap="round"/>
                                                                                                                                                    <path d="M4 58 H246" stroke="#dfe3f1" stroke-width="2"/>
                                                                                                                                                </svg>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </aside>
                                                                                                                            </div>
                                                                                                                        </section>
                                                                                                                        <footer class="landing-footer">
                                                                                                                            <div class="landing-container">
                                                                                                                                <div class="landing-footer-grid">
                                                                                                                                    <div class="landing-footer-about">
                                                                                                                                        <a class="landing-brand" href="<?= View::e(
                                                                                                                                            $baseUrl,
                                                                                                                                        ) ?>/">
                                                                                                                                            <img class="landing-logo" src="<?= $assetBase ?>/branding/logo-app-64.png" alt="">
                                                                                                                                            <span>
                                                                                                                                                <strong>SRO</strong>
                                                                                                                                                    <small>Student Routine Organizer</small>
                                                                                                                                                    </span>
                                                                                                                                                </a>
                                                                                                                                                <p>A free student companion designed to help you build a productive, organised routine.</p>
                                                                                                                                                </div>
                                                                                                                                                <div>
                                                                                                                                                    <h3>Quick Links</h3>
                                                                                                                                                        <a href="#home">Home</a>
                                                                                                                                                            <a href="#features">Features</a>
                                                                                                                                                                <a href="#about">About Us</a>
                                                                                                                                                                </div>
                                                                                                                                                                <div>
                                                                                                                                                                    <h3>Modules</h3>
                                                                                                                                                                        <a href="#features">Exercise Tracker</a>
                                                                                                                                                                            <a href="#features">Diary Journal</a>
                                                                                                                                                                                <a href="#features">Money Tracker</a>
                                                                                                                                                                                    <a href="#features">Habit Tracker</a>
                                                                                                                                                                                    </div>
                                                                                                                                                                                    <div>
                                                                                                                                                                                        <h3>Support</h3>
                                                                                                                                                                                            <a href="<?= View::e(
                                                                                                                                                                                                $baseUrl,
                                                                                                                                                                                            ) ?>/login">Login</a>
                                                                                                                                                                                                <a href="<?= View::e(
                                                                                                                                                                                                    $baseUrl,
                                                                                                                                                                                                ) ?>/register">Create account</a>
                                                                                                                                                                                                    <a href="#about">Privacy &amp; security</a>
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                    <div>
                                                                                                                                                                                                        <h3>Stay Connected</h3>
                                                                                                                                                                                                            <p>Start building a better routine today.</p>
                                                                                                                                                                                                                <div class="landing-socials">
                                                                                                                                                                                                                    <a href="#home" aria-label="Facebook">
                                                                                                                                                                                                                        <i class="bi bi-facebook">
                                                                                                                                                                                                                        </i>
                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                    <a href="#home" aria-label="Instagram">
                                                                                                                                                                                                                        <i class="bi bi-instagram">
                                                                                                                                                                                                                        </i>
                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                    <a href="#home" aria-label="X">
                                                                                                                                                                                                                        <i class="bi bi-twitter-x">
                                                                                                                                                                                                                        </i>
                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                    <a href="mailto:" aria-label="Email">
                                                                                                                                                                                                                        <i class="bi bi-envelope-fill">
                                                                                                                                                                                                                        </i>
                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                        <p class="landing-copyright">© <?= date(
                                                                                                                                                                                                            "Y",
                                                                                                                                                                                                        ) ?> Student Routine Organizer. All rights reserved.</p>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    </footer>
                                                                                                                                                                                                </section>
