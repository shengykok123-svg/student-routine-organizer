<?php

declare(strict_types=1);

namespace App\Services;

/** Validates and stores student profile pictures. */
final class ProfileImageUploadService
{
    private const MIMES = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function upload(array $file): ?string
    {
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (
            ($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK ||
            !is_uploaded_file((string) ($file["tmp_name"] ?? ""))
        ) {
            throw new \RuntimeException(
                "The profile picture could not be uploaded.",
            );
        }
        if (
            (int) ($file["size"] ?? 0) < 1 ||
            (int) $file["size"] > self::MAX_BYTES
        ) {
            throw new \RuntimeException(
                "Profile pictures must be 5 MB or smaller.",
            );
        }

        $temporaryPath = (string) $file["tmp_name"];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $extension = self::MIMES[$mime] ?? null;
        if ($extension === null || @getimagesize($temporaryPath) === false) {
            throw new \RuntimeException(
                "Use a genuine JPG, PNG, or WebP profile picture.",
            );
        }

        $directory = SRO_ROOT . "/public/uploads/profile";
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException(
                "Profile picture storage is unavailable.",
            );
        }
        $name = bin2hex(random_bytes(16)) . "." . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . "/" . $name)) {
            throw new \RuntimeException(
                "The profile picture could not be saved.",
            );
        }
        return $name;
    }

    public function path(?string $name): ?string
    {
        if (!$name || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $name)) {
            return null;
        }
        return SRO_ROOT . "/public/uploads/profile/" . $name;
    }

    public function remove(?string $name): void
    {
        $path = $this->path($name);
        if ($path !== null) {
            @unlink($path);
        }
    }
}
