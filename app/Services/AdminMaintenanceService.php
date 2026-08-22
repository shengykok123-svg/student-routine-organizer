<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/** Reports system storage and removes safely identified orphan uploads. */
final class AdminMaintenanceService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function storageSummary(): array
    {
        $summary = [];
        foreach ($this->uploadSources() as $name => $source) {
            $files = $this->files($source["directory"]);
            $summary[$name] = [
                "files" => count($files),
                "bytes" => array_sum(array_map("filesize", $files)),
            ];
        }
        return $summary;
    }

    public function cleanOrphans(): int
    {
        $removed = 0;
        $minimumAge = time() - 86400;
        foreach ($this->uploadSources() as $source) {
            $statement = $this->pdo->query($source["query"]);
            $referenced = array_flip(
                array_filter(array_map("strval", $statement->fetchAll(PDO::FETCH_COLUMN))),
            );
            foreach ($this->files($source["directory"]) as $file) {
                if (!isset($referenced[basename($file)]) && filemtime($file) < $minimumAge && @unlink($file)) {
                    $removed++;
                }
            }
        }
        return $removed;
    }

    private function uploadSources(): array
    {
        return [
            "Profile pictures" => [
                "directory" => SRO_ROOT . "/public/uploads/profile",
                "query" => "SELECT profile_image_path FROM users WHERE profile_image_path IS NOT NULL",
            ],
            "Diary photos" => [
                "directory" => SRO_ROOT . "/public/uploads/diary",
                "query" => "SELECT image_path FROM diary_entries WHERE image_path IS NOT NULL",
            ],
            "Money receipts" => [
                "directory" => SRO_ROOT . "/public/uploads/money",
                "query" => "SELECT receipt_path FROM money_records WHERE receipt_path IS NOT NULL",
            ],
        ];
    }

    /** @return list<string> */
    private function files(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }
        return array_values(array_filter(
            glob($directory . "/*") ?: [],
            static fn (string $path): bool => is_file($path) && basename($path) !== ".htaccess",
        ));
    }
}
