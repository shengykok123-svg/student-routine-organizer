<?php

declare(strict_types=1);

namespace App\Services;

/** Validates and stores exercise attachments. */
final class ExerciseAttachmentService
{
    private const ALLOWED = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "application/pdf" => "pdf",
    ];

    /** @return array{stored_name:string,original_name:string,mime_type:string,file_size:int}|null */
    public function upload(array $file): ?array
    {
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (
            ($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK ||
            !is_uploaded_file((string) ($file["tmp_name"] ?? ""))
        ) {
            throw new \RuntimeException(
                "The file could not be uploaded.",
            );
        }
        if (
            (int) ($file["size"] ?? 0) < 1 ||
            (int) $file["size"] > 5 * 1024 * 1024
        ) {
            throw new \RuntimeException("File must be 5 MB or smaller.");
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file(
            (string) $file["tmp_name"],
        );
        $extension = self::ALLOWED[$mime] ?? null;
        if ($extension === null) {
            throw new \RuntimeException(
                "File must be a genuine JPG, PNG, or PDF file.",
            );
        }
        $directory = SRO_ROOT . "/public/uploads/exercise-evidence";
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException("File storage is unavailable.");
        }
        $stored = bin2hex(random_bytes(20)) . "." . $extension;
        if (
            !move_uploaded_file(
                (string) $file["tmp_name"],
                $directory . "/" . $stored,
            )
        ) {
            throw new \RuntimeException("File could not be stored.");
        }
        return [
            "stored_name" => $stored,
            "original_name" => mb_substr(
                basename((string) ($file["name"] ?? "evidence." . $extension)),
                0,
                180,
            ),
            "mime_type" => $mime,
            "file_size" => (int) $file["size"],
        ];
    }

    public function remove(?string $storedName): void
    {
        if (
            $storedName &&
            preg_match('/^[a-f0-9]{40}\.(jpg|png|pdf)$/', $storedName)
        ) {
            @unlink(
                SRO_ROOT . "/public/uploads/exercise-evidence/" . $storedName,
            );
        }
    }
}
