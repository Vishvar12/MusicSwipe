<?php
declare(strict_types=1);

/**
 * GET api/get_tracks.php
 * Réponse : { ok: true, data: { tracks: [...], remaining: int } }
 */
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/tracks.php';

$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'error' => 'Méthode non autorisée.'], 405);
}

try {
    json_response([
        'ok'   => true,
        'data' => [
            'tracks'    => get_next_tracks($pdo, $userId),
            'remaining' => count_remaining_tracks($pdo, $userId),
        ],
    ]);
} catch (PDOException $e) {
    error_log('MusicSwipe get_tracks : ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Impossible de charger les morceaux.'], 500);
}
