<?php
declare(strict_types=1);

/**
 * POST api/update_note.php  (JSON { track_id, note }, en-tête X-CSRF-Token)
 * Réponse : { ok: true, data: { track_id, note } } — note vide = note effacée (null)
 * L'utilisateur vient UNIQUEMENT de la session.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/likes.php';

$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Méthode non autorisée.'], 405);
}
csrf_check();

[$trackId, $input] = read_track_request();

$note = $input['note'] ?? '';
if (!is_string($note)) {
    json_response(['ok' => false, 'error' => 'Requête invalide.'], 400);
}
$note = trim($note);
if (mb_strlen($note) > NOTE_MAX_LENGTH) {
    json_response(['ok' => false, 'error' => 'La note ne doit pas dépasser 255 caractères.'], 400);
}
$note = $note === '' ? null : $note;

try {
    // Vérification préalable : un UPDATE sans changement renvoie aussi 0 ligne
    if (!user_has_like($pdo, $userId, $trackId)) {
        json_response(['ok' => false, 'error' => "Ce morceau n'est pas dans ta playlist."], 404);
    }
    update_like_note($pdo, $userId, $trackId, $note);
    json_response(['ok' => true, 'data' => ['track_id' => $trackId, 'note' => $note]]);
} catch (PDOException $e) {
    error_log('MusicSwipe update_note : ' . $e->getMessage());
    json_response(['ok' => false, 'error' => "La note n'a pas pu être enregistrée."], 500);
}
