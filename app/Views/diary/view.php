<?php use App\Core\Csrf;
use App\Core\View;

$emoji = [
    "Happy" => "😊",
    "Sad" => "😔",
    "Excited" => "🤩",
    "Anxious" => "😟",
    "Calm" => "😌",
    "Angry" => "😠",
    "Grateful" => "🙏",
    "Tired" => "😴",
    "Stressed" => "😣",
    "Neutral" => "😐",
];
?>
<section class="detail-hero">
    <div>
        <div class="detail-meta">
            <span class="mood-pill mood-<?= strtolower(
                View::e($entry["mood"]),
            ) ?>">
                <?= $emoji[$entry["mood"]] ?>
                <?= View::e($entry["mood"]) ?>
            </span>
            <span>
                <i class="bi bi-calendar3">
                </i>
                <?= View::e(date("d M Y", strtotime($entry["entry_date"]))) ?>
            </span>
            <?php if ($entry["is_favorite"]): ?>
            <span>
                <i class="bi bi-star-fill text-warning">
                </i> Favourite</span>
                <?php endif; ?>
            </div>
            <h1>
                <?= View::e($entry["title"]) ?>
            </h1>
            <p>Mood score: <?= (int) $entry["mood_score"] ?>/10</p>
            </div>
            <div class="detail-actions">
                <a class="btn btn-light" href="<?= View::e(
                    $baseUrl,
                ) ?>/diary/edit?id=<?= (int) $entry["entry_id"] ?>">
                    <i class="bi bi-pencil">
                    </i> Edit</a>
                    <a class="btn btn-outline-light" href="<?= View::e(
                        $baseUrl,
                    ) ?>/diary">
                        <i class="bi bi-arrow-left">
                        </i> Back</a>
                    </div>
                </section>
                <div class="row g-3">
                    <div class="col-lg-8">
                        <?php if ($entry["image_path"]): ?>
                        <section class="content-card p-0 overflow-hidden mb-3">
                            <img class="diary-entry-image" src="<?= View::e(
                                $baseUrl,
                            ) ?>/uploads/diary/<?= View::e(
                                $entry["image_path"],
                            ) ?>" alt="Journal memory">
                        </section>
                        <?php endif; ?>
                        <article class="content-card diary-reading-card">
                            <p class="card-kicker">Your reflection</p>
                                <?= nl2br(View::e($entry["content"])) ?>
                            </article>
                        </div>
                        <aside class="col-lg-4">
                            <section class="content-card danger-zone">
                                <p class="card-kicker">Danger zone</p>
                                    <h2>Delete Reflection</h2>
                                        <p class="text-muted">This permanently removes your reflection and photo.</p>
                                            <form method="post" action="<?= View::e(
                                                $baseUrl,
                                            ) ?>/diary/delete" onsubmit="return confirm('Delete this reflection?')">
                                                <input type="hidden" name="_csrf" value="<?= View::e(
                                                    Csrf::token(),
                                                ) ?>">
                                                <input type="hidden" name="entry_id" value="<?= (int) $entry[
                                                    "entry_id"
                                                ] ?>">
                                                <button class="btn btn-outline-danger">
                                                    <i class="bi bi-trash3">
                                                    </i> Delete reflection</button>
                                                </form>
                                            </section>
                                        </aside>
                                    </div>
