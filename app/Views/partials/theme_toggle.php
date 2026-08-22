<?php
/** Shared Light/Dark quick-toggle; the Settings page keeps the full preference selector. */
$themeToggleClass = trim((string) ($themeToggleClass ?? ''));
?>
<button class="btn theme-toggle <?= App\Core\View::e($themeToggleClass) ?>" type="button" data-theme-toggle aria-label="Switch to dark theme" aria-pressed="false">
    <span class="theme-toggle-track" aria-hidden="true">
        <i class="bi bi-sun-fill theme-toggle-icon theme-toggle-icon-sun"></i>
        <i class="bi bi-moon-stars-fill theme-toggle-icon theme-toggle-icon-moon"></i>
        <span class="theme-toggle-thumb"></span>
    </span>
</button>
