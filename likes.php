<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/likes.php';

$userId = require_login();

$likes = [];
$loadError = false;
try {
    $likes = get_user_likes($pdo, $userId);
} catch (PDOException $e) {
    error_log('MusicSwipe likes : ' . $e->getMessage());
    $loadError = true;
}

$count = count($likes);
$countLabel = static fn (int $n): string => $n . ($n > 1 ? ' morceaux' : ' morceau');

$pageTitle = 'Mes likes';
$activeNav = 'likes';
$pageScripts = ['assets/js/likes.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="likes" aria-labelledby="page-title" data-likes
         data-api-note="api/update_note.php" data-api-delete="api/delete_like.php">
    <header class="likes__header">
        <h1 id="page-title" class="heading" tabindex="-1">Mes likes</h1>
        <?php if (!$loadError): ?>
            <span class="badge" data-likes-count <?= $count === 0 ? 'hidden' : '' ?>><?= e($countLabel($count)) ?></span>
        <?php endif; ?>
    </header>

    <?php if ($loadError): ?>
        <!-- État : erreur -->
        <div class="state-block" role="alert">
            <span class="state-block__icon state-block__icon--error"><?= icon('alert', 32) ?></span>
            <h2 class="subtitle">Impossible de charger ta playlist</h2>
            <p class="text-secondary">Vérifie ta connexion puis réessaie.</p>
            <a class="btn btn--secondary" href="likes.php"><?= icon('refresh', 18) ?><span>Réessayer</span></a>
        </div>
    <?php else: ?>
        <!-- État : vide (affiché aussi par likes.js quand le dernier morceau est retiré) -->
        <div class="state-block" data-likes-empty <?= $count > 0 ? 'hidden' : '' ?>>
            <span class="state-block__icon"><?= icon('music', 32) ?></span>
            <h2 class="subtitle">Aucun morceau liké pour l'instant</h2>
            <p class="text-secondary">Swipe à droite sur un morceau pour le retrouver ici.</p>
            <a class="btn btn--primary" href="swipe.php">Commencer à swiper</a>
        </div>

        <!-- État : rempli -->
        <ul class="likes__list" data-likes-list <?= $count === 0 ? 'hidden' : '' ?>>
            <?php foreach ($likes as $like):
                $id = (int) $like['id'];
                $note = $like['personal_note'];
                $addedAt = date('d/m', strtotime((string) $like['added_at']));
                ?>
                <li class="track-row" data-track-row data-track-id="<?= $id ?>"
                    data-title="<?= e($like['title']) ?>" data-artist="<?= e($like['artist']) ?>"
                    data-preview="<?= e($like['preview_url']) ?>" data-note="<?= e($note ?? '') ?>">
                    <div class="track-row__top">
                        <span class="track-row__cover">
                            <?= icon('music', 24) ?>
                            <img src="<?= e($like['cover_url']) ?>" alt="" width="56" height="56" loading="lazy" data-cover>
                        </span>
                        <div class="track-row__info">
                            <p class="track-row__title"><?= e($like['title']) ?></p>
                            <p class="text-small text-secondary"><?= e($like['artist']) ?> · ajouté le <?= e($addedAt) ?></p>
                            <span class="badge"><?= e($like['genre']) ?></span>
                        </div>
                        <button class="track-row__play" type="button" data-play aria-pressed="false"
                                aria-label="Écouter <?= e($like['title']) ?>">
                            <span class="track-row__icon-play"><?= icon('play', 18) ?></span>
                            <span class="track-row__icon-pause"><?= icon('pause', 18) ?></span>
                        </button>
                    </div>

                    <div class="track-row__bottom" data-note-view>
                        <p class="track-row__note<?= $note === null ? ' is-empty' : '' ?>" data-note-text>
                            <?= $note === null ? 'Ajouter une note…' : '« ' . e($note) . ' »' ?>
                        </p>
                        <button class="icon-btn" type="button" data-edit aria-label="Modifier la note de <?= e($like['title']) ?>">
                            <?= icon('pencil', 20) ?>
                        </button>
                        <button class="icon-btn icon-btn--danger" type="button" data-delete aria-label="Retirer <?= e($like['title']) ?> de Mes likes">
                            <?= icon('trash', 20) ?>
                        </button>
                    </div>

                    <form class="track-row__editor" data-note-form hidden>
                        <label class="visually-hidden" for="note-<?= $id ?>">Note personnelle pour <?= e($like['title']) ?></label>
                        <textarea class="field__input" id="note-<?= $id ?>" name="note" rows="2"
                                  maxlength="<?= NOTE_MAX_LENGTH ?>" data-note-input
                                  aria-describedby="note-<?= $id ?>-count"><?= e($note ?? '') ?></textarea>
                        <p class="text-small text-secondary" id="note-<?= $id ?>-count" data-note-count></p>
                        <p class="field__error" data-note-error role="alert" hidden><?= icon('alert', 16) ?><span></span></p>
                        <div class="track-row__editor-actions">
                            <button class="btn btn--ghost btn--sm" type="button" data-cancel>Annuler</button>
                            <button class="btn btn--primary btn--sm" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Modale de confirmation de suppression -->
    <dialog class="modal" data-delete-dialog aria-labelledby="delete-title" aria-describedby="delete-desc">
        <h2 class="subtitle" id="delete-title">Retirer « <span data-delete-name></span> » de Mes likes ?</h2>
        <p class="text-secondary" id="delete-desc">Ce morceau ne te sera plus proposé au swipe. Cette action est définitive.</p>
        <p class="field__error" data-delete-error role="alert" hidden><?= icon('alert', 16) ?><span></span></p>
        <div class="modal__actions">
            <button class="btn btn--secondary" type="button" data-delete-cancel>Annuler</button>
            <button class="btn btn--danger" type="button" data-delete-confirm>Retirer</button>
        </div>
    </dialog>

    <div class="toast" data-toast role="status" hidden>
        <span class="toast__icon toast__icon--success"><?= icon('check', 20) ?></span>
        <span class="toast__icon toast__icon--warning"><?= icon('alert', 20) ?></span>
        <span data-toast-text></span>
    </div>

    <audio data-audio preload="none"></audio>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
