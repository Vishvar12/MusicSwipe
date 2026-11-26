<?php
declare(strict_types=1);

/**
 * Protection CSRF : un jeton aléatoire par session, vérifié sur chaque requête POST.
 * Formulaires : champ caché csrf_token. Appels fetch : en-tête X-CSRF-Token.
 * À inclure après session_start() (fait par auth.php).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Arrête la requête (HTTP 403) si le jeton envoyé est absent ou différent.
 */
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sent) || $expected === '' || !hash_equals($expected, $sent)) {
        $message = 'Ta session a expiré. Recharge la page et réessaie.';
        if (function_exists('is_api_request') && is_api_request()) {
            json_response(['ok' => false, 'error' => $message], 403);
        }
        http_response_code(403);
        exit($message);
    }
}
