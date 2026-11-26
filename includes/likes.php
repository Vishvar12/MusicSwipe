<?php
declare(strict_types=1);

/**
 * Playlist « Mes likes » (US-05, US-07, US-08).
 * Chaque requête passe par la jointure playlists.user_id = utilisateur connecté :
 * un utilisateur ne peut ni lire, ni modifier, ni supprimer les likes d'un autre.
 */

const NOTE_MAX_LENGTH = 255;
const LIKES_PER_PAGE = 10;
const SEARCH_MAX_LENGTH = 100;

/**
 * Tris autorisés (liste blanche). La clause SQL vient d'ici, jamais de l'URL.
 */
const LIKE_SORTS = [
    'recent' => ['label' => 'Plus récents',  'sql' => 'pt.added_at DESC, t.id DESC'],
    'oldest' => ['label' => 'Plus anciens',  'sql' => 'pt.added_at ASC, t.id ASC'],
    'artist' => ['label' => 'Artiste A→Z',   'sql' => 't.artist ASC, t.title ASC'],
    'title'  => ['label' => 'Titre A→Z',     'sql' => 't.title ASC, t.artist ASC'],
];

/**
 * Lit et valide les critères de $_GET (US-06). Toute valeur invalide est remplacée par la valeur par défaut.
 *
 * @param int[] $genreIds identifiants de genres existants
 * @return array{q: string, genre: ?int, sort: string, page: int}
 */
function read_like_filters(array $query, array $genreIds): array
{
    $q = is_string($query['q'] ?? null) ? trim($query['q']) : '';
    $q = mb_substr($q, 0, SEARCH_MAX_LENGTH);

    $genre = filter_var($query['genre'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $genre = ($genre !== false && in_array($genre, $genreIds, true)) ? $genre : null;

    $sort = is_string($query['sort'] ?? null) && isset(LIKE_SORTS[$query['sort']]) ? $query['sort'] : 'recent';

    $page = filter_var($query['page'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return ['q' => $q, 'genre' => $genre, 'sort' => $sort, 'page' => $page === false ? 1 : $page];
}

/**
 * Vrai si une recherche ou un filtre réduit la liste.
 */
function like_filters_active(array $filters): bool
{
    return $filters['q'] !== '' || $filters['genre'] !== null;
}

/**
 * Nombre total de morceaux dans la playlist de l'utilisateur (sans filtre).
 */
function count_user_likes(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         WHERE p.user_id = :uid'
    );
    $stmt->execute(['uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Recherche paginée dans la playlist de l'utilisateur.
 * Les valeurs saisies passent uniquement par des paramètres liés ; la clause ORDER BY vient de LIKE_SORTS.
 *
 * @return array{items: array, total: int, page: int, pages: int}
 */
function search_user_likes(PDO $pdo, int $userId, array $filters): array
{
    $where = 'p.user_id = :uid';
    $params = ['uid' => $userId];

    if ($filters['q'] !== '') {
        // % et _ sont des jokers en SQL : on les neutralise pour chercher le texte tel quel
        $pattern = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $filters['q']) . '%';
        $where .= " AND (t.title LIKE :q_title ESCAPE '|' OR t.artist LIKE :q_artist ESCAPE '|')";
        $params['q_title'] = $pattern;
        $params['q_artist'] = $pattern;
    }
    if ($filters['genre'] !== null) {
        $where .= ' AND t.genre_id = :genre';
        $params['genre'] = $filters['genre'];
    }

    $from = 'FROM playlist_tracks pt
             JOIN playlists p ON p.id = pt.playlist_id
             JOIN tracks t ON t.id = pt.track_id
             JOIN genres g ON g.id = t.genre_id
             WHERE ' . $where;

    $stmt = $pdo->prepare('SELECT COUNT(*) ' . $from);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $pages = max(1, (int) ceil($total / LIKES_PER_PAGE));
    $page = min($filters['page'], $pages); // page trop grande → dernière page

    $stmt = $pdo->prepare(
        'SELECT t.id, t.title, t.artist, t.cover_url, t.preview_url, g.name AS genre,
                pt.personal_note, pt.added_at '
        . $from
        . ' ORDER BY ' . LIKE_SORTS[$filters['sort']]['sql']
        . ' LIMIT :lim OFFSET :off'
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue('lim', LIKES_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue('off', ($page - 1) * LIKES_PER_PAGE, PDO::PARAM_INT);
    $stmt->execute();

    return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
}

/**
 * URL de likes.php avec les critères actuels (valeurs par défaut omises) et les changements demandés.
 */
function likes_url(array $filters, array $changes = []): string
{
    $f = array_merge($filters, $changes);
    $query = array_filter([
        'q'     => $f['q'] !== '' ? $f['q'] : null,
        'genre' => $f['genre'],
        'sort'  => $f['sort'] !== 'recent' ? $f['sort'] : null,
        'page'  => $f['page'] > 1 ? $f['page'] : null,
    ], static fn ($v) => $v !== null);

    return 'likes.php' . ($query ? '?' . http_build_query($query) : '');
}

/**
 * Vrai si le morceau est dans la playlist de CET utilisateur.
 */
function user_has_like(PDO $pdo, int $userId, int $trackId): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         WHERE p.user_id = :uid AND pt.track_id = :tid'
    );
    $stmt->execute(['uid' => $userId, 'tid' => $trackId]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Modifie (ou efface si null) la note personnelle d'un morceau de la playlist de l'utilisateur.
 */
function update_like_note(PDO $pdo, int $userId, int $trackId, ?string $note): void
{
    $stmt = $pdo->prepare(
        'UPDATE playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         SET pt.personal_note = :note
         WHERE p.user_id = :uid AND pt.track_id = :tid'
    );
    $stmt->execute(['note' => $note, 'uid' => $userId, 'tid' => $trackId]);
}

/**
 * Retire un morceau de la playlist de l'utilisateur. Retourne le nombre de lignes supprimées (0 ou 1).
 * L'historique des swipes est conservé (le morceau n'est pas reproposé).
 */
function delete_like(PDO $pdo, int $userId, int $trackId): int
{
    $stmt = $pdo->prepare(
        'DELETE pt FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         WHERE p.user_id = :uid AND pt.track_id = :tid'
    );
    $stmt->execute(['uid' => $userId, 'tid' => $trackId]);
    return $stmt->rowCount();
}

/**
 * Lit le corps JSON d'une requête API (ou $_POST en secours) et valide track_id.
 *
 * @return array{0: int, 1: array} [track_id, données reçues] — arrête la requête (400) si invalide
 */
function read_track_request(): array
{
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }
    $trackId = filter_var($input['track_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($trackId === false) {
        json_response(['ok' => false, 'error' => 'Requête invalide.'], 400);
    }
    return [$trackId, $input];
}
