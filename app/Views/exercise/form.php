<?php use App\Core\Csrf;
use App\Core\View;
use App\Services\ExerciseValidationService;

$isEdit = isset($record["exercise_id"]);
$currentActivity = (string) ($record["activity_type"] ?? "");
$isCustomActivity =
    $currentActivity !== "" &&
    !in_array($currentActivity, ExerciseValidationService::ACTIVITIES, true);
$selectedActivity = $isCustomActivity ? "Others" : $currentActivity;
$customActivity = $isCustomActivity
    ? $currentActivity
    : (string) ($record["other_activity_type"] ?? "");
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Exercise tracker</p>
            <h1>
                <?= $isEdit ? "Edit Exercise" : "Add Exercise" ?>
            </h1>
            <p class="page-subtitle">Log the details of your workout.</p>
            </div>
            <a class="btn btn-outline-secondary" href="<?= View::e(
                $baseUrl,
            ) ?>/exercise">
                <i class="bi bi-arrow-left">
                </i> Back</a>
            </section>
            <?php foreach ($errors as $error): ?>
            <div class="app-flash flash-error">
                <i class="bi bi-exclamation-circle-fill">
                </i>
                <?= View::e($error) ?>
            </div>
            <?php endforeach; ?>
            <section class="form-panel">
                <form method="post" action="<?= View::e(
                    $baseUrl,
                ) ?>/exercise/<?= $isEdit ? "update" : "store" ?>">
                    <input type="hidden" name="_csrf" value="<?= View::e(
                        Csrf::token(),
                    ) ?>">
                    <?php
                    if ($isEdit): ?>
                    <input type="hidden" name="exercise_id" value="<?= (int) $record[
                        "exercise_id"
                    ] ?>">
                    <?php endif;
if (isset($duplicateToken)): ?>
                    <input type="hidden" name="duplicate_token" value="<?= View::e(
                        $duplicateToken,
                    ) ?>">
                    <?php endif;
?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label" for="activity_type">Activity <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="activity_type" name="activity_type">
                                <?php foreach (
                                    ExerciseValidationService::ACTIVITIES as $activity
                                ): ?>
                                <option <?= $selectedActivity === $activity
                                    ? "selected"
                                    : "" ?>>
                                    <?= View::e($activity) ?>
                                </option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6<?= $selectedActivity === "Others" ? "" : " invisible" ?>" id="custom-activity-field">
                                <label class="form-label" for="other_activity_type">Custom activity <span class="text-muted">(if Others)</span>
                                </label>
                                <input class="form-control" id="other_activity_type" name="other_activity_type" value="<?= View::e(
                                    $customActivity,
                                ) ?>" placeholder="Describe your activity">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="duration_minutes">Duration (minutes) <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" id="duration_minutes" type="number" min="1" name="duration_minutes" value="<?= View::e(
                                    $record["duration_minutes"] ?? "",
                                ) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="calories_burned">Calories burned <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" id="calories_burned" type="number" step="0.01" min="0" name="calories_burned" value="<?= View::e(
                                    $record["calories_burned"] ?? "",
                                ) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="exercise_date">Date <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" id="exercise_date" type="date" max="<?= date(
                                    "Y-m-d",
                                ) ?>" name="exercise_date" value="<?= View::e(
                                    $record["exercise_date"] ?? date("Y-m-d"),
                                ) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Notes <span class="text-muted">(optional)</span>
                                </label>
                                <textarea class="form-control" id="notes" rows="5" name="notes" maxlength="500" placeholder="How did the workout feel?">
                                    <?= View::e($record["notes"] ?? "") ?>
                                </textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a class="btn btn-outline-secondary" href="<?= View::e(
                                $baseUrl,
                            ) ?>/exercise">Cancel</a>
                                <button class="btn btn-primary">
                                    <i class="bi bi-check2-circle">
                                    </i> Save Exercise</button>
                                </div>
                            </form>
                        </section>
                        <script>
                            (() => {
                                const activitySelect = document.getElementById("activity_type");
                                const customField = document.getElementById("custom-activity-field");
                                const customInput = document.getElementById("other_activity_type");

                                if (!activitySelect || !customField || !customInput) return;

                                const updateCustomActivityField = () => {
                                    const isCustomActivity = activitySelect.value === "Others";
                                    customField.classList.toggle("invisible", !isCustomActivity);
                                    customInput.disabled = !isCustomActivity;
                                    customInput.required = isCustomActivity;
                                };

                                activitySelect.addEventListener("change", updateCustomActivityField);
                                updateCustomActivityField();
                            })();
                        </script>
