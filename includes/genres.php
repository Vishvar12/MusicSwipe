<?php
declare(strict_types=1);

/**
 * Genres musicaux et genres préférés de l'utilisateur.
 * Utilisé par l'onboarding (T2) et la modification du profil (T6).
 */

/**
 * Tous les genres, triés par nom : [ ['id' => int, 'name' => string], ... ]
 */
function get_all_genres(PDO $pdo): array
{
    return $pdo->query('SELECT id, name FROM genres ORDER BY name')->fetchAll();
}

/**
 * Identifiants des genres préférés d'un utilisateur.
 *
 * @return int[]
 */
function get_user_genre_ids(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT genre_id FROM user_genres WHERE user_id = :uid');
    $stmt->execute(['uid' => $userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/**
 * Ne garde, parmi les valeurs reçues du client, que des identifiants de genres existants (sans doublon).
 *
 * @return int[]
 */
function filter_valid_genre_ids(PDO $pdo, mixed $submitted): array
{
    if (!is_array($submitted)) {
        return [];
    }
    $existing = array_map('intval', array_column(get_all_genres($pdo), 'id'));

    $ids = [];
    foreach ($submitted as $value) {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id !== false && in_array($id, $existing, true)) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

/**
 * Remplace les genres préférés de l'utilisateur (tout ou rien).
 *
 * @param int[] $genreIds identifiants déjà validés par filter_valid_genre_ids()
 */
function save_user_genres(PDO $pdo, int $userId, array $genreIds): void
{
    // Si l'appelant a déjà ouvert une transaction (profil : pseudo + genres), on s'y intègre
    $ownTransaction = !$pdo->inTransaction();
    if ($ownTransaction) {
        $pdo->beginTransaction();
    }
    try {
        $stmt = $pdo->prepare('DELETE FROM user_genres WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);

        $stmt = $pdo->prepare('INSERT INTO user_genres (user_id, genre_id) VALUES (:uid, :gid)');
        foreach ($genreIds as $genreId) {
            $stmt->execute(['uid' => $userId, 'gid' => $genreId]);
        }
        if ($ownTransaction) {
            $pdo->commit();
        }
    } catch (PDOException $e) {
        if ($ownTransaction) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Libellé du compteur : « 0 genre choisi », « 1 genre choisi », « 3 genres choisis ».
 */
function genre_count_label(int $count): string
{
    return $count . ($count > 1 ? ' genres choisis' : ' genre choisi');
}
