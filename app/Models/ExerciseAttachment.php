<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ExerciseAttachment
{
    public function __construct(private readonly PDO $pdo) {}
    public function findForExercise(int $exerciseId,int $userId): ?array { $s=$this->pdo->prepare('SELECT a.* FROM exercise_attachments a JOIN exercises e ON e.exercise_id=a.exercise_id WHERE a.exercise_id=? AND e.user_id=?');$s->execute([$exerciseId,$userId]);return $s->fetch()?:null; }
    public function save(int $exerciseId,int $userId,array $file): ?array { $old=$this->findForExercise($exerciseId,$userId);$check=$this->pdo->prepare('SELECT exercise_id FROM exercises WHERE exercise_id=? AND user_id=?');$check->execute([$exerciseId,$userId]);if(!$check->fetch())return null;$s=$this->pdo->prepare('INSERT INTO exercise_attachments (exercise_id,stored_name,original_name,mime_type,file_size) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE stored_name=VALUES(stored_name),original_name=VALUES(original_name),mime_type=VALUES(mime_type),file_size=VALUES(file_size)');$s->execute([$exerciseId,$file['stored_name'],$file['original_name'],$file['mime_type'],$file['file_size']]);return $old; }
    public function delete(int $attachmentId,int $userId): ?array { $s=$this->pdo->prepare('SELECT a.* FROM exercise_attachments a JOIN exercises e ON e.exercise_id=a.exercise_id WHERE a.attachment_id=? AND e.user_id=?');$s->execute([$attachmentId,$userId]);$item=$s->fetch();if(!$item)return null;$d=$this->pdo->prepare('DELETE FROM exercise_attachments WHERE attachment_id=?');$d->execute([$attachmentId]);return $item; }
}
