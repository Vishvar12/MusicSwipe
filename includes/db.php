<?php
// Charge la configuration locale (identifiants BDD, jamais versionnés)
$configPath = __DIR__ . '/../config.local.php';

if (!file_exists($configPath)) {
    error_log('MusicSwipe : config.local.php manquant (dupliquer config.example.php).');
    http_response_code(500);
    exit('Le service est temporairement indisponible. Merci de réessayer plus tard.');
}

require_once $configPath;

// Affichage des erreurs : seulement si APP_DEBUG vaut true (développement local).
// Sans la constante, on choisit l'option sûre : rien à l'écran, tout dans les logs.
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceptions sur erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Vraies requêtes préparées (anti-injection SQL)
    ]);
} catch (PDOException $e) {
    // Le détail technique va dans les logs PHP, jamais à l'écran
    error_log('MusicSwipe : connexion BDD impossible — ' . $e->getMessage());
    http_response_code(500);
    exit('Le service est temporairement indisponible. Merci de réessayer plus tard.');
}
