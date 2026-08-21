<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class MoneyRecord
{
    public function __construct(private readonly PDO $pdo) {}

    private function where(int $userId, array $filters): array
    {
        $sql = ' WHERE user_id = ? AND transaction_date >= ? AND transaction_date < ?';
        $arguments = [$userId, $filters['start'], $filters['end']];
        foreach (['type' => 'transaction_type', 'category' => 'category'] as $key => $column) {
            if ($filters[$key] !== '') {
                $sql .= " AND $column = ?";
                $arguments[] = $filters[$key];
            }
        }
        if ($filters['search'] !== '') {
            $sql .= ' AND (category LIKE ? OR description LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $arguments[] = $search;
            $arguments[] = $search;
        }
        return [$sql, $arguments];
    }

    public function list(int $userId, array $filters): array
    {
        [$where, $arguments] = $this->where($userId, $filters);
        $statement = $this->pdo->prepare('SELECT * FROM money_records' . $where . ' ORDER BY transaction_date DESC, record_id DESC');
        $statement->execute($arguments);
        return $statement->fetchAll();
    }

    public function totals(int $userId, array $filters): array
    {
        [$where, $arguments] = $this->where($userId, $filters);
        $statement = $this->pdo->prepare("SELECT COALESCE(SUM(CASE WHEN transaction_type = 'Income' THEN amount ELSE 0 END), 0) income, COALESCE(SUM(CASE WHEN transaction_type = 'Expense' THEN amount ELSE 0 END), 0) expense FROM money_records" . $where);
        $statement->execute($arguments);
        return $statement->fetch();
    }

    public function categorySummary(int $userId, array $filters): array
    {
        [$where, $arguments] = $this->where($userId, $filters);
        $statement = $this->pdo->prepare("SELECT category, COALESCE(SUM(CASE WHEN transaction_type = 'Income' THEN amount ELSE 0 END), 0) income, COALESCE(SUM(CASE WHEN transaction_type = 'Expense' THEN amount ELSE 0 END), 0) expense FROM money_records" . $where . ' GROUP BY category ORDER BY SUM(amount) DESC, category ASC');
        $statement->execute($arguments);
        return $statement->fetchAll();
    }

    public function findOwned(int $id, int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM money_records WHERE record_id = ? AND user_id = ?');
        $statement->execute([$id, $userId]);
        return $statement->fetch() ?: null;
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare('INSERT INTO money_records (user_id, amount, category, description, transaction_type, transaction_date, receipt_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([$userId, $data['amount'], $data['category'], $data['description'], $data['transaction_type'], $data['transaction_date'], $data['receipt_path']]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare('UPDATE money_records SET amount = ?, category = ?, description = ?, transaction_type = ?, transaction_date = ?, receipt_path = ? WHERE record_id = ? AND user_id = ?');
        $statement->execute([$data['amount'], $data['category'], $data['description'], $data['transaction_type'], $data['transaction_date'], $data['receipt_path'], $id, $userId]);
        return $statement->rowCount() > 0;
    }

    public function delete(int $id, int $userId): ?array
    {
        $record = $this->findOwned($id, $userId);
        if (!$record) return null;
        $statement = $this->pdo->prepare('DELETE FROM money_records WHERE record_id = ? AND user_id = ?');
        $statement->execute([$id, $userId]);
        return $statement->rowCount() > 0 ? $record : null;
    }

    public function export(int $userId, array $filters): array
    {
        return $this->list($userId, $filters);
    }
}
