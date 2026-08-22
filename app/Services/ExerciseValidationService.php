<?php

declare(strict_types=1);

namespace App\Services;

/** Validates the exercise form payload. */
final class ExerciseValidationService
{
    public const ACTIVITIES = [
        "Walking",
        "Jogging",
        "Cycling",
        "Gym",
        "Swimming",
        "Badminton",
    ];
    public const SORTS = [
        "newest" => "Newest first",
        "oldest" => "Oldest first",
        "duration" => "Longest duration",
        "calories" => "Highest calories",
    ];

    /** @return array{data:array<string,mixed>,errors:list<string>} */
    public function validate(array $input): array
    {
        $errors = [];
        $activity = trim((string) ($input["activity_type"] ?? ""));
        if ($activity === "Other") {
            $activity = trim((string) ($input["other_activity_type"] ?? ""));
        }
        $duration = filter_var(
            $input["duration_minutes"] ?? null,
            FILTER_VALIDATE_INT,
        );
        $calories = filter_var(
            $input["calories_burned"] ?? null,
            FILTER_VALIDATE_FLOAT,
        );
        $date = trim((string) ($input["exercise_date"] ?? ""));
        $notes = trim((string) ($input["notes"] ?? ""));
        $dateObject = \DateTimeImmutable::createFromFormat("!Y-m-d", $date);
        if ($activity === "" || mb_strlen($activity) > 100) {
            $errors[] =
                "Choose an activity or enter a custom activity of 100 characters or fewer.";
        }
        if ($duration === false || $duration < 1 || $duration > 1440) {
            $errors[] =
                "Duration must be a whole number from 1 to 1,440 minutes.";
        }
        if ($calories === false || $calories < 0 || $calories > 20000) {
            $errors[] = "Calories must be a number from 0 to 20,000.";
        }
        if (
            !$dateObject ||
            $dateObject->format("Y-m-d") !== $date ||
            $dateObject > new \DateTimeImmutable("today")
        ) {
            $errors[] =
                "Choose a valid exercise date that is not in the future.";
        }
        if (mb_strlen($notes) > 500) {
            $errors[] = "Notes must be 500 characters or fewer.";
        }
        return [
            "data" => [
                "activity_type" => $activity,
                "duration_minutes" => (int) $duration,
                "calories_burned" => round((float) $calories, 2),
                "exercise_date" => $date,
                "notes" => $notes ?: null,
            ],
            "errors" => $errors,
        ];
    }
}
