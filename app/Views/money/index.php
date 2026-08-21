<?php

use App\Core\Csrf;
use App\Core\View;

$balance = (float) $totals['income'] - (float) $totals['expense'];
$selectedCategory = $category;
$exportQuery = http_build_query(['search' => $search, 'range' => $range, 'type' => $type, 'category' => $category, 'start_date' => $customStart, 'end_date' => $customEnd]);
$chartData = ['labels' => array_column($categorySummary, 'category'), 'income' => array_map(static fn(array $row): float => (float) $row['income'], $categorySummary), 'expense' => array_map(static fn(array $row): float => (float) $row['expense'], $categorySummary)];
$hasExpenses = array_sum($chartData['expense']) > 0;
$hasCategoryActivity = array_sum($chartData['income']) + array_sum($chartData['expense']) > 0;
?>
<section class="page-heading">
    <div><p class="page-eyebrow">Personal finance</p><h1>Money Tracker</h1><p class="page-subtitle">Showing <?= View::e($periodLabel) ?>. Filters update automatically.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money/export?<?= View::e($exportQuery) ?>"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a><a class="btn btn-primary" href="<?= View::e($baseUrl) ?>/money/create"><i class="bi bi-plus-lg"></i> Add Transaction</a></div>
</section>
<?php if ($filterError): ?><div class="app-flash flash-error"><i class="bi bi-exclamation-circle-fill"></i><?= View::e($filterError) ?></div><?php endif; ?>
<section class="stats-grid stats-grid-3">
    <?php foreach ([['Total Income', $totals['income'], 'stat-card-success', 'bi-arrow-down-left-circle'], ['Total Expenses', $totals['expense'], 'stat-card-danger', 'bi-arrow-up-right-circle'], ['Current Balance', $balance, 'money-balance', 'bi-wallet2']] as [$label, $value, $class, $icon]): ?>
        <article class="stat-card <?= $class ?>"><div><p><?= $label ?></p><h2>RM <?= number_format((float) $value, 2) ?></h2></div><span class="stat-icon"><i class="bi <?= $icon ?>"></i></span></article>
    <?php endforeach; ?>
</section>
<section class="filter-panel mb-3">
    <form method="get" id="moneyFilter" class="row g-3 align-items-end">
        <div class="col-lg-3"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="<?= View::e($search) ?>" placeholder="Description or category"></div>
        <div class="col-lg-3"><label class="form-label" for="range">Period</label><select class="form-select" id="range" name="range"><option value="month" <?= $range === 'month' ? 'selected' : '' ?>>Recent 1 Month</option><option value="quarter" <?= $range === 'quarter' ? 'selected' : '' ?>>Recent 3 Months</option><option value="all" <?= $range === 'all' ? 'selected' : '' ?>>All Time</option><option value="custom" <?= $range === 'custom' ? 'selected' : '' ?>>Custom Date Range</option></select></div>
        <div class="col-lg-3"><label class="form-label" for="type">Type</label><select class="form-select" id="type" name="type"><option value="">All types</option><option value="Income" <?= $type === 'Income' ? 'selected' : '' ?>>Income</option><option value="Expense" <?= $type === 'Expense' ? 'selected' : '' ?>>Expense</option></select></div>
        <div class="col-lg-3"><label class="form-label" for="category">Category</label><select class="form-select" id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $filterCategory): ?><option value="<?= View::e($filterCategory) ?>" <?= $selectedCategory === $filterCategory ? 'selected' : '' ?>><?= View::e($filterCategory) ?></option><?php endforeach; ?></select></div>
        <div class="col-12 row g-3 <?= $range === 'custom' ? '' : 'd-none' ?>" id="customDateRange"><div class="col-md-3"><input class="form-control" id="start_date" type="date" name="start_date" value="<?= View::e($customStart) ?>"></div><div class="col-md-3"><input class="form-control" id="end_date" type="date" name="end_date" value="<?= View::e($customEnd) ?>"></div></div>
    </form>
</section>
<section class="row g-3 mb-3" id="moneyCharts" data-chart="<?= View::e(json_encode($chartData, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>">
    <div class="col-xl-5"><article class="content-card chart-card money-chart-card h-100"><p class="card-kicker">Expense analysis</p><h2>Spending by Category</h2><?php if ($hasExpenses): ?><div class="money-chart-area"><canvas id="moneyCategoryChart" aria-label="Expense distribution by category"></canvas></div><?php else: ?><div class="money-chart-empty"><i class="bi bi-pie-chart"></i><p>No expenses match the current filters.</p></div><?php endif; ?></article></div>
    <div class="col-xl-7"><article class="content-card chart-card money-chart-card h-100"><p class="card-kicker">Category comparison</p><h2>Income vs Expenses</h2><?php if ($hasCategoryActivity): ?><div class="money-chart-area"><canvas id="moneyComparisonChart" aria-label="Income and expense comparison by category"></canvas></div><?php else: ?><div class="money-chart-empty"><i class="bi bi-bar-chart"></i><p>No transactions match the current filters.</p></div><?php endif; ?></article></div>
</section>
<section class="content-card p-0 overflow-hidden"><div class="table-responsive"><table class="table app-table align-middle mb-0"><thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Receipt</th><th class="text-end">Amount</th><th class="text-end">Action</th></tr></thead><tbody>
<?php foreach ($records as $record): ?><tr><td><?= View::e(date('d M Y', strtotime($record['transaction_date']))) ?></td><td><span class="type-pill <?= $record['transaction_type'] === 'Income' ? 'type-income' : 'type-expense' ?>"><?= View::e($record['transaction_type']) ?></span></td><td><strong><?= View::e($record['category']) ?></strong></td><td><?= View::e($record['description'] ?: '—') ?></td><td><?php if ($record['receipt_path']): ?><a class="btn btn-sm btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money/receipt?id=<?= (int) $record['record_id'] ?>" target="_blank" rel="noopener" title="View receipt"><i class="bi bi-receipt"></i></a><?php else: ?><span class="text-muted">—</span><?php endif; ?></td><td class="text-end"><strong><?= $record['transaction_type'] === 'Income' ? '+' : '−' ?> RM <?= number_format((float) $record['amount'], 2) ?></strong></td><td class="text-end"><div class="d-inline-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="<?= View::e($baseUrl) ?>/money/edit?id=<?= (int) $record['record_id'] ?>" title="Edit transaction"><i class="bi bi-pencil"></i></a><form method="post" action="<?= View::e($baseUrl) ?>/money/delete" onsubmit="return confirm('Delete this transaction?')"><input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><input type="hidden" name="record_id" value="<?= (int) $record['record_id'] ?>"><button class="btn btn-sm btn-outline-danger" title="Delete transaction"><i class="bi bi-trash3"></i></button></form></div></td></tr><?php endforeach; ?>
<?php if (!$records): ?><tr><td colspan="7"><div class="empty-state"><h3>No transactions found</h3><p>Try another filter or add a transaction.</p></div></td></tr><?php endif; ?>
</tbody></table></div></section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
