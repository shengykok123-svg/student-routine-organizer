<?php
use App\Core\Csrf;
use App\Core\View;

$moods = [
    'Happy' => ['&#x1F60A;', 'Sunny and positive', '#f6a928'],
    'Sad' => ['&#x1F614;', 'A quieter moment', '#6686c7'],
    'Excited' => ['&#x1F929;', 'Full of energy', '#ec7b38'],
    'Anxious' => ['&#x1F61F;', 'Taking it gently', '#a272ca'],
    'Calm' => ['&#x1F60C;', 'Peaceful and grounded', '#3aa6a0'],
    'Angry' => ['&#x1F620;', 'Strong feelings', '#e05b55'],
    'Grateful' => ['&#x1F970;', 'Heart full', '#d9789a'],
    'Tired' => ['&#x1F634;', 'Time to recharge', '#7781a8'],
    'Stressed' => ['&#x1F623;', 'A lot on my mind', '#d06775'],
    'Neutral' => ['&#x1F610;', 'Steady and balanced', '#6b8ca1'],
];
$mood = $moods[$entry['mood']] ?? $moods['Neutral'];
$score = max(1, min(10, (int) $entry['mood_score']));
?>

<div class="journal-reader" style="--journal-accent:<?= View::e($mood[2]) ?>;--mood-score:<?= $score * 10 ?>%">
    <nav class="journal-reader-nav">
        <a href="<?= View::e($baseUrl) ?>/diary"><i class="bi bi-arrow-left"></i> All reflections</a>
        <span><i class="bi bi-lock-fill"></i> Private journal</span>
    </nav>

    <header class="journal-cover">
        <div class="journal-cover-main">
            <div class="journal-date"><strong><?= View::e(date('d', strtotime($entry['entry_date']))) ?></strong><span><?= View::e(strtoupper(date('M', strtotime($entry['entry_date'])))) ?></span></div>
            <div>
                <div class="journal-tags"><span class="journal-mood"><?= $mood[0] ?> <?= View::e($entry['mood']) ?></span><?php if ($entry['is_favorite']): ?><span class="journal-favourite"><i class="bi bi-star-fill"></i> Favourite memory</span><?php endif; ?></div>
                <p class="journal-overline">My reflection · <?= View::e(date('l', strtotime($entry['entry_date']))) ?></p>
                <h1><?= View::e($entry['title']) ?></h1>
                <p class="journal-caption"><?= View::e($mood[1]) ?></p>
            </div>
        </div>
        <div class="journal-actions"><a class="btn journal-edit" href="<?= View::e($baseUrl) ?>/diary/edit?id=<?= (int) $entry['entry_id'] ?>"><i class="bi bi-pencil-square"></i> Edit entry</a></div>
    </header>

    <div class="journal-layout">
        <main class="journal-paper">
            <?php if ($entry['image_path']): ?><figure class="journal-photo"><img src="<?= View::e($baseUrl) ?>/uploads/diary/<?= View::e($entry['image_path']) ?>" alt="Memory attached to this reflection"><figcaption><i class="bi bi-camera"></i> A memory from this day</figcaption></figure><?php endif; ?>
            <article>
                <div class="journal-section-title"><span><i class="bi bi-quote"></i></span><div><p>Today's reflection</p><h2>What was on my mind</h2></div></div>
                <div class="journal-copy"><?= nl2br(View::e($entry['content'])) ?></div>
            </article>
            <footer class="journal-signoff"><span></span><i class="bi bi-heart-fill"></i><span></span><p>One day, one thought, one step forward.</p></footer>
        </main>

        <aside class="journal-side">
            <section class="journal-insight">
                <p class="journal-kicker">Mood check-in</p>
                <div class="journal-mood-summary"><span><?= $mood[0] ?></span><div><strong><?= View::e($entry['mood']) ?></strong><small><?= View::e($mood[1]) ?></small></div></div>
                <div class="journal-score-label"><span>How I felt</span><strong><?= $score ?><small>/10</small></strong></div>
                <div class="journal-score" role="img" aria-label="Mood score <?= $score ?> out of 10"><span></span></div>
                <div class="journal-score-ends"><span>Low</span><span>Great</span></div>
            </section>
            <section class="journal-prompt"><i class="bi bi-lightbulb"></i><div><strong>Keep reflecting</strong><p>Small moments become meaningful memories over time.</p></div></section>
            <details class="journal-options"><summary><i class="bi bi-three-dots"></i> Entry options</summary><div><p>Deleting permanently removes this reflection<?= $entry['image_path'] ? ' and its photo' : '' ?>.</p><form method="post" action="<?= View::e($baseUrl) ?>/diary/delete" onsubmit="return confirm('Delete this reflection permanently?')"><input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>"><input type="hidden" name="entry_id" value="<?= (int) $entry['entry_id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i> Delete reflection</button></form></div></details>
        </aside>
    </div>
</div>
