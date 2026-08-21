<?php

declare(strict_types=1);

namespace App\Services;

final class MoneyReceiptUploadService
{
    private const MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function upload(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) throw new \RuntimeException('The receipt image could not be uploaded.');
        if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > self::MAX_BYTES) throw new \RuntimeException('Receipt images must be 5 MB or smaller.');
        $temporaryPath = (string) $file['tmp_name'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $extension = self::MIMES[$mime] ?? null;
        if ($extension === null || @getimagesize($temporaryPath) === false) throw new \RuntimeException('Use a genuine JPG, PNG, or WebP receipt image.');
        $directory = SRO_ROOT . '/public/uploads/money';
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new \RuntimeException('Receipt storage is unavailable.');
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . '/' . $name)) throw new \RuntimeException('The receipt image could not be saved.');
        return $name;
    }

    public function remove(?string $name): void
    {
        $path = $this->path($name);
        if ($path !== null) @unlink($path);
    }

    public function path(?string $name): ?string
    {
        if (!$name || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $name)) return null;
        return SRO_ROOT . '/public/uploads/money/' . $name;
    }
}
