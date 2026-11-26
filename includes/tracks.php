<?php
declare(strict_types=1);

/**
 * Morceaux, swipes et playlist « Mes likes ».
 * Toutes les requêtes sont filtrées par l'utilisateur connecté (passé par l'appelant depuis la session).
 */

/**
 * Identifiant de la playlist « Mes likes » de l'utilisateur (créée si elle manque).
 */
function get_user_playlist_id(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT id FROM playlists WHERE user_id = :uid ORDER BY id LIMIT 1');
    $stmt->execute(['uid' => $userId]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int) $id;
    }

    $stmt = $pdo->prepare('INSERT INTO playlists (user_id, name) VALUES (:uid, :name)');
    $stmt->execute(['uid' => $userId, 'name' => 'Mes likes']);
    return (int) $pdo->lastInsertId();
}

/**
 * Nombre de morceaux que l'utilisateur n'a pas encore swipés.
 */
function count_remaining_tracks(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM tracks t
         WHERE NOT EXISTS (SELECT 1 FROM swipes s WHERE s.track_id = t.id AND s.user_id = :uid)'
    );
    $stmt->execute(['uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Prochains morceaux à proposer : jamais swipés, genres préférés en premier.
 */
function get_next_tracks(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT t.id, t.title, t.artist, t.cover_url, t.preview_url, g.name AS genre,
                (ug.genre_id IS NOT NULL) AS preferred
         FROM tracks t
         JOIN genres g ON g.id = t.genre_id
         LEFT JOIN user_genres ug ON ug.genre_id = t.genre_id AND ug.user_id = :uid_pref
         WHERE NOT EXISTS (SELECT 1 FROM swipes s WHERE s.track_id = t.id AND s.user_id = :uid_swipe)
         ORDER BY preferred DESC, t.id ASC
         LIMIT :lim'
    );
    $stmt->bindValue('uid_pref', $userId, PDO::PARAM_INT);
    $stmt->bindValue('uid_swipe', $userId, PDO::PARAM_INT);
    $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(static fn (array $row): array => [
        'id'          => (int) $row['id'],
        'title'       => $row['title'],
        'artist'      => $row['artist'],
        'cover_url'   => $row['cover_url'],
        'preview_url' => $row['preview_url'],
        'genre'       => $row['genre'],
        'preferred'   => (bool) $row['preferred'],
    ], $stmt->fetchAll());
}
