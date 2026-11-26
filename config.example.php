<?php
// Dupliquez ce fichier en "config.local.php" et mettez vos vrais identifiants.
// Ne mettez JAMAIS vos vrais mots de passe ici.

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '8889');      // MAMP utilise souvent 8889 (ou 3306 selon vos réglages)
define('DB_NAME', 'musicswipe');
define('DB_USER', 'root');
define('DB_PASS', 'root');      // Sur MAMP par défaut : 'root' (sur XAMPP : '')

// true en local uniquement : affiche les erreurs PHP à l'écran.
// En production : false (les erreurs vont dans les logs, jamais à l'écran). Valeur par défaut si absent : false.
define('APP_DEBUG', true);
