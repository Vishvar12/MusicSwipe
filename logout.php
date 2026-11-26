<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';

// Déconnexion uniquement en POST avec jeton CSRF (un simple lien ne doit pas pouvoir déconnecter)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(current_user_id() !== null ? 'swipe.php' : 'index.php');
}

csrf_check();
logout_user();
redirect('index.php');
