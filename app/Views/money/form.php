<?php

use App\Core\Csrf;
use App\Core\View;

$edit = is_array($record) && isset($record["record_id"]);
$type = $record["transaction_type"] ?? "Expense";
$category = $record["category"] ?? "Food";
$categoryOptions = $type === "Income" ? $incomeCategories : $expenseCategories;
$categoryData = ["Income" => $incomeCategories, "Expense" => $expenseCategories];
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Money tracker</p>
        <h1><?= $edit ? "Edit Transaction" : "Add Transaction" ?></h1>
        <p class="page-subtitle">Use the right category for a clearer budget and cash-flow view.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money"><i class="bi bi-arrow-left"></i> Back</a>
</section>

<?php foreach ($errors as $error): ?>
    <div class="app-flash flash-error"><i class="bi bi-exclamation-circle-fill"></i><?= View::e($error) ?></div>
<?php endforeach; ?>

<section class="form-panel">
    <form method="post" enctype="multipart/form-data" action="<?= View::e($baseUrl) ?>/money/<?= $edit ? "update" : "store" ?>">
        <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>">
        <?php if ($edit): ?>
            <input type="hidden" name="record_id" value="<?= (int) $record["record_id"] ?>">
        <?php endif; ?>
        <div id="moneyForm" data-categories="<?= View::e((string) json_encode($categoryData, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>" class="row g-4">
            <div class="col-md-6">
                <label class="form-label" for="amount">Amount (RM) <span class="text-danger">*</span></label>
                <div class="input-group"><span class="input-group-text">RM</span><input class="form-control" id="amount" type="number" min="0.01" step="0.01" name="amount" value="<?= View::e($record["amount"] ?? "") ?>" required></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="transaction_type">Type <span class="text-danger">*</span></label>
                <select class="form-select" id="transaction_type" name="transaction_type">
                    <option value="Income" <?= $type === "Income" ? "selected" : "" ?>>Income</option>
                    <option value="Expense" <?= $type === "Expense" ? "selected" : "" ?>>Expense</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category" name="category">
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?= View::e($option) ?>" <?= $category === $option ? "selected" : "" ?>><?= View::e($option) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Categories change to match Income or Expense.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="transaction_date">Date <span class="text-danger">*</span></label>
                <input class="form-control" id="transaction_date" type="date" max="<?= date("Y-m-d") ?>" name="transaction_date" value="<?= View::e($record["transaction_date"] ?? date("Y-m-d")) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description <span class="text-muted">(optional)</span></label>
                <input class="form-control" id="description" maxlength="255" name="description" value="<?= View::e($record["description"] ?? "") ?>" placeholder="What was this transaction for?">
            </div>
            <div class="col-md-8">
                <label class="form-label" for="receipt">Receipt or evidence image <span class="text-muted">(optional)</span></label>
                <input class="form-control" id="receipt" type="file" name="receipt" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG, or WebP only. Maximum 5 MB.</div>
            </div>
            <?php if ($edit && ($record["receipt_path"] ?? "")): ?>
                <div class="col-12">
                    <div class="existing-file">
                        <a href="<?= View::e($baseUrl) ?>/money/receipt?id=<?= (int) $record["record_id"] ?>" target="_blank" rel="noopener"><img src="<?= View::e($baseUrl) ?>/money/receipt?id=<?= (int) $record["record_id"] ?>" alt="Current receipt"></a>
                        <div>
                            <strong>Current receipt</strong>
                            <div class="form-text">Choose another image above to replace it.</div>
                            <div class="form-check mt-2"><input class="form-check-input" id="remove_receipt" type="checkbox" name="remove_receipt"><label class="form-check-label" for="remove_receipt">Remove current receipt</label></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a class="btn btn-outline-secondary" href="<?= View::e($baseUrl) ?>/money">Cancel</a>
            <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save Transaction</button>
        </div>
    </form>
</section>

<script src="<?= View::e($baseUrl) ?>/assets/js/money-form.js" defer></script>
