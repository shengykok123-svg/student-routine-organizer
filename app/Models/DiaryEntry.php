<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class DiaryEntry
{
    public function __construct(private readonly PDO $pdo) {}

    public function findOwned(int $id, int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?');
        $statement->execute([$id, $userId]);
        return $statement->fetch() ?: null;
    }

    public function list(int $userId, array $filters): array
    {
        $where = ['user_id = :user'];
        $parameters = [':user' => $userId];
        if ($filters['search'] !== '') {
            $where[] = '(title LIKE :title_search OR content LIKE :content_search)';
            $parameters[':title_search'] = '%' . $filters['search'] . '%';
            $parameters[':content_search'] = '%' . $filters['search'] . '%';
        }
        if ($filters['mood'] !== '') {
            $where[] = 'mood = :mood';
            $parameters[':mood'] = $filters['mood'];
        }
        if ($filters['favorite']) {
            $where[] = 'is_favorite = 1';
        }
        $sort = ['newest' => 'entry_date DESC, entry_id DESC', 'oldest' => 'entry_date ASC, entry_id ASC', 'title' => 'title ASC'][$filters['sort']] ?? 'entry_date DESC';
        $statement = $this->pdo->prepare('SELECT * FROM diary_entries WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $sort);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare('INSERT INTO diary_entries (user_id, title, content, mood, mood_score, image_path, is_favorite, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([$userId, $data['title'], $data['content'], $data['mood'], $data['mood_score'], $data['image_path'], $data['is_favorite'], $data['entry_date']]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare('UPDATE diary_entries SET title = ?, content = ?, mood = ?, mood_score = ?, image_path = ?, is_favorite = ?, entry_date = ? WHERE entry_id = ? AND user_id = ?');
        $statement->execute([$data['title'], $data['content'], $data['mood'], $data['mood_score'], $data['image_path'], $data['is_favorite'], $data['entry_date'], $id, $userId]);
        return $statement->rowCount() === 1;
    }

    public function delete(int $id, int $userId): ?array
    {
        $entry = $this->findOwned($id, $userId);
        if ($entry === null) return null;
        $statement = $this->pdo->prepare('DELETE FROM diary_entries WHERE entry_id = ? AND user_id = ?');
        $statement->execute([$id, $userId]);
        return $statement->rowCount() ? $entry : null;
    }

    public function insights(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) entries, COALESCE(ROUND(AVG(mood_score), 1), 0) average, COALESCE(SUM(is_favorite = 1), 0) favorites, COUNT(DISTINCT entry_date) active_days FROM diary_entries WHERE user_id = ?');
        $statement->execute([$userId]);
        $totals = $statement->fetch();
        $statement = $this->pdo->prepare('SELECT mood, COUNT(*) count FROM diary_entries WHERE user_id = ? GROUP BY mood ORDER BY count DESC, mood');
        $statement->execute([$userId]);
        return ['totals' => $totals, 'moods' => $statement->fetchAll()];
    }

    public function calendar(int $userId, string $start, string $end): array
    {
        $statement = $this->pdo->prepare('SELECT entry_id, title, mood, entry_date FROM diary_entries WHERE user_id = ? AND entry_date BETWEEN ? AND ? ORDER BY entry_id DESC');
        $statement->execute([$userId, $start, $end]);
        return $statement->fetchAll();
    }
}
