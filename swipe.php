<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$userId = require_login();

// Sans genre préféré, l'utilisateur passe d'abord par l'onboarding (US-03)
if (user_home($pdo, $userId) === 'onboarding.php') {
    redirect('onboarding.php');
}

$pageTitle = 'Découvrir';
$activeNav = 'swipe';
$pageScripts = ['assets/js/player.js', 'assets/js/swipe.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="swipe" aria-labelledby="page-title"
         data-swipe data-api-tracks="api/get_tracks.php" data-api-swipe="api/swipe_action.php">
    <header class="swipe__header">
        <h1 id="page-title" class="heading">Découvrir</h1>
        <p class="text-small text-secondary" data-remaining>Chargement…</p>
    </header>

    <p class="visually-hidden" aria-live="polite" data-announce></p>

    <div class="swipe__stage">
        <!-- État : chargement -->
        <div class="track-card track-card--skeleton" data-view="loading" aria-hidden="true">
            <span class="skeleton skeleton--cover"></span>
            <span class="skeleton skeleton--title"></span>
            <span class="skeleton skeleton--text"></span>
            <span class="skeleton skeleton--player"></span>
        </div>

        <!-- État : carte prête -->
        <article class="track-card" data-view="ready" data-card hidden aria-labelledby="track-title">
            <span class="track-card__stamp track-card__stamp--like" aria-hidden="true">J'AIME</span>
            <span class="track-card__stamp track-card__stamp--dislike" aria-hidden="true">JE PASSE</span>

            <div class="track-card__cover">
                <span class="track-card__cover-fallback"><?= icon('music', 96) ?></span>
                <img data-track-cover src="" alt="" width="310" height="310" draggable="false" hidden>
            </div>

            <h2 class="track-card__title" id="track-title" data-track-title></h2>
            <p class="track-card__meta">
                <span class="track-card__artist" data-track-artist></span>
                <span class="badge" data-track-genre></span>
            </p>

            <div class="player" data-player data-state="idle">
                <button class="player__toggle" type="button" data-player-toggle aria-label="Écouter l'extrait">
                    <span class="player__icon player__icon--play"><?= icon('play', 20) ?></span>
                    <span class="player__icon player__icon--pause"><?= icon('pause', 20) ?></span>
                    <span class="player__icon player__icon--loading"><?= icon('refresh', 20) ?></span>
                </button>
                <div class="player__body">
                    <div class="player__bar" role="progressbar" aria-label="Progression de l'extrait"
                         aria-valuemin="0" aria-valuemax="30" aria-valuenow="0" data-player-bar>
                        <span class="player__fill" data-player-fill></span>
                    </div>
                    <p class="player__times text-small text-secondary">
                        <span data-player-current>0:00</span><span>0:30</span>
                    </p>
                    <p class="player__error text-small" data-player-error hidden>
                        <?= icon('alert', 16) ?><span>Extrait indisponible — tu peux quand même swiper.</span>
                    </p>
                </div>
            </div>

            <div class="alert alert--error track-card__error" data-swipe-error role="alert" hidden>
                <?= icon('alert', 20) ?>
                <span data-swipe-error-text>Ton choix n'a pas pu être enregistré.</span>
                <button class="btn btn--secondary btn--sm" type="button" data-swipe-retry>Réessayer</button>
            </div>
        </article>

        <!-- État : vide -->
        <div class="state-block" data-view="empty" hidden>
            <span class="state-block__icon state-block__icon--success"><?= icon('check', 32) ?></span>
            <h2 class="subtitle">Tu as tout découvert !</h2>
            <p class="text-secondary">Reviens plus tard ou élargis tes genres préférés.</p>
            <div class="state-block__actions">
                <a class="btn btn--primary" href="likes.php">Voir Mes likes</a>
                <a class="btn btn--secondary" href="onboarding.php">Modifier mes genres</a>
            </div>
        </div>

        <!-- État : erreur de chargement -->
        <div class="state-block" data-view="error" role="alert" hidden>
            <span class="state-block__icon state-block__icon--error"><?= icon('alert', 32) ?></span>
            <h2 class="subtitle">Impossible de charger les morceaux</h2>
            <p class="text-secondary">Vérifie ta connexion puis réessaie.</p>
            <button class="btn btn--secondary" type="button" data-reload><?= icon('refresh', 18) ?><span>Réessayer</span></button>
        </div>
    </div>

    <div class="swipe__actions" data-swipe-controls>
        <div class="swipe__action">
            <button class="swipe-btn swipe-btn--dislike" type="button" data-swipe-dislike aria-label="Je passe" disabled>
                <?= icon('x', 28) ?>
            </button>
            <span class="text-small text-secondary" aria-hidden="true">Je passe</span>
        </div>
        <div class="swipe__action">
            <button class="swipe-btn swipe-btn--like" type="button" data-swipe-like aria-label="J'aime" disabled>
                <?= icon('heart', 28) ?>
            </button>
            <span class="text-small text-secondary" aria-hidden="true">J'aime</span>
        </div>
    </div>
    <p class="swipe__hint text-small text-secondary" data-swipe-controls>
        Glisse la carte ou utilise les flèches ← →. Espace : écouter / pause.
    </p>

    <div class="toast" data-toast role="status" hidden>
        <?= icon('check', 20) ?><span data-toast-text></span>
    </div>

    <audio data-audio preload="none"></audio>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
