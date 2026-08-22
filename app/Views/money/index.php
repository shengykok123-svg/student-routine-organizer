<?php

use App\Core\Csrf;
use App\Core\View;

$balance = (float) $totals["income"] - (float) $totals["expense"];
$exportQuery = http_build_query([
    "search" => $search,
    "range" => $range,
    "type" => $type,
    "category" => $category,
    "start_date" => $customStart,
    "end_date" => $customEnd,
]);
$expenseSummary = array_values(array_filter(
    $categorySummary,
    static fn (array $row): bool => (float) $row["expense"] > 0,
));
usort($expenseSummary, static fn (array $left, array $right): int => (float) $right["expense"] <=> (float) $left["expense"]);
$chartData = [
    "category" => [
        "labels" => array_column($categorySummary, "category"),
        "income" => array_map(static fn (array $row): float => (float) $row["income"], $categorySummary),
        "expense" => array_map(static fn (array $row): float => (float) $row["expense"], $categorySummary),
        "expenseLabels" => array_column($expenseSummary, "category"),
        "expenseValues" => array_map(static fn (array $row): float => (float) $row["expense"], $expenseSummary),
    ],
    "cashFlow" => $cashFlow,
];
$hasCategoryActivity = array_sum($chartData["category"]["income"]) + array_sum($chartData["category"]["expense"]) > 0;
$hasCashFlow = $cashFlow["labels"] !== [];
$remainingBudgetLabel = $budgetOverview["remaining"] < 0 ? "Over budget" : "Remaining";
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Personal finance</p>
        <h1>Money Tracker</h1>
        <p class="page-subtitle">Track spending, keep to your plan, and understand your cash flow.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money/export?<?= View::e($exportQuery) ?>">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a class="btn btn-primary" href="<?= View::e($baseUrl) ?>/money/create">
            <i class="bi bi-plus-lg"></i> Add Transaction
        </a>
    </div>
</section>

<?php if ($filterError): ?>
    <div class="app-flash flash-error"><i class="bi bi-exclamation-circle-fill"></i><?= View::e($filterError) ?></div>
<?php endif; ?>

<section class="stats-grid stats-grid-3">
    <?php foreach ([
        ["Total Income", $totals["income"], "stat-card-success", "bi-arrow-down-left-circle"],
        ["Total Expenses", $totals["expense"], "stat-card-danger", "bi-arrow-up-right-circle"],
        ["Current Balance", $balance, "money-balance", "bi-wallet2"],
    ] as [$label, $value, $class, $icon]): ?>
        <article class="stat-card <?= $class ?>">
            <div>
                <p><?= View::e($label) ?></p>
                <h2>RM <?= number_format((float) $value, 2) ?></h2>
                <small class="text-muted"><?= View::e($periodLabel) ?></small>
            </div>
            <span class="stat-icon"><i class="bi <?= $icon ?>"></i></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="content-card money-budget-card mb-3">
    <div class="card-section-heading">
        <div>
            <p class="card-kicker">Current month plan · <?= View::e($budgetOverview["month_label"]) ?></p>
            <h2>Budget & Safe to Spend</h2>
        </div>
        <span class="budget-days"><i class="bi bi-calendar3"></i> <?= (int) $budgetOverview["days_remaining"] ?> days remaining</span>
    </div>

    <div class="money-budget-summary">
        <div><span>Budgeted</span><strong>RM <?= number_format((float) $budgetOverview["limit_total"], 2) ?></strong></div>
        <div><span>Spent</span><strong>RM <?= number_format((float) $budgetOverview["spent_total"], 2) ?></strong></div>
        <div><span><?= View::e($remainingBudgetLabel) ?></span><strong class="<?= $budgetOverview["remaining"] < 0 ? "text-danger" : "text-success" ?>">RM <?= number_format((float) abs($budgetOverview["remaining"]), 2) ?></strong></div>
        <div><span>Safe to spend today</span><strong>RM <?= number_format((float) $budgetOverview["safe_daily"], 2) ?></strong></div>
    </div>

    <?php if ($budgetOverview["items"]): ?>
        <div class="budget-progress-list">
            <?php foreach ($budgetOverview["items"] as $budget): ?>
                <?php $budgetState = $budget["percentage"] >= 100 ? "is-over" : ($budget["percentage"] >= 80 ? "is-warning" : "is-healthy"); ?>
                <article class="budget-progress-item <?= $budgetState ?>">
                    <div class="budget-progress-copy">
                        <strong><?= View::e($budget["category"]) ?></strong>
                        <span>RM <?= number_format((float) $budget["spent"], 2) ?> of RM <?= number_format((float) $budget["limit"], 2) ?></span>
                    </div>
                    <div class="budget-progress-track" aria-label="<?= View::e($budget["category"]) ?> budget progress">
                        <span style="width: <?= min(100, max(0, (float) $budget["percentage"])) ?>%"></span>
                    </div>
                    <div class="budget-progress-meta">
                        <span><?= number_format((float) $budget["percentage"], 0) ?>%</span>
                        <form method="post" action="<?= View::e($baseUrl) ?>/money/budget/delete" onsubmit="return confirm('Remove this monthly budget?')">
                            <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
                            <input type="hidden" name="category" value="<?= View::e($budget["category"]) ?>">
                            <button class="btn btn-link btn-sm text-danger p-0" type="submit">Remove</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted mb-0">Set an expense budget to see progress and a daily spending guide.</p>
    <?php endif; ?>

    <form class="budget-form" method="post" action="<?= View::e($baseUrl) ?>/money/budget/save">
        <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
        <div>
            <label class="form-label" for="budget-category">Expense category</label>
            <select class="form-select" id="budget-category" name="category">
                <?php foreach ($expenseCategories as $expenseCategory): ?>
                    <option value="<?= View::e($expenseCategory) ?>"><?= View::e($expenseCategory) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="monthly-limit">Monthly limit</label>
            <div class="input-group">
                <span class="input-group-text">RM</span>
                <input class="form-control" id="monthly-limit" type="number" min="0.01" max="99999999" step="0.01" name="monthly_limit" placeholder="e.g. 250.00" required>
            </div>
        </div>
        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-piggy-bank"></i> Save Budget</button>
    </form>
</section>

<section class="filter-panel mb-3">
    <div class="money-quick-filters" aria-label="Quick transaction filters">
        <button class="<?= $type === "" ? "is-active" : "" ?>" type="button" data-money-type="">All</button>
        <button class="<?= $type === "Income" ? "is-active" : "" ?>" type="button" data-money-type="Income">Income</button>
        <button class="<?= $type === "Expense" ? "is-active" : "" ?>" type="button" data-money-type="Expense">Expenses</button>
        <button class="<?= $range === "month" ? "is-active" : "" ?>" type="button" data-money-range="month">This Month</button>
        <button class="<?= $range === "quarter" ? "is-active" : "" ?>" type="button" data-money-range="quarter">3 Months</button>
        <a href="<?= View::e($baseUrl) ?>/money">Reset</a>
    </div>
    <form method="get" id="moneyFilter" class="row g-3 align-items-end">
        <div class="col-lg-3">
            <label class="form-label" for="search">Search</label>
            <input class="form-control" id="search" name="search" value="<?= View::e($search) ?>" placeholder="Description or category">
        </div>
        <div class="col-lg-3">
            <label class="form-label" for="range">Period</label>
            <select class="form-select" id="range" name="range">
                <option value="month" <?= $range === "month" ? "selected" : "" ?>>Recent 1 Month</option>
                <option value="quarter" <?= $range === "quarter" ? "selected" : "" ?>>Recent 3 Months</option>
                <option value="all" <?= $range === "all" ? "selected" : "" ?>>All Time</option>
                <option value="custom" <?= $range === "custom" ? "selected" : "" ?>>Custom Date Range</option>
            </select>
        </div>
        <div class="col-lg-3">
            <label class="form-label" for="type">Type</label>
            <select class="form-select" id="type" name="type">
                <option value="">All types</option>
                <option value="Income" <?= $type === "Income" ? "selected" : "" ?>>Income</option>
                <option value="Expense" <?= $type === "Expense" ? "selected" : "" ?>>Expenses</option>
            </select>
        </div>
        <div class="col-lg-3">
            <label class="form-label" for="category">Category</label>
            <select class="form-select" id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $filterCategory): ?>
                    <option value="<?= View::e($filterCategory) ?>" <?= $category === $filterCategory ? "selected" : "" ?>><?= View::e($filterCategory) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 row g-3 <?= $range === "custom" ? "" : "d-none" ?>" id="customDateRange">
            <div class="col-md-3">
                <label class="form-label" for="start_date">Start date</label>
                <input class="form-control" id="start_date" type="date" name="start_date" value="<?= View::e($customStart) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="end_date">End date</label>
                <input class="form-control" id="end_date" type="date" name="end_date" value="<?= View::e($customEnd) ?>">
            </div>
        </div>
    </form>
</section>

<section class="content-card chart-card money-chart-card mb-3" id="moneyCharts" data-chart="<?= View::e((string) json_encode($chartData, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>">
    <div class="card-section-heading">
        <div>
            <p class="card-kicker"><?= View::e($periodLabel) ?></p>
            <h2>Cash Flow Trend</h2>
        </div>
        <span class="text-muted small">Income, spending, and net movement</span>
    </div>
    <?php if ($hasCashFlow): ?>
        <div class="money-cashflow-area"><canvas id="moneyCashFlowChart" aria-label="Income, expenses, and net cash flow over time"></canvas></div>
    <?php else: ?>
        <div class="money-chart-empty"><i class="bi bi-graph-up"></i><p>No transactions match the current filters.</p></div>
    <?php endif; ?>
</section>

<section class="row g-3 mb-3">
    <div class="col-xl-5">
        <article class="content-card chart-card money-chart-card h-100">
            <p class="card-kicker">Expense analysis</p>
            <h2>Spending by Category</h2>
            <?php if ($chartData["category"]["expenseValues"]): ?>
                <div class="money-chart-area"><canvas id="moneyCategoryChart" aria-label="Expense distribution by category"></canvas></div>
            <?php else: ?>
                <div class="money-chart-empty"><i class="bi bi-pie-chart"></i><p>No expenses match the current filters.</p></div>
            <?php endif; ?>
        </article>
    </div>
    <div class="col-xl-7">
        <article class="content-card chart-card money-chart-card h-100">
            <p class="card-kicker">Category comparison</p>
            <h2>Income vs Expenses</h2>
            <?php if ($hasCategoryActivity): ?>
                <div class="money-chart-area"><canvas id="moneyComparisonChart" aria-label="Income and expenses by category"></canvas></div>
            <?php else: ?>
                <div class="money-chart-empty"><i class="bi bi-bar-chart"></i><p>No transactions match the current filters.</p></div>
            <?php endif; ?>
        </article>
    </div>
</section>

<section class="content-card p-0 overflow-hidden">
    <div class="card-section-heading px-3 pt-3 mb-2">
        <div><p class="card-kicker">Transaction history</p><h2>Records</h2></div>
        <span class="text-muted small"><?= count($records) ?> matching record<?= count($records) === 1 ? "" : "s" ?></span>
    </div>
    <div class="table-responsive">
        <table class="table app-table align-middle mb-0">
            <thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Receipt</th><th class="text-end">Amount</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= View::e(date("d M Y", strtotime($record["transaction_date"]))) ?></td>
                        <td><span class="type-pill <?= $record["transaction_type"] === "Income" ? "type-income" : "type-expense" ?>"><?= View::e($record["transaction_type"]) ?></span></td>
                        <td><strong><?= View::e($record["category"]) ?></strong></td>
                        <td><?= View::e($record["description"] ?: "—") ?></td>
                        <td>
                            <?php if ($record["receipt_path"]): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money/receipt?id=<?= (int) $record["record_id"] ?>" target="_blank" rel="noopener" title="View receipt"><i class="bi bi-receipt"></i></a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><strong class="<?= $record["transaction_type"] === "Income" ? "text-success" : "text-danger" ?>"><?= $record["transaction_type"] === "Income" ? "+" : "−" ?> RM <?= number_format((float) $record["amount"], 2) ?></strong></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= View::e($baseUrl) ?>/money/edit?id=<?= (int) $record["record_id"] ?>" title="Edit transaction"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= View::e($baseUrl) ?>/money/delete" onsubmit="return confirm('Delete this transaction?')">
                                    <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
                                    <input type="hidden" name="record_id" value="<?= (int) $record["record_id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete transaction"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$records): ?>
                    <tr><td colspan="7"><div class="empty-state"><h3>No transactions found</h3><p>Try another filter or add a transaction.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
