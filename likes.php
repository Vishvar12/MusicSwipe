<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/likes.php';
require __DIR__ . '/includes/genres.php';

$userId = require_login();

$genres = [];
$filters = ['q' => '', 'genre' => null, 'sort' => 'recent', 'page' => 1];
$result = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$count = 0;
$loadError = false;
try {
    $genres = get_all_genres($pdo);
    // Critères lus dans l'URL ($_GET) et validés par liste blanche (US-06)
    $filters = read_like_filters($_GET, array_map('intval', array_column($genres, 'id')));
    $count = count_user_likes($pdo, $userId);
    $result = search_user_likes($pdo, $userId, $filters);
    $filters['page'] = $result['page'];
} catch (PDOException $e) {
    error_log('MusicSwipe likes : ' . $e->getMessage());
    $loadError = true;
}

$likes = $result['items'];
$filtered = like_filters_active($filters);
// États : error | empty (aucun like) | noresult (filtres sans résultat) | list
$view = $loadError ? 'error' : ($count === 0 ? 'empty' : ($result['total'] === 0 ? 'noresult' : 'list'));

$countLabel = static fn (int $n): string => $n . ($n > 1 ? ' morceaux' : ' morceau');
$summary = static fn (array $r): string => $r['total'] . ($r['total'] > 1 ? ' résultats' : ' résultat')
    . ' · page ' . $r['page'] . ' sur ' . $r['pages'];

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
        <?php if ($count > 0): ?>
            <!-- Recherche, filtre et tri : formulaire GET, critères conservés dans l'URL -->
            <form class="likes-filters" method="get" action="likes.php" role="search" data-likes-filters data-hide-when-empty>
                <div class="likes-filters__search">
                    <label class="visually-hidden" for="f-q">Rechercher un titre ou un artiste</label>
                    <span class="likes-filters__icon"><?= icon('search', 20) ?></span>
                    <input class="field__input" id="f-q" type="search" name="q" value="<?= e($filters['q']) ?>"
                           maxlength="<?= SEARCH_MAX_LENGTH ?>" placeholder="Rechercher un titre, un artiste">
                </div>
                <div class="likes-filters__row">
                    <label class="visually-hidden" for="f-genre">Genre</label>
                    <select class="field__input" id="f-genre" name="genre" data-autosubmit>
                        <option value="">Genre : tous</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?= (int) $genre['id'] ?>" <?= $filters['genre'] === (int) $genre['id'] ? 'selected' : '' ?>>
                                <?= e($genre['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="visually-hidden" for="f-sort">Trier par</label>
                    <select class="field__input" id="f-sort" name="sort" data-autosubmit>
                        <?php foreach (LIKE_SORTS as $key => $sort): ?>
                            <option value="<?= e($key) ?>" <?= $filters['sort'] === $key ? 'selected' : '' ?>><?= e($sort['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn--primary" type="submit">Rechercher</button>
                </div>
                <?php if ($filtered): ?>
                    <a class="likes-filters__reset text-small" href="likes.php">Réinitialiser les filtres</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <?php if ($view === 'list'): ?>
            <p class="text-small text-secondary" data-likes-summary data-hide-when-empty
               data-total="<?= $result['total'] ?>" data-page="<?= $result['page'] ?>" data-pages="<?= $result['pages'] ?>">
                <?= e($summary($result)) ?>
            </p>
        <?php endif; ?>

        <!-- État : aucun résultat (des likes existent, mais aucun ne correspond) -->
        <?php if ($view === 'noresult'): ?>
            <div class="state-block" role="status">
                <span class="state-block__icon"><?= icon('search', 32) ?></span>
                <h2 class="subtitle">Aucun morceau ne correspond à ta recherche</h2>
                <p class="text-secondary">Essaie un autre mot-clé ou un autre genre.</p>
                <a class="btn btn--secondary" href="likes.php">Réinitialiser les filtres</a>
            </div>
        <?php endif; ?>

        <!-- État : vide (affiché aussi par likes.js quand le dernier morceau est retiré) -->
        <div class="state-block" data-likes-empty <?= $view === 'empty' ? '' : 'hidden' ?>>
            <span class="state-block__icon"><?= icon('music', 32) ?></span>
            <h2 class="subtitle">Aucun morceau liké pour l'instant</h2>
            <p class="text-secondary">Swipe à droite sur un morceau pour le retrouver ici.</p>
            <a class="btn btn--primary" href="swipe.php">Commencer à swiper</a>
        </div>

        <!-- État : chargement (affiché par likes.js pendant une nouvelle recherche ou un changement de page) -->
        <ul class="likes__list" data-likes-skeleton aria-hidden="true" hidden>
            <?php for ($i = 0; $i < 3; $i++): ?>
                <li class="track-row track-row--skeleton">
                    <span class="skeleton skeleton--thumb"></span>
                    <span class="track-row__skeleton-lines">
                        <span class="skeleton skeleton--line"></span>
                        <span class="skeleton skeleton--line-short"></span>
                    </span>
                </li>
            <?php endfor; ?>
        </ul>

        <!-- État : rempli -->
        <ul class="likes__list" data-likes-list <?= $view === 'list' ? '' : 'hidden' ?>>
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

        <?php if ($view === 'list' && $result['pages'] > 1): ?>
            <nav class="pagination" aria-label="Pagination de Mes likes" data-likes-pagination data-hide-when-empty>
                <?php if ($result['page'] > 1): ?>
                    <a class="pagination__link" href="<?= e(likes_url($filters, ['page' => $result['page'] - 1])) ?>" aria-label="Page précédente">
                        <?= icon('chevronLeft', 20) ?>
                    </a>
                <?php else: ?>
                    <span class="pagination__link is-disabled" aria-hidden="true"><?= icon('chevronLeft', 20) ?></span>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                    <?php if ($p === $result['page']): ?>
                        <a class="pagination__link is-current" href="<?= e(likes_url($filters, ['page' => $p])) ?>" aria-current="page"
                           aria-label="Page <?= $p ?>, page actuelle"><?= $p ?></a>
                    <?php else: ?>
                        <a class="pagination__link" href="<?= e(likes_url($filters, ['page' => $p])) ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($result['page'] < $result['pages']): ?>
                    <a class="pagination__link" href="<?= e(likes_url($filters, ['page' => $result['page'] + 1])) ?>" aria-label="Page suivante">
                        <?= icon('chevronRight', 20) ?>
                    </a>
                <?php else: ?>
                    <span class="pagination__link is-disabled" aria-hidden="true"><?= icon('chevronRight', 20) ?></span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
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
