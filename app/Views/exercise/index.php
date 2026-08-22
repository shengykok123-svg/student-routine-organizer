<?php use App\Core\View;
use App\Services\ExerciseValidationService;

$activityIcons = [
    "Jogging" => "bi-lightning-charge",
    "Badminton" => "badminton-racket",
    "Walking" => "bi-person-walking",
    "Cycling" => "bi-bicycle",
    "Gym" => "gym-dumbbell",
    "Swimming" => "bi-water",
];

?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Routine fitness</p>
            <h1>Exercise Tracker</h1>
                <div class="metric-line">
                    <span>
                        <i class="bi bi-activity">
                        </i>
                        <strong>
                            <?= (int) $analytics["records"] ?>
                        </strong> workouts</span>
                        <span>
                            <i class="bi bi-clock">
                            </i>
                            <strong>
                                <?= (int) $analytics["minutes"] ?>
                            </strong> minutes</span>
                            <span>
                                <i class="bi bi-fire">
                                </i>
                                <strong>
                                    <?= number_format(
                                        (float) $analytics["calories"],
                                        0,
                                    ) ?>
                                </strong> calories</span>
                            </div>
                        </div>
                        <a class="btn btn-primary" href="<?= View::e(
                            $baseUrl,
                        ) ?>/exercise/create">
                            <i class="bi bi-plus-lg">
                            </i> Add Exercise</a>
                        </section>
                        <section class="filter-panel mb-3">
                            <form method="get" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label" for="search">Search</label>
                                        <input class="form-control" id="search" name="search" placeholder="Search activity..." value="<?= View::e(
                                            $filters["search"],
                                        ) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="activity">Activity</label>
                                            <select class="form-select" id="activity" name="activity">
                                                <option value="">All Activities</option>
                                                    <?php foreach (
                                                        $activities as $activity
                                                    ): ?>
                                                    <option <?= $filters[
                                                        "activity"
                                                    ] === $activity
                                                        ? "selected"
                                                        : "" ?>>
                                                        <?= View::e(
                                                            $activity,
                                                        ) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="sort">Sort By</label>
                                                    <select class="form-select" id="sort" name="sort">
                                                        <?php foreach (
                                                            ExerciseValidationService::SORTS as $key => $label
                                                        ): ?>
                                                        <option value="<?= View::e(
                                                            $key,
                                                        ) ?>" <?= $filters[
    "sort"
] === $key
    ? "selected"
    : "" ?>>
                                                            <?= View::e(
                                                                $label,
                                                            ) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex gap-2">
                                                    <button class="btn btn-outline-primary flex-grow-1">
                                                        <i class="bi bi-funnel">
                                                        </i> Filter</button>
                                                        <a class="btn btn-outline-secondary" href="<?= View::e(
                                                            $baseUrl,
                                                        ) ?>/exercise/export?<?= http_build_query(
                                                            $filters,
                                                        ) ?>" title="CSV Export">
                                                            <i class="bi bi-file-earmark-spreadsheet">
                                                            </i>
                                                        </a>
                                                    </div>
                                                </form>
                                            </section>
                                            <section class="content-card p-0 overflow-hidden">
                                                <div class="table-responsive">
                                                    <table class="table app-table align-middle mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Activity</th>
                                                                    <th>Duration</th>
                                                                        <th>Calories</th>
                                                                            <th>Date</th>
                                                                                <th class="text-end">Action</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                foreach (
                                                                                    $result[
                                                                                        "records"
                                                                                    ] as $record
                                                                                ): ?>
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="d-flex align-items-center gap-2">
                                                                                            <?php $activityIcon = $activityIcons[$record["activity_type"]] ?? "bi-activity"; ?>
                                                                                            <span class="activity-badge" title="<?= View::e($record["activity_type"]) ?>">
                                                                                                <?php if ($activityIcon === "badminton-racket"): ?>
                                                                                                    <svg viewBox="0 0 24 24" width="18" height="18" role="img" aria-label="Badminton racket" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                                        <ellipse cx="9" cy="8.5" rx="4.5" ry="6" transform="rotate(-35 9 8.5)"></ellipse>
                                                                                                        <path d="M12.4 13.2 18.5 19.3M17.2 18l1.8 1.8M18.6 19.4l1.2-1.2"></path>
                                                                                                    </svg>
                                                                                                <?php elseif ($activityIcon === "gym-dumbbell"): ?>
                                                                                                    <svg viewBox="0 0 24 24" width="18" height="18" role="img" aria-label="Dumbbell" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                                                        <path d="m7 8-2 2m0 4 2 2m10-8 2 2m0 4-2 2M7 12h10"></path>
                                                                                                        <path d="M3.5 7.5v9M6 6v12M18 6v12M20.5 7.5v9"></path>
                                                                                                    </svg>
                                                                                                <?php else: ?>
                                                                                                    <i class="bi <?= View::e($activityIcon) ?>" aria-hidden="true"></i>
                                                                                                <?php endif; ?>
                                                                                            </span>
                                                                                            <strong>
                                                                                                <?= View::e(
                                                                                                    $record[
                                                                                                        "activity_type"
                                                                                                    ],
                                                                                                ) ?>
                                                                                            </strong>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <i class="bi bi-clock me-1 text-muted">
                                                                                        </i>
                                                                                        <?= (int) $record[
                                                                                            "duration_minutes"
                                                                                        ] ?> min</td>
                                                                                        <td>
                                                                                            <i class="bi bi-fire me-1 text-warning">
                                                                                            </i>
                                                                                            <?= number_format(
                                                                                                (float) $record[
                                                                                                    "calories_burned"
                                                                                                ],
                                                                                                0,
                                                                                            ) ?> kcal</td>
                                                                                            <td>
                                                                                                <i class="bi bi-calendar3 me-1 text-muted">
                                                                                                </i>
                                                                                                <?= View::e(
                                                                                                    date(
                                                                                                        "d M Y",
                                                                                                        strtotime(
                                                                                                            $record[
                                                                                                                "exercise_date"
                                                                                                            ],
                                                                                                        ),
                                                                                                    ),
                                                                                                ) ?>
                                                                                            </td>
                                                                                            <td class="text-end">
                                                                                                <a class="btn btn-sm btn-outline-primary" href="<?= View::e(
                                                                                                    $baseUrl,
                                                                                                ) ?>/exercise/view?id=<?= (int) $record[
    "exercise_id"
] ?>">View</a>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <?php endforeach;
if (
    !$result[
        "records"
    ]
): ?>
                                                                                            <tr>
                                                                                                <td colspan="5">
                                                                                                    <div class="empty-state">
                                                                                                        <div class="empty-icon">
                                                                                                            <i class="bi bi-activity">
                                                                                                            </i>
                                                                                                        </div>
                                                                                                        <h3>No exercises found</h3>
                                                                                                            <p>Record your first workout to start tracking progress.</p>
                                                                                                                <a class="btn btn-primary" href="<?= View::e(
                                                                                                                    $baseUrl,
                                                                                                                ) ?>/exercise/create">Add exercise</a>
                                                                                                                </div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        <?php endif;
?>
                                                                                                    </tbody>
                                                                                                </table>
                                                                                            </div>
                                                                                        </section>
