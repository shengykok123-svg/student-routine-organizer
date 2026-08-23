<?php
use App\Core\Csrf;
use App\Core\View;

/** @var string $baseUrl */
/** @var array $habits */
/** @var string $search */
/** @var string $status */
/** @var array $heatmap */
/** @var int $longestStreak */
/** @var int $totalHabits */
/** @var int $totalCheckins */
?>

<!-- Calendar Specific Styles -->
<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 45px); 
    justify-content: center;
    gap: 6px;
    text-align: center;
    margin: 0 auto;
}
.cal-header {
    font-weight: bold;
    color: #6c757d;
    font-size: 0.75rem;
    padding-bottom: 8px;
    text-transform: uppercase;
}
.cal-day {
    aspect-ratio: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    font-weight: 500;
    font-size: 0.85rem;
    color: #495057;
    transition: all 0.2s;
    cursor: default;
}
.cal-day:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.cal-empty { background-color: transparent; border: none; }
.cal-lvl-1 { background-color: #cce5ff; color: #084298; border-color: #b6d4fe; }
.cal-lvl-2 { background-color: #66b2ff; color: #fff; border-color: #66b2ff; }
.cal-lvl-3 { background-color: #0d6efd; color: #fff; border-color: #0d6efd; }
.cal-lvl-4 { background-color: #0a58ca; color: #fff; border-color: #0a58ca; }
</style>

<section class="page-heading">
    <div>
        <p class="page-eyebrow">Routine consistency</p>
        <h1>Habit Tracker</h1>
        <p class="page-subtitle">Build small routines that add up every day.</p>
    </div>
    <a class="btn btn-primary" href="<?= View::e($baseUrl) ?>/habit/create">
        <i class="bi bi-plus-lg"></i> Add Habit
    </a>
</section>

<!-- Split Panel: Calendar (Left) & Stats (Right) -->
<section class="content-card mb-4 p-4 border-0 shadow-sm rounded-4">
    <div class="row align-items-center">
        <!-- Left Side: Calendar -->
        <div class="col-lg-7 mb-4 mb-lg-0 pe-lg-4" style="border-right: 1px solid var(--bs-border-color, #dee2e6);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Removed text-dark here -->
                <h5 class="fw-bold mb-0 d-flex align-items-center">
                    <i class="bi bi-calendar3 text-primary me-2 fs-4"></i> Monthly Consistency
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button id="prevMonth" class="btn btn-sm btn-outline-secondary shadow-sm"><i class="bi bi-chevron-left"></i></button>
                    <!-- Removed text-dark here -->
                    <span id="calendarMonthLabel" class="fw-bold fs-6" style="min-width: 120px; text-align: center;"></span>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary shadow-sm"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div id="calendarGrid" class="calendar-grid"></div>
        </div>

        <!-- Right Side: Performance Stats -->
        <div class="col-lg-5 ps-lg-4">
            <!-- Removed text-dark here -->
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-bar-chart-fill text-primary me-2 fs-4"></i> Overall Progress
            </h5>
            
            <!-- Replaced bg-light with a subtle border/padding so it adapts to dark mode better -->
            <div class="d-flex align-items-center mb-3 p-3 rounded-3 shadow-sm border">
                <div class="fs-1 text-danger me-3"><i class="bi bi-fire"></i></div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Longest Streak</div>
                    <!-- Removed text-dark here -->
                    <div class="fs-4 fw-bold"><?= (int) $longestStreak ?> Days</div>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3 p-3 rounded-3 shadow-sm border">
                <div class="fs-1 text-success me-3"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Total Check-Ins</div>
                    <!-- Removed text-dark here -->
                    <div class="fs-4 fw-bold"><?= (int) $totalCheckins ?> Logs</div>
                </div>
            </div>
            <div class="d-flex align-items-center p-3 rounded-3 shadow-sm border">
                <div class="fs-1 text-primary me-3"><i class="bi bi-ui-checks-grid"></i></div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Active Habits</div>
                    <!-- Removed text-dark here -->
                    <div class="fs-4 fw-bold"><?= (int) $totalHabits ?> Routines</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Panel -->
<section class="filter-panel mb-3">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label" for="search">Search habits</label>
            <input class="form-control" id="search" name="search" placeholder="Search habits..." value="<?= View::e($search) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">All statuses</option>
                <option <?= $status === "Active" ? "selected" : "" ?>>Active</option>
                <option <?= $status === "Completed" ? "selected" : "" ?>>Completed</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
        </div>
    </form>
</section>

<!-- Data Table -->
<section class="content-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table app-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Habit</th>
                    <th>Frequency</th>
                    <th>Streak</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($habits as $habit): ?>
                    <tr>
                        <td><strong><?= View::e($habit["habit_name"]) ?></strong></td>
                        <td><?= View::e($habit["frequency"]) ?></td>
                        <td><i class="bi bi-fire text-warning me-1"></i> <?= (int) $habit["streak"] ?> days</td>
                        <td>
                            <span class="status-pill <?= $habit["status"] === "Completed" ? "status-completed" : "status-active" ?>">
                                <?= View::e($habit["status"]) ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions justify-content-end">
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#check<?= (int) $habit["habit_id"] ?>">
                                    <i class="bi bi-check2"></i> Check In
                                </button>
                                <a class="btn btn-sm btn-outline-primary" href="<?= View::e($baseUrl) ?>/habit/edit?id=<?= (int) $habit["habit_id"] ?>" aria-label="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post" action="<?= View::e($baseUrl) ?>/habit/delete" onsubmit="return confirm('Delete this habit?')">
                                    <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
                                    <input type="hidden" name="habit_id" value="<?= (int) $habit["habit_id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (!$habits): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-bullseye"></i></div>
                                <h3>No habits found</h3>
                                <p>Create your first habit and check in every day.</p>
                                <a class="btn btn-primary" href="<?= View::e($baseUrl) ?>/habit/create">Add Habit</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modals -->
<?php foreach ($habits as $habit): ?>
    <div class="modal fade" id="check<?= (int) $habit["habit_id"] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="<?= View::e($baseUrl) ?>/habit/check-in">
                <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
                <input type="hidden" name="habit_id" value="<?= (int) $habit["habit_id"] ?>">
                
                <div class="modal-header">
                    <div>
                        <p class="card-kicker mb-1">Daily progress</p>
                        <h2 class="modal-title">Check In: <?= View::e($habit["habit_name"]) ?></h2>
                    </div>
                    <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                
                <div class="modal-body">
                    <label class="form-label">Sleep Quality</label>
                    <select class="form-select mb-3" name="sleep_quality">
                        <option>Poor</option><option>Fair</option><option>Good</option><option>Excellent</option>
                    </select>
                    
                    <label class="form-label">Diet Adherence</label>
                    <select class="form-select mb-3" name="diet_adherence">
                        <option>Poor</option><option>Fair</option><option>Good</option><option>Excellent</option>
                    </select>
                    
                    <label class="form-label">Stress Level</label>
                    <select class="form-select mb-3" name="stress_level">
                        <option>Low</option><option>Moderate</option><option>High</option><option>Severe</option>
                    </select>
                    
                    <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" name="notes" maxlength="255" rows="3"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Submit Check In</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<!-- JavaScript Engine -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const heatmapData = <?= json_encode($heatmap) ?>;
    let currentDate = new Date(); 

    const grid = document.getElementById('calendarGrid');
    const monthLabel = document.getElementById('calendarMonthLabel');
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    function renderCalendar() {
        if (!grid) return;
        grid.innerHTML = '';
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthLabel.textContent = `${monthNames[month]} ${year}`;

        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        days.forEach(day => {
            const el = document.createElement('div');
            el.className = 'cal-header';
            el.textContent = day;
            grid.appendChild(el);
        });

        const firstDay = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            const el = document.createElement('div');
            el.className = 'cal-empty';
            grid.appendChild(el);
        }

        for (let i = 1; i <= totalDays; i++) {
            const el = document.createElement('div');
            el.className = 'cal-day';
            el.textContent = i;

            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const count = heatmapData[dateStr] || 0;

            el.title = count === 0 ? `No activity on ${dateStr}` : `${count} habits completed`;

            if (count === 1) el.classList.add('cal-lvl-1');
            else if (count === 2) el.classList.add('cal-lvl-2');
            else if (count === 3) el.classList.add('cal-lvl-3');
            else if (count >= 4) el.classList.add('cal-lvl-4');

            grid.appendChild(el);
        }
    }

    const btnPrev = document.getElementById('prevMonth');
    const btnNext = document.getElementById('nextMonth');

    if(btnPrev) {
        btnPrev.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if(btnNext) {
        btnNext.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    renderCalendar();
});
</script>