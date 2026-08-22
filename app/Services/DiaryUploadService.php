<?php

declare(strict_types=1);

namespace App\Services;

/** Validates and stores diary image uploads. */
final class DiaryUploadService
{
    private const MIMES = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];
    public function upload(array $file): ?string
    {
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (
            ($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK ||
            !is_uploaded_file((string) ($file["tmp_name"] ?? ""))
        ) {
            throw new \RuntimeException("The photo could not be uploaded.");
        }
        if ((int) ($file["size"] ?? 0) > 5 * 1024 * 1024) {
            throw new \RuntimeException("Photo must be 5 MB or smaller.");
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file(
            (string) $file["tmp_name"],
        );
        $ext = self::MIMES[$mime] ?? null;
        if (!$ext) {
            throw new \RuntimeException(
                "Use a genuine JPG, PNG, or WebP photo.",
            );
        }
        $dir = SRO_ROOT . "/public/uploads/diary";
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException("Photo storage is unavailable.");
        }
        $name = bin2hex(random_bytes(16)) . "." . $ext;
        if (
            !move_uploaded_file((string) $file["tmp_name"], $dir . "/" . $name)
        ) {
            throw new \RuntimeException("The photo could not be saved.");
        }
        return $name;
    }
    public function remove(?string $name): void
    {
        if ($name && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $name)) {
            @unlink(SRO_ROOT . "/public/uploads/diary/" . $name);
        }
    }
}
