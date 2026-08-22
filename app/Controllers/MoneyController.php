<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\MoneyBudget;
use App\Models\MoneyRecord;
use App\Services\MoneyReceiptUploadService;

/** Handles student finance records, spending plans, filters, and exports. */
final class MoneyController extends Controller
{
    private const EXPENSE_CATEGORIES = [
        "Food",
        "Transport",
        "Study",
        "Accommodation",
        "Health",
        "Entertainment",
        "Other",
    ];

    private const INCOME_CATEGORIES = [
        "Allowance",
        "Salary",
        "Scholarship",
        "Refund",
        "Other",
    ];

    private const CATEGORIES = [
        "Food",
        "Transport",
        "Study",
        "Accommodation",
        "Health",
        "Entertainment",
        "Allowance",
        "Salary",
        "Scholarship",
        "Refund",
        "Other",
    ];

    public function __construct(
        private readonly Auth $auth,
        private readonly MoneyRecord $records,
        private readonly MoneyBudget $budgets,
        private readonly MoneyReceiptUploadService $receipts,
    ) {
    }

    public function index(): void
    {
        $userId = $this->studentId();
        $filterData = $this->filters();
        $filters = $filterData["filters"];

        $this->view("money/index", [
            "pageTitle" => "Money Tracker",
            "records" => $this->records->list($userId, $filters),
            "totals" => $this->records->totals($userId, $filters),
            "categorySummary" => $this->records->categorySummary($userId, $filters),
            "cashFlow" => $this->records->cashFlowTrend($userId, $filters),
            "budgetOverview" => $this->budgets->overview($userId),
            "type" => $filterData["type"],
            "category" => $filterData["category"],
            "search" => $filterData["search"],
            "range" => $filterData["range"],
            "periodLabel" => $filterData["periodLabel"],
            "filterError" => $filterData["filterError"],
            "customStart" => $filterData["customStart"],
            "customEnd" => $filterData["customEnd"],
            "categories" => self::CATEGORIES,
            "expenseCategories" => self::EXPENSE_CATEGORIES,
        ]);
    }

    public function createForm(): void
    {
        $this->studentId();
        $this->renderForm(null, []);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $userId = $this->studentId();
        $validated = $this->validate($_POST);
        if ($validated["errors"]) {
            $this->renderForm($_POST, $validated["errors"]);
            return;
        }

        try {
            $validated["data"]["receipt_path"] = $this->receipts->upload($_FILES["receipt"] ?? []);
            $this->records->create($userId, $validated["data"]);
            Flash::add("success", "Transaction added.");
            $this->addBudgetFeedback($userId, $validated["data"]);
            $this->redirect("money");
        } catch (\RuntimeException $error) {
            $this->renderForm($_POST, [$error->getMessage()]);
        }
    }

    public function editForm(): void
    {
        $userId = $this->studentId();
        $record = $this->records->findOwned((int) ($_GET["id"] ?? 0), $userId);
        if (!$record) {
            Flash::add("error", "Transaction not found.");
            $this->redirect("money");
        }
        $this->renderForm($record, []);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $userId = $this->studentId();
        $id = (int) ($_POST["record_id"] ?? 0);
        $old = $this->records->findOwned($id, $userId);
        if (!$old) {
            Flash::add("error", "Transaction not found.");
            $this->redirect("money");
        }

        $validated = $this->validate($_POST);
        if ($validated["errors"]) {
            $this->renderForm(array_merge($old, $_POST, ["record_id" => $id]), $validated["errors"]);
            return;
        }

        try {
            $newReceipt = $this->receipts->upload($_FILES["receipt"] ?? []);
            $removeReceipt = isset($_POST["remove_receipt"]);
            $validated["data"]["receipt_path"] = $newReceipt ?: ($removeReceipt ? null : $old["receipt_path"]);
            $this->records->update($id, $userId, $validated["data"]);
            if (($newReceipt !== null || $removeReceipt) && $old["receipt_path"]) {
                $this->receipts->remove($old["receipt_path"]);
            }
            Flash::add("success", "Transaction updated.");
            $this->addBudgetFeedback($userId, $validated["data"]);
            $this->redirect("money");
        } catch (\RuntimeException $error) {
            $this->renderForm(array_merge($old, $_POST, ["record_id" => $id]), [$error->getMessage()]);
        }
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $record = $this->records->delete((int) ($_POST["record_id"] ?? 0), $this->studentId());
        if ($record) {
            $this->receipts->remove($record["receipt_path"]);
            Flash::add("success", "Transaction deleted.");
        } else {
            Flash::add("error", "Transaction not found.");
        }
        $this->redirect("money");
    }

    public function saveBudget(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $userId = $this->studentId();
        $category = (string) ($_POST["category"] ?? "");
        $limit = (float) ($_POST["monthly_limit"] ?? 0);
        if (!in_array($category, self::EXPENSE_CATEGORIES, true)) {
            Flash::add("error", "Choose a valid expense category for the budget.");
        } elseif ($limit <= 0 || $limit > 99999999) {
            Flash::add("error", "Enter a valid monthly budget amount.");
        } else {
            $this->budgets->save($userId, $category, $limit);
            Flash::add("success", "Monthly budget saved for {$category}.");
        }
        $this->redirect("money");
    }

    public function deleteBudget(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $category = (string) ($_POST["category"] ?? "");
        if (in_array($category, self::EXPENSE_CATEGORIES, true)) {
            $this->budgets->delete($this->studentId(), $category);
            Flash::add("success", "Monthly budget removed for {$category}.");
        }
        $this->redirect("money");
    }

    public function export(): void
    {
        $userId = $this->studentId();
        $filters = $this->filters()["filters"];
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename="money-transactions.csv"');
        $output = fopen("php://output", "w");
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ["Date", "Type", "Category", "Description", "Amount (RM)", "Receipt attached"]);
        foreach ($this->records->export($userId, $filters) as $record) {
            fputcsv($output, [
                $record["transaction_date"],
                $this->csvValue($record["transaction_type"]),
                $this->csvValue($record["category"]),
                $this->csvValue($record["description"] ?? ""),
                $record["amount"],
                $record["receipt_path"] ? "Yes" : "No",
            ]);
        }
        fclose($output);
        exit();
    }

    public function receipt(): void
    {
        $record = $this->records->findOwned((int) ($_GET["id"] ?? 0), $this->studentId());
        $path = $record ? $this->receipts->path($record["receipt_path"] ?? null) : null;
        if ($path === null || !is_file($path)) {
            http_response_code(404);
            exit("Receipt not found.");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ["image/jpeg", "image/png", "image/webp"], true)) {
            http_response_code(404);
            exit("Receipt not found.");
        }
        header("Content-Type: " . $mime);
        header("Content-Length: " . (string) filesize($path));
        header("X-Content-Type-Options: nosniff");
        readfile($path);
        exit();
    }

    /** Builds the active filters and a safe inclusive date range. */
    private function filters(): array
    {
        $type = (string) ($_GET["type"] ?? "");
        $category = (string) ($_GET["category"] ?? "");
        $search = mb_substr(trim((string) ($_GET["search"] ?? "")), 0, 100);
        $range = (string) ($_GET["range"] ?? "month");
        $customStart = (string) ($_GET["start_date"] ?? "");
        $customEnd = (string) ($_GET["end_date"] ?? "");
        $filterError = null;

        if (!in_array($type, ["", "Income", "Expense"], true)) {
            $type = "";
        }
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = "";
        }
        if (!in_array($range, ["month", "quarter", "all", "custom"], true)) {
            $range = "month";
        }

        if ($range === "custom") {
            $startDate = \DateTimeImmutable::createFromFormat("!Y-m-d", $customStart);
            $endDate = \DateTimeImmutable::createFromFormat("!Y-m-d", $customEnd);
            if (!$startDate || !$endDate || $startDate->format("Y-m-d") !== $customStart || $endDate->format("Y-m-d") !== $customEnd || $startDate > $endDate) {
                [$start, $end, $periodLabel] = [date("Y-m-01"), date("Y-m-01", strtotime("+1 month")), "Choose custom dates"];
                $filterError = $customStart !== "" || $customEnd !== "" ? "Choose a valid start and end date." : null;
            } else {
                [$start, $end, $periodLabel] = [$startDate->format("Y-m-d"), $endDate->modify("+1 day")->format("Y-m-d"), $startDate->format("d M Y") . " – " . $endDate->format("d M Y")];
            }
        } elseif ($range === "quarter") {
            [$start, $end, $periodLabel] = [date("Y-m-01", strtotime("-2 months")), date("Y-m-01", strtotime("+1 month")), "Recent 3 Months"];
        } elseif ($range === "all") {
            [$start, $end, $periodLabel] = ["1000-01-01", "9999-12-31", "All Time"];
        } else {
            [$start, $end, $periodLabel] = [date("Y-m-01"), date("Y-m-01", strtotime("+1 month")), "Recent 1 Month"];
        }

        return compact("type", "category", "search", "range", "periodLabel", "filterError", "customStart", "customEnd") + [
            "filters" => compact("type", "category", "search", "start", "end"),
        ];
    }

    /** Renders the shared create/edit form with type-specific category choices. */
    private function renderForm(?array $record, array $errors): void
    {
        $this->view("money/form", [
            "pageTitle" => $record && isset($record["record_id"]) ? "Edit Transaction" : "Add Transaction",
            "record" => $record,
            "errors" => $errors,
            "incomeCategories" => self::INCOME_CATEGORIES,
            "expenseCategories" => self::EXPENSE_CATEGORIES,
        ]);
    }

    /** Validates a transaction and enforces categories that fit its selected type. */
    private function validate(array $input): array
    {
        $data = [
            "amount" => (float) ($input["amount"] ?? 0),
            "category" => trim((string) ($input["category"] ?? "")),
            "description" => trim((string) ($input["description"] ?? "")) ?: null,
            "transaction_type" => (string) ($input["transaction_type"] ?? ""),
            "transaction_date" => (string) ($input["transaction_date"] ?? ""),
        ];
        $errors = [];
        $date = \DateTimeImmutable::createFromFormat("!Y-m-d", $data["transaction_date"]);
        if ($data["amount"] <= 0 || $data["amount"] > 99999999) {
            $errors[] = "Enter a valid positive amount.";
        }
        if (!in_array($data["transaction_type"], ["Income", "Expense"], true)) {
            $errors[] = "Choose Income or Expense.";
        } elseif (!in_array($data["category"], $this->categoriesForType($data["transaction_type"]), true)) {
            $errors[] = "Choose a category that matches the transaction type.";
        }
        if (!$date || $date->format("Y-m-d") !== $data["transaction_date"] || $date > new \DateTimeImmutable("today")) {
            $errors[] = "Choose a valid date that is not in the future.";
        }
        if ($data["description"] !== null && mb_strlen($data["description"]) > 255) {
            $errors[] = "Description must be 255 characters or fewer.";
        }

        return ["data" => $data, "errors" => $errors];
    }

    /** Adds immediate financial feedback after an expense affects a saved budget. */
    private function addBudgetFeedback(int $userId, array $data): void
    {
        if ($data["transaction_type"] !== "Expense") {
            return;
        }
        $warning = $this->budgets->categoryWarning($userId, $data["category"]);
        if ($warning !== null) {
            Flash::add("warning", $warning);
        }
    }

    /** Stops administrators from creating or reading student finance records. */
    private function studentId(): int
    {
        $userId = $this->auth->requireLogin();
        if ($this->auth->role() === "Admin") {
            $this->redirect("admin");
        }
        return $userId;
    }

    /** Returns only the categories appropriate to a transaction type. */
    private function categoriesForType(string $type): array
    {
        return $type === "Income" ? self::INCOME_CATEGORIES : self::EXPENSE_CATEGORIES;
    }

    /** Prefixes spreadsheet formula-like CSV values so exports remain safe to open. */
    private function csvValue(mixed $value): mixed
    {
        return is_string($value) && $value !== "" && in_array($value[0], ["=", "+", "-", "@"], true)
            ? "'" . $value
            : $value;
    }
}
