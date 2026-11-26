<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/genres.php';

$userId = require_login();

$genres = get_all_genres($pdo);
$selected = get_user_genre_ids($pdo, $userId);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Les identifiants inexistants ou invalides sont ignorés (US-03, cas limite)
    $selected = filter_valid_genre_ids($pdo, $_POST['genres'] ?? []);

    if ($selected === []) {
        $error = 'Choisis au moins un genre pour continuer.';
    } else {
        try {
            save_user_genres($pdo, $userId, $selected);
            redirect('swipe.php');
        } catch (PDOException $e) {
            error_log('MusicSwipe onboarding : ' . $e->getMessage());
            $error = 'Une erreur est survenue. Réessaie dans un instant.';
        }
    }
}

$pageTitle = 'Tes genres préférés';
$activeNav = null;
$pageScripts = ['assets/js/onboarding.js', 'assets/js/forms.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="onboarding" aria-labelledby="page-title">
    <span class="logo">
        <span class="logo__mark"><?= icon('music', 20) ?></span>
        <span class="logo__text">MusicSwipe</span>
    </span>

    <div class="onboarding__intro">
        <h1 id="page-title">Qu'est-ce que tu écoutes ?</h1>
        <p class="text-secondary">Choisis au moins un genre. Tu pourras les modifier plus tard dans ton profil.</p>
    </div>

    <?php if ($genres === []): ?>
        <p class="alert alert--error" role="alert"><?= icon('alert', 20) ?><span>Aucun genre disponible pour le moment. Réessaie plus tard.</span></p>
    <?php else: ?>
        <form class="form" method="post" action="onboarding.php" data-loading-text="Enregistrement…" data-genre-form>
            <?= csrf_field() ?>
            <?php
            $genreError = $error;
            $genreLegend = 'Genres musicaux';
            require __DIR__ . '/includes/genre_picker.php';
            ?>

            <button class="btn btn--primary btn--block" type="submit">Continuer</button>
        </form>
    <?php endif; ?>

    <form class="onboarding__logout" action="logout.php" method="post">
        <?= csrf_field() ?>
        <button class="btn btn--ghost btn--sm" type="submit">Se déconnecter</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
