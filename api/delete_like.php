<?php
declare(strict_types=1);

/**
 * POST api/delete_like.php  (JSON { track_id }, en-tête X-CSRF-Token)
 * Réponse : { ok: true, data: { track_id, count } } — count = morceaux restants dans la playlist
 * L'utilisateur vient UNIQUEMENT de la session.
 */
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/likes.php';

$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Méthode non autorisée.'], 405);
}
csrf_check();

[$trackId] = read_track_request();

try {
    if (delete_like($pdo, $userId, $trackId) === 0) {
        json_response(['ok' => false, 'error' => "Ce morceau n'est pas dans ta playlist."], 404);
    }
    json_response(['ok' => true, 'data' => [
        'track_id' => $trackId,
        'count'    => count(get_user_likes($pdo, $userId)),
    ]]);
} catch (PDOException $e) {
    error_log('MusicSwipe delete_like : ' . $e->getMessage());
    json_response(['ok' => false, 'error' => "Le morceau n'a pas pu être retiré."], 500);
}
