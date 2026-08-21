<?php if ($authenticated): ?>
        </main>
    </div>
</div>
<?php else: ?>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= \App\Core\View::e($baseUrl) ?>/assets/js/dashboard-polish.js" defer></script>
<script src="<?= \App\Core\View::e($baseUrl) ?>/assets/js/dashboard-activity-filters.js" defer></script>
<script src="<?= \App\Core\View::e($baseUrl) ?>/assets/js/money-polish.js" defer></script>
<script src="<?= \App\Core\View::e($baseUrl) ?>/assets/js/landing-nav-scroll.js" defer></script>
</body>
</html>
