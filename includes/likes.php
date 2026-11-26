<?php
declare(strict_types=1);

/**
 * Playlist « Mes likes » (US-05, US-07, US-08).
 * Chaque requête passe par la jointure playlists.user_id = utilisateur connecté :
 * un utilisateur ne peut ni lire, ni modifier, ni supprimer les likes d'un autre.
 */

const NOTE_MAX_LENGTH = 255;

/**
 * Morceaux likés par l'utilisateur, du plus récent au plus ancien.
 */
function get_user_likes(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT t.id, t.title, t.artist, t.cover_url, t.preview_url, g.name AS genre,
                pt.personal_note, pt.added_at
         FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id AND p.user_id = :uid
         JOIN tracks t ON t.id = pt.track_id
         JOIN genres g ON g.id = t.genre_id
         ORDER BY pt.added_at DESC, t.id DESC'
    );
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchAll();
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
