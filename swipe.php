<?php
declare(strict_types=1);

// Page provisoire (T1) : protégée et reliée à la navigation. Contenu livré dans un ticket suivant.
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
$userId = require_login();

// Sans genre préféré, l'utilisateur passe d'abord par l'onboarding (US-03)
if (user_home($pdo, $userId) === 'onboarding.php') {
    redirect('onboarding.php');
}

$pageTitle = 'Découvrir';
$activeNav = 'swipe';
require __DIR__ . '/includes/header.php';
?>
<section class="coming-soon" aria-labelledby="page-title">
    <p class="text-secondary">Bonjour <?= e($_SESSION['pseudo'] ?? '') ?> 👋</p>
    <h1 id="page-title">Découvrir</h1>
    <p class="text-secondary">Bientôt disponible : la découverte de morceaux par swipe.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
