<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/genres.php';
require __DIR__ . '/includes/stats.php';

$userId = require_login();

$errors = [];
$pseudo = (string) ($_SESSION['pseudo'] ?? '');
$selected = [];

// --- Modification du profil (US-10) : identité tirée de la session uniquement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $pseudo = is_string($_POST['pseudo'] ?? null) ? trim($_POST['pseudo']) : '';
    $selected = filter_valid_genre_ids($pdo, $_POST['genres'] ?? []);

    $length = mb_strlen($pseudo);
    if ($length < 2 || $length > 30) {
        $errors['pseudo'] = 'Le pseudo doit contenir entre 2 et 30 caractères.';
    }
    if ($selected === []) {
        $errors['genres'] = 'Choisis au moins un genre.';
    }

    if (!$errors) {
        try {
            // Pseudo + genres : tout ou rien
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE users SET pseudo = :pseudo WHERE id = :uid');
            $stmt->execute(['pseudo' => $pseudo, 'uid' => $userId]);
            save_user_genres($pdo, $userId, $selected);
            $pdo->commit();

            $_SESSION['pseudo'] = $pseudo;
            $_SESSION['flash'] = 'Profil mis à jour';
            redirect('profil.php'); // Post/Redirect/Get : un rechargement ne renvoie pas le formulaire
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('MusicSwipe profil : ' . $e->getMessage());
            $errors['form'] = 'Une erreur est survenue. Réessaie dans un instant.';
        }
    }
}

// --- Lecture du dashboard (US-09) ---
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$stats = null;
$genres = [];
$loadError = false;
try {
    $stats = get_user_stats($pdo, $userId);
    $genres = get_all_genres($pdo);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $selected = get_user_genre_ids($pdo, $userId);
    }
} catch (PDOException $e) {
    error_log('MusicSwipe profil (lecture) : ' . $e->getMessage());
    $loadError = true;
}

$displayPseudo = (string) ($_SESSION['pseudo'] ?? '');
$initial = mb_strtoupper(mb_substr($displayPseudo, 0, 1));

$pageTitle = 'Profil';
$activeNav = 'profil';
$pageScripts = ['assets/js/onboarding.js', 'assets/js/forms.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="profile" aria-labelledby="page-title">
    <header class="profile__header">
        <span class="profile__avatar" aria-hidden="true"><?= e($initial) ?></span>
        <div>
            <h1 id="page-title" class="subtitle"><?= e($displayPseudo) ?></h1>
            <?php if ($stats !== null): ?>
                <p class="text-small text-secondary">Membre depuis <?= e($stats['member_since']) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($flash !== null): ?>
        <p class="alert alert--success" role="status"><?= icon('check', 20) ?><span><?= e($flash) ?></span></p>
    <?php endif; ?>

    <?php if ($loadError): ?>
        <!-- État : erreur -->
        <div class="state-block" role="alert">
            <span class="state-block__icon state-block__icon--error"><?= icon('alert', 32) ?></span>
            <h2 class="subtitle">Impossible de charger ton profil</h2>
            <p class="text-secondary">Vérifie ta connexion puis réessaie.</p>
            <a class="btn btn--secondary" href="profil.php"><?= icon('refresh', 18) ?><span>Réessayer</span></a>
        </div>
    <?php else: ?>
        <!-- Dashboard -->
        <section class="profile__section" aria-labelledby="stats-title">
            <h2 id="stats-title" class="subtitle">Mes statistiques</h2>
            <dl class="stats">
                <div class="stat-card">
                    <dt class="stat-card__label">Swipes</dt>
                    <dd class="stat-card__value"><?= $stats['swipes'] ?></dd>
                </div>
                <div class="stat-card">
                    <dt class="stat-card__label">Likes</dt>
                    <dd class="stat-card__value"><?= $stats['likes'] ?></dd>
                </div>
                <div class="stat-card">
                    <dt class="stat-card__label">Dislikes</dt>
                    <dd class="stat-card__value"><?= $stats['dislikes'] ?></dd>
                </div>
                <div class="stat-card">
                    <dt class="stat-card__label">Taux de like</dt>
                    <dd class="stat-card__value"><?= $stats['like_rate'] === null ? '—' : $stats['like_rate'] . ' %' ?></dd>
                </div>
            </dl>

            <?php if ($stats['swipes'] === 0): ?>
                <!-- État : vide -->
                <div class="profile__empty">
                    <p class="text-secondary">Tu n'as encore rien swipé. Tes statistiques apparaîtront ici.</p>
                    <a class="btn btn--primary btn--block" href="swipe.php">Commencer à swiper</a>
                </div>
            <?php endif; ?>

            <dl class="profile__facts">
                <div>
                    <dt class="text-secondary">Genre le plus liké</dt>
                    <dd><?= $stats['top_genre'] === null ? '<span class="text-secondary">—</span>' : '<span class="badge">' . e($stats['top_genre']) . '</span>' ?></dd>
                </div>
                <div>
                    <dt class="text-secondary">Genres préférés</dt>
                    <dd class="profile__badges">
                        <?php foreach ($stats['preferred_genres'] as $name): ?>
                            <span class="badge"><?= e($name) ?></span>
                        <?php endforeach; ?>
                        <?php if ($stats['preferred_genres'] === []): ?>
                            <span class="text-secondary">—</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Modification du profil (Update) -->
        <section class="profile__section" aria-labelledby="edit-title">
            <h2 id="edit-title" class="subtitle">Modifier mon profil</h2>

            <?php if (isset($errors['form'])): ?>
                <p class="alert alert--error" role="alert"><?= icon('alert', 20) ?><span><?= e($errors['form']) ?></span></p>
            <?php endif; ?>

            <form class="form" method="post" action="profil.php" data-loading-text="Enregistrement…" data-genre-form>
                <?= csrf_field() ?>
                <?= form_field('pseudo', 'Pseudo', 'text', $pseudo, $errors['pseudo'] ?? null,
                    'required minlength="2" maxlength="30" autocomplete="nickname"') ?>

                <p class="field__label" id="genres-label">Genres préférés</p>
                <?php
                $genreError = $errors['genres'] ?? null;
                $genreLegend = 'Genres préférés';
                require __DIR__ . '/includes/genre_picker.php';
                ?>

                <button class="btn btn--primary btn--block" type="submit">Enregistrer les modifications</button>
            </form>
        </section>
    <?php endif; ?>

    <form action="logout.php" method="post" class="profile__logout">
        <?= csrf_field() ?>
        <button class="btn btn--ghost btn--block" type="submit"><?= icon('logout', 18) ?><span>Se déconnecter</span></button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
