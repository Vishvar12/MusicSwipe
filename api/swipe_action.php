<?php
declare(strict_types=1);

/**
 * POST api/swipe_action.php  (JSON { track_id, action: "like"|"dislike" }, en-tête X-CSRF-Token)
 * Réponse : { ok: true, data: { action, already, remaining } }
 * L'utilisateur vient UNIQUEMENT de la session : tout user_id envoyé par le client est ignoré.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/tracks.php';

$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Méthode non autorisée.'], 405);
}
csrf_check();

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$trackId = filter_var($input['track_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action  = $input['action'] ?? null;

if ($trackId === false || !in_array($action, ['like', 'dislike'], true)) {
    json_response(['ok' => false, 'error' => 'Requête invalide.'], 400);
}

try {
    $stmt = $pdo->prepare('SELECT 1 FROM tracks WHERE id = :id');
    $stmt->execute(['id' => $trackId]);
    if (!$stmt->fetchColumn()) {
        json_response(['ok' => false, 'error' => 'Ce morceau est introuvable.'], 404);
    }

    $already = false;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO swipes (user_id, track_id, action) VALUES (:uid, :tid, :action)');
        $stmt->execute(['uid' => $userId, 'tid' => $trackId, 'action' => $action]);

        if ($action === 'like') {
            $stmt = $pdo->prepare('INSERT INTO playlist_tracks (playlist_id, track_id) VALUES (:pid, :tid)');
            $stmt->execute(['pid' => get_user_playlist_id($pdo, $userId), 'tid' => $trackId]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() !== '23000') {
            throw $e;
        }
        // Swipe déjà enregistré (contrainte unique user_id + track_id) : on ne crée pas de doublon
        $already = true;
    }

    json_response([
        'ok'   => true,
        'data' => [
            'action'    => $action,
            'already'   => $already,
            'remaining' => count_remaining_tracks($pdo, $userId),
        ],
    ]);
} catch (PDOException $e) {
    error_log('MusicSwipe swipe_action : ' . $e->getMessage());
    json_response(['ok' => false, 'error' => "Ton choix n'a pas pu être enregistré."], 500);
}
