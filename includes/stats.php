<?php
declare(strict_types=1);

/**
 * Statistiques du dashboard (US-09). Toutes les requêtes sont filtrées par l'utilisateur connecté.
 *
 * Règles (PROJECT.md §6) :
 *  - swipes   = nombre de lignes dans swipes
 *  - likes    = morceaux actuellement dans « Mes likes » (playlist_tracks)
 *  - dislikes = swipes « dislike »
 *  - taux     = swipes « like » / swipes (l'historique ne bouge pas si un morceau est retiré)
 */

/**
 * @return array{swipes: int, likes: int, dislikes: int, like_rate: ?int, top_genre: ?string,
 *               preferred_genres: string[], member_since: string}
 */
function get_user_stats(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS swipes,
                COALESCE(SUM(action = 'like'), 0) AS liked,
                COALESCE(SUM(action = 'dislike'), 0) AS disliked
         FROM swipes WHERE user_id = :uid"
    );
    $stmt->execute(['uid' => $userId]);
    $swipes = $stmt->fetch();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         WHERE p.user_id = :uid'
    );
    $stmt->execute(['uid' => $userId]);
    $likes = (int) $stmt->fetchColumn();

    // Genre le plus présent dans « Mes likes » ; égalité → ordre alphabétique
    $stmt = $pdo->prepare(
        'SELECT g.name FROM playlist_tracks pt
         JOIN playlists p ON p.id = pt.playlist_id
         JOIN tracks t ON t.id = pt.track_id
         JOIN genres g ON g.id = t.genre_id
         WHERE p.user_id = :uid
         GROUP BY g.id, g.name
         ORDER BY COUNT(*) DESC, g.name ASC
         LIMIT 1'
    );
    $stmt->execute(['uid' => $userId]);
    $topGenre = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT g.name FROM user_genres ug
         JOIN genres g ON g.id = ug.genre_id
         WHERE ug.user_id = :uid
         ORDER BY g.name'
    );
    $stmt->execute(['uid' => $userId]);
    $preferred = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = :uid');
    $stmt->execute(['uid' => $userId]);
    $createdAt = (string) $stmt->fetchColumn();

    $total = (int) $swipes['swipes'];

    return [
        'swipes'           => $total,
        'likes'            => $likes,
        'dislikes'         => (int) $swipes['disliked'],
        'like_rate'        => $total > 0 ? (int) round(((int) $swipes['liked'] / $total) * 100) : null,
        'top_genre'        => $topGenre === false ? null : (string) $topGenre,
        'preferred_genres' => $preferred,
        'member_since'     => format_month_year($createdAt),
    ];
}

/**
 * « novembre 2026 » à partir d'une date SQL (sans dépendre de l'extension intl).
 */
function format_month_year(string $date): string
{
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
               'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $timestamp = strtotime($date) ?: time();
    return $months[(int) date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
}
