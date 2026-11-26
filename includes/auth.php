<?php
declare(strict_types=1);

/**
 * Sessions et contrôle d'accès.
 * L'identité de l'utilisateur vient UNIQUEMENT de $_SESSION['user_id'] (jamais du client).
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';   // en-têtes HTTP de sécurité, avant toute sortie
require_once __DIR__ . '/db.php';         // $pdo : nécessaire pour vérifier que le compte existe encore

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');     // refuse un identifiant de session inventé par le client
    ini_set('session.use_only_cookies', '1');    // jamais d'identifiant de session dans l'URL
    session_name('MUSICSWIPESESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/csrf.php';

/**
 * Identifiant de l'utilisateur connecté, ou null.
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Vrai si le script appelé est un endpoint JSON du dossier api/.
 */
function is_api_request(): bool
{
    return str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
}

/**
 * Empêche le navigateur de garder en cache une page privée
 * (le bouton « Précédent » ne doit plus l'afficher après déconnexion).
 */
function send_no_cache_headers(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/**
 * Bloque l'accès aux visiteurs non connectés et retourne l'id de l'utilisateur.
 * Page HTML → redirection vers login.php ; endpoint api/ → JSON 401.
 */
function require_login(): int
{
    $userId = current_user_id();

    // Session « orpheline » : le compte a été supprimé en base depuis la connexion
    if ($userId !== null && !user_exists($GLOBALS['pdo'], $userId)) {
        logout_user();
        session_start(); // nouvelle session vide (jeton CSRF propre pour la page de connexion)
        $userId = null;
    }

    if ($userId === null) {
        if (is_api_request()) {
            json_response(['ok' => false, 'error' => 'Tu dois être connecté.'], 401);
        }
        redirect('login.php');
    }
    send_no_cache_headers();
    return $userId;
}

/**
 * Vrai si le compte existe toujours en base.
 */
function user_exists(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE id = :uid');
    $stmt->execute(['uid' => $userId]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Page d'arrivée d'un utilisateur connecté : onboarding s'il n'a encore choisi aucun genre.
 */
function user_home(PDO $pdo, int $userId): string
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_genres WHERE user_id = :uid');
    $stmt->execute(['uid' => $userId]);
    return (int) $stmt->fetchColumn() === 0 ? 'onboarding.php' : 'swipe.php';
}

/**
 * Pages réservées aux visiteurs (connexion, inscription) : un utilisateur connecté est renvoyé chez lui.
 */
function require_guest(PDO $pdo): void
{
    $userId = current_user_id();
    if ($userId !== null && !user_exists($pdo, $userId)) {
        logout_user();   // session orpheline : on reste sur la page de connexion
        session_start();
        return;
    }
    if ($userId !== null) {
        redirect(user_home($pdo, $userId));
    }
}

/**
 * Ouvre la session d'un utilisateur authentifié (nouvel identifiant de session : anti-fixation).
 */
function login_user(int $userId, string $pseudo): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['pseudo'] = $pseudo;
    unset($_SESSION['csrf_token']); // nouveau jeton CSRF pour la session authentifiée
}

/**
 * Ferme complètement la session (données, cookie, fichier serveur).
 */
function logout_user(): void
{
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $params['path'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'],
    ]);
    session_destroy();
}
