<?php
// Charge la configuration locale
$configPath = __DIR__ . '/../config.local.php';

if (!file_exists($configPath)) {
    die('Erreur : le fichier config.local.php est manquant. Dupliquez config.example.php.');
}

require_once $configPath;

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs SQL en dev
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne des tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Vraies requêtes préparées (anti-injection SQL)
    ]);
} catch (PDOException $e) {
    // En production, on ne montre jamais le détail de l'erreur à l'utilisateur
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}