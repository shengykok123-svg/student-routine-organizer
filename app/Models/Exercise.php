<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Exercise
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array{records:list<array<string,mixed>>,total:int} */
    public function list(int $userId, array $filters): array
    {
        $where = ['user_id = :user']; $params = [':user' => $userId];
        if (($filters['search'] ?? '') !== '') { $where[] = 'activity_type LIKE :search'; $params[':search'] = '%' . $filters['search'] . '%'; }
        if (($filters['activity'] ?? '') !== '') { $where[] = 'activity_type = :activity'; $params[':activity'] = $filters['activity']; }
        $order = ['newest' => 'exercise_date DESC, exercise_id DESC', 'oldest' => 'exercise_date ASC, exercise_id ASC', 'duration' => 'duration_minutes DESC, exercise_id DESC', 'calories' => 'calories_burned DESC, exercise_id DESC'][$filters['sort'] ?? 'newest'] ?? 'exercise_date DESC';
        $sqlWhere = implode(' AND ', $where);
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM exercises WHERE ' . $sqlWhere); $count->execute($params);
        $statement = $this->pdo->prepare('SELECT * FROM exercises WHERE ' . $sqlWhere . ' ORDER BY ' . $order); $statement->execute($params);
        return ['records' => $statement->fetchAll(), 'total' => (int) $count->fetchColumn()];
    }

    public function findOwned(int $id, int $userId): ?array { $s=$this->pdo->prepare('SELECT * FROM exercises WHERE exercise_id=? AND user_id=?');$s->execute([$id,$userId]);return $s->fetch()?:null; }
    public function create(int $userId, array $data): int { $s=$this->pdo->prepare('INSERT INTO exercises (user_id,activity_type,duration_minutes,calories_burned,exercise_date,notes) VALUES (?,?,?,?,?,?)');$s->execute([$userId,$data['activity_type'],$data['duration_minutes'],$data['calories_burned'],$data['exercise_date'],$data['notes']]);return (int)$this->pdo->lastInsertId(); }
    public function update(int $id,int $userId,array $data): bool { $s=$this->pdo->prepare('UPDATE exercises SET activity_type=?,duration_minutes=?,calories_burned=?,exercise_date=?,notes=? WHERE exercise_id=? AND user_id=?');$s->execute([$data['activity_type'],$data['duration_minutes'],$data['calories_burned'],$data['exercise_date'],$data['notes'],$id,$userId]);return $s->rowCount()===1; }
    public function delete(int $id,int $userId): bool { $s=$this->pdo->prepare('DELETE FROM exercises WHERE exercise_id=? AND user_id=?');$s->execute([$id,$userId]);return $s->rowCount()===1; }
    public function duplicate(int $userId,array $data,?int $except=null): ?array { $sql='SELECT * FROM exercises WHERE user_id=? AND activity_type=? AND duration_minutes=? AND calories_burned=? AND exercise_date=?';$args=[$userId,$data['activity_type'],$data['duration_minutes'],$data['calories_burned'],$data['exercise_date']];if($except){$sql.=' AND exercise_id<>?';$args[]=$except;}$s=$this->pdo->prepare($sql);$s->execute($args);return $s->fetch()?:null; }
    public function analytics(int $userId): array { $s=$this->pdo->prepare('SELECT COUNT(*) records,COALESCE(SUM(duration_minutes),0) minutes,COALESCE(SUM(calories_burned),0) calories FROM exercises WHERE user_id=?');$s->execute([$userId]);return $s->fetch()?:['records'=>0,'minutes'=>0,'calories'=>0]; }
    public function export(int $userId,array $filters): array { return $this->list($userId,$filters)['records']; }
}
