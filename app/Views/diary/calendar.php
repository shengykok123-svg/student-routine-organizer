<?php use App\Core\View;

$days = (int) $first->format("t");
$offset = (int) $first->format("N") - 1;
$previous = $first->modify("-1 month");
$next = $first->modify("+1 month");
?>
<section class="page-heading">
    <div>
        <p class="page-eyebrow">Diary journal</p>
            <h1>Journal Calendar</h1>
                <p class="page-subtitle">Browse your reflections month by month.</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="<?= View::e(
                        $baseUrl,
                    ) ?>/diary">
                        <i class="bi bi-arrow-left">
                        </i> Journal</a>
                        <a class="btn btn-primary" href="<?= View::e(
                            $baseUrl,
                        ) ?>/diary/create">
                            <i class="bi bi-plus-lg">
                            </i> New Reflection</a>
                        </div>
                    </section>
                    <section class="calendar-wrap">
                        <div class="calendar-toolbar">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= View::e(
                                $baseUrl,
                            ) ?>/diary/calendar?month=<?= $previous->format(
                                "n",
                            ) ?>&year=<?= $previous->format("Y") ?>">
                                <i class="bi bi-chevron-left">
                                </i> Previous</a>
                                <strong>
                                    <?= View::e($first->format("F Y")) ?>
                                </strong>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= View::e(
                                    $baseUrl,
                                ) ?>/diary/calendar?month=<?= $next->format(
                                    "n",
                                ) ?>&year=<?= $next->format("Y") ?>">Next <i class="bi bi-chevron-right">
                                </i>
                            </a>
                        </div>
                        <div class="calendar-grid">
                            <?php
                            foreach (
                                [
                                    "Mon",
                                    "Tue",
                                    "Wed",
                                    "Thu",
                                    "Fri",
                                    "Sat",
                                    "Sun",
                                ] as $day
                            ): ?>
                            <div class="calendar-weekday">
                                <?= $day ?>
                            </div>
                            <?php endforeach;
for ($x = 0; $x < $offset; $x++): ?>
                            <div class="calendar-cell">
                            </div>
                            <?php endfor;
for ($day = 1; $day <= $days; $day++):
    $date =
        $first->format("Y-m-") .
        str_pad(
            (string) $day,
            2,
            "0",
            STR_PAD_LEFT,
        ); ?>
                            <div class="calendar-cell <?= $date ===
                            date("Y-m-d")
                                ? "is-today"
                                : "" ?>">
                                <div class="d-flex justify-content-between">
                                    <span class="calendar-date">
                                        <?= $day ?>
                                    </span>
                                    <?php if ($date <= date("Y-m-d")): ?>
                                    <a href="<?= View::e(
                                        $baseUrl,
                                    ) ?>/diary/create" aria-label="Add diary entry">
                                        <i class="bi bi-plus-circle">
                                        </i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php foreach (
                                    $entries[$date] ?? [] as $entry
                                ): ?>
                                <a class="calendar-entry mood-<?= strtolower(
                                    View::e($entry["mood"]),
                                ) ?>" href="<?= View::e(
                                    $baseUrl,
                                ) ?>/diary/view?id=<?= (int) $entry["entry_id"] ?>">
                                    <?= View::e($entry["title"]) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php
endfor;
?>
                        </div>
                    </section>
