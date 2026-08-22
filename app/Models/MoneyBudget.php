<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Stores monthly spending limits and calculates the current month's budget health. */
final class MoneyBudget
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** Returns all current-month category budgets with their actual spending. */
    public function overview(int $userId): array
    {
        $monthStart = date("Y-m-01");
        $monthEnd = date("Y-m-01", strtotime("+1 month"));
        $statement = $this->pdo->prepare(
            "SELECT b.category, b.monthly_limit,
                COALESCE(SUM(CASE WHEN r.transaction_type = 'Expense' THEN r.amount ELSE 0 END), 0) AS spent
             FROM money_budgets b
             LEFT JOIN money_records r ON r.user_id = b.user_id
                AND r.category = b.category
                AND r.transaction_date >= ?
                AND r.transaction_date < ?
             WHERE b.user_id = ?
             GROUP BY b.budget_id, b.category, b.monthly_limit
             ORDER BY b.category ASC",
        );
        $statement->execute([$monthStart, $monthEnd, $userId]);
        $items = [];
        $limitTotal = 0.0;
        $spentTotal = 0.0;

        foreach ($statement->fetchAll() as $row) {
            $limit = (float) $row["monthly_limit"];
            $spent = (float) $row["spent"];
            $limitTotal += $limit;
            $spentTotal += $spent;
            $items[] = [
                "category" => $row["category"],
                "limit" => $limit,
                "spent" => $spent,
                "remaining" => $limit - $spent,
                "percentage" => $limit > 0 ? ($spent / $limit) * 100 : 0,
            ];
        }

        $daysRemaining = max(1, (int) date("t") - (int) date("j") + 1);
        $remaining = $limitTotal - $spentTotal;

        return [
            "items" => $items,
            "limit_total" => $limitTotal,
            "spent_total" => $spentTotal,
            "remaining" => $remaining,
            "safe_daily" => max(0, $remaining) / $daysRemaining,
            "days_remaining" => $daysRemaining,
            "month_label" => date("F Y"),
        ];
    }

    /** Creates or updates a spending limit for one expense category. */
    public function save(int $userId, string $category, float $limit): void
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO money_budgets (user_id, category, monthly_limit) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE monthly_limit = VALUES(monthly_limit), updated_at = CURRENT_TIMESTAMP",
        );
        $statement->execute([$userId, $category, $limit]);
    }

    /** Removes a user's spending limit for one category. */
    public function delete(int $userId, string $category): void
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM money_budgets WHERE user_id = ? AND category = ?",
        );
        $statement->execute([$userId, $category]);
    }

    /** Returns an over-budget or near-limit warning for a category, if applicable. */
    public function categoryWarning(int $userId, string $category): ?string
    {
        foreach ($this->overview($userId)["items"] as $item) {
            if ($item["category"] !== $category) {
                continue;
            }
            if ($item["percentage"] >= 100) {
                return "{$category} is over its monthly budget by RM " . number_format(abs($item["remaining"]), 2) . ".";
            }
            if ($item["percentage"] >= 80) {
                return "{$category} has used " . number_format($item["percentage"], 0) . "% of its monthly budget.";
            }
        }

        return null;
    }
}
