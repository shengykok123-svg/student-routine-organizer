<?php

declare(strict_types=1);

namespace App\Services;

/** Creates confirmation tokens for potential duplicate workouts. */
final class ExerciseDuplicateConfirmationService
{
    public function issue(array $data): string
    {
        $token = bin2hex(random_bytes(24));
        $_SESSION["_exercise_duplicates"][$token] = [
            "fingerprint" => $this->fingerprint($data),
            "expires" => time() + 600,
        ];
        return $token;
    }

    public function confirm(mixed $token, array $data): bool
    {
        $item = is_string($token)
            ? $_SESSION["_exercise_duplicates"][$token] ?? null
            : null;
        unset($_SESSION["_exercise_duplicates"][$token]);
        return is_array($item) &&
            (int) ($item["expires"] ?? 0) >= time() &&
            hash_equals(
                (string) ($item["fingerprint"] ?? ""),
                $this->fingerprint($data),
            );
    }

    private function fingerprint(array $data): string
    {
        ksort($data);
        return hash(
            "sha256",
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            ),
        );
    }
}
