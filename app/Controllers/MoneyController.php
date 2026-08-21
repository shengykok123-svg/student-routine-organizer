<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\MoneyRecord;
use App\Services\MoneyReceiptUploadService;

final class MoneyController extends Controller
{
    private const CATEGORIES = ['Food', 'Transport', 'Study', 'Accommodation', 'Health', 'Entertainment', 'Salary', 'Allowance', 'Other'];

    public function __construct(
        private readonly Auth $auth,
        private readonly MoneyRecord $records,
        private readonly MoneyReceiptUploadService $receipts,
    ) {}

    public function index(): void
    {
        $user = $this->auth->requireLogin();
        $filterData = $this->filters();
        $records = $this->records->list($user, $filterData['filters']);
        $this->view('money/index', [
            'pageTitle' => 'Money Tracker',
            'records' => $records,
            'totals' => $this->records->totals($user, $filterData['filters']),
            'categorySummary' => $this->records->categorySummary($user, $filterData['filters']),
            'type' => $filterData['type'], 'category' => $filterData['category'], 'search' => $filterData['search'],
            'range' => $filterData['range'], 'periodLabel' => $filterData['periodLabel'],
            'filterError' => $filterData['filterError'], 'customStart' => $filterData['customStart'],
            'customEnd' => $filterData['customEnd'], 'categories' => self::CATEGORIES,
        ]);
    }

    public function createForm(): void
    {
        $this->auth->requireLogin();
        $this->view('money/form', ['pageTitle' => 'Add Transaction', 'record' => null, 'errors' => [], 'categories' => self::CATEGORIES]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $validated = $this->validate($_POST);
        if ($validated['errors']) {
            $this->view('money/form', ['pageTitle' => 'Add Transaction', 'record' => $_POST, 'errors' => $validated['errors'], 'categories' => self::CATEGORIES]);
            return;
        }
        try {
            $validated['data']['receipt_path'] = $this->receipts->upload($_FILES['receipt'] ?? []);
            $this->records->create($user, $validated['data']);
            Flash::add('success', 'Transaction added.');
            $this->redirect('money');
        } catch (\RuntimeException $e) {
            $this->view('money/form', ['pageTitle' => 'Add Transaction', 'record' => $_POST, 'errors' => [$e->getMessage()], 'categories' => self::CATEGORIES]);
        }
    }

    public function editForm(): void
    {
        $user = $this->auth->requireLogin();
        $record = $this->records->findOwned((int) ($_GET['id'] ?? 0), $user);
        if (!$record) {
            Flash::add('error', 'Transaction not found.');
            $this->redirect('money');
        }
        $this->view('money/form', ['pageTitle' => 'Edit Transaction', 'record' => $record, 'errors' => [], 'categories' => self::CATEGORIES]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $id = (int) ($_POST['record_id'] ?? 0);
        $old = $this->records->findOwned($id, $user);
        if (!$old) {
            Flash::add('error', 'Transaction not found.');
            $this->redirect('money');
        }
        $validated = $this->validate($_POST);
        if ($validated['errors']) {
            $this->renderEditWithInput($id, $old, $validated['errors']);
            return;
        }
        try {
            $newReceipt = $this->receipts->upload($_FILES['receipt'] ?? []);
            $removeReceipt = isset($_POST['remove_receipt']);
            $validated['data']['receipt_path'] = $newReceipt ?: ($removeReceipt ? null : $old['receipt_path']);
            $this->records->update($id, $user, $validated['data']);
            if (($newReceipt !== null || $removeReceipt) && $old['receipt_path']) $this->receipts->remove($old['receipt_path']);
            Flash::add('success', 'Transaction updated.');
            $this->redirect('money');
        } catch (\RuntimeException $e) {
            $this->renderEditWithInput($id, $old, [$e->getMessage()]);
        }
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $record = $this->records->delete((int) ($_POST['record_id'] ?? 0), $this->auth->requireLogin());
        if ($record) {
            $this->receipts->remove($record['receipt_path']);
            Flash::add('success', 'Transaction deleted.');
        } else {
            Flash::add('error', 'Transaction not found.');
        }
        $this->redirect('money');
    }

    public function export(): void
    {
        $user = $this->auth->requireLogin();
        $filters = $this->filters()['filters'];
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="money-transactions.csv"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Date', 'Type', 'Category', 'Description', 'Amount (RM)', 'Receipt attached']);
        foreach ($this->records->export($user, $filters) as $record) {
            fputcsv($output, [$record['transaction_date'], $this->csvValue($record['transaction_type']), $this->csvValue($record['category']), $this->csvValue($record['description'] ?? ''), $record['amount'], $record['receipt_path'] ? 'Yes' : 'No']);
        }
        fclose($output);
        exit;
    }

    public function receipt(): void
    {
        $user = $this->auth->requireLogin();
        $record = $this->records->findOwned((int) ($_GET['id'] ?? 0), $user);
        $path = $record ? $this->receipts->path($record['receipt_path'] ?? null) : null;
        if ($path === null || !is_file($path)) {
            http_response_code(404);
            exit('Receipt not found.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            exit('Receipt not found.');
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** @return array{filters:array{type:string,category:string,search:string,start:string,end:string},type:string,category:string,search:string,range:string,periodLabel:string,filterError:?string,customStart:string,customEnd:string} */
    private function filters(): array
    {
        $type = (string) ($_GET['type'] ?? '');
        $category = (string) ($_GET['category'] ?? '');
        $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
        if (!in_array($type, ['', 'Income', 'Expense'], true)) $type = '';
        if (!in_array($category, self::CATEGORIES, true)) $category = '';
        $range = (string) ($_GET['range'] ?? 'month');
        if (!in_array($range, ['month', 'quarter', 'all', 'custom'], true)) $range = 'month';
        $customStart = (string) ($_GET['start_date'] ?? '');
        $customEnd = (string) ($_GET['end_date'] ?? '');
        $filterError = null;
        if ($range === 'custom') {
            $startDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $customStart);
            $endDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $customEnd);
            if (!$startDate || !$endDate || $startDate->format('Y-m-d') !== $customStart || $endDate->format('Y-m-d') !== $customEnd || $startDate > $endDate) {
                [$start, $end, $periodLabel] = [date('Y-m-01'), date('Y-m-01', strtotime('+1 month')), 'Choose custom dates'];
                $filterError = ($customStart !== '' || $customEnd !== '') ? 'Choose a valid start and end date.' : null;
            } else {
                [$start, $end, $periodLabel] = [$startDate->format('Y-m-d'), $endDate->modify('+1 day')->format('Y-m-d'), $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y')];
            }
        } elseif ($range === 'quarter') {
            [$start, $end, $periodLabel] = [date('Y-m-01', strtotime('-2 months')), date('Y-m-01', strtotime('+1 month')), 'Recent 3 Months'];
        } elseif ($range === 'all') {
            [$start, $end, $periodLabel] = ['1000-01-01', '9999-12-31', 'All Time'];
        } else {
            [$start, $end, $periodLabel] = [date('Y-m-01'), date('Y-m-01', strtotime('+1 month')), 'Recent 1 Month'];
        }
        return compact('type', 'category', 'search', 'range', 'periodLabel', 'filterError', 'customStart', 'customEnd') + ['filters' => compact('type', 'category', 'search', 'start', 'end')];
    }

    private function renderEditWithInput(int $id, array $old, array $errors): void
    {
        $record = array_merge($old, $_POST, ['record_id' => $id]);
        $this->view('money/form', ['pageTitle' => 'Edit Transaction', 'record' => $record, 'errors' => $errors, 'categories' => self::CATEGORIES]);
    }

    private function validate(array $input): array
    {
        $data = ['amount' => (float) ($input['amount'] ?? 0), 'category' => trim((string) ($input['category'] ?? '')), 'description' => trim((string) ($input['description'] ?? '')) ?: null, 'transaction_type' => (string) ($input['transaction_type'] ?? ''), 'transaction_date' => (string) ($input['transaction_date'] ?? '')];
        $errors = [];
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $data['transaction_date']);
        if ($data['amount'] <= 0 || $data['amount'] > 99999999) $errors[] = 'Enter a valid positive amount.';
        if (!in_array($data['category'], self::CATEGORIES, true)) $errors[] = 'Choose a valid category.';
        if (!in_array($data['transaction_type'], ['Income', 'Expense'], true)) $errors[] = 'Choose Income or Expense.';
        if (!$date || $date->format('Y-m-d') !== $data['transaction_date'] || $date > new \DateTimeImmutable('today')) $errors[] = 'Choose a valid date that is not in the future.';
        if ($data['description'] !== null && mb_strlen($data['description']) > 255) $errors[] = 'Description must be 255 characters or fewer.';
        return ['data' => $data, 'errors' => $errors];
    }

    private function csvValue(mixed $value): mixed
    {
        return is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) ? "'" . $value : $value;
    }

}
