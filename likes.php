<?php
declare(strict_types=1);

// Page provisoire (T1) : protégée et reliée à la navigation. Contenu livré dans un ticket suivant.
require __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Mes likes';
$activeNav = 'likes';
require __DIR__ . '/includes/header.php';
?>
<section class="coming-soon" aria-labelledby="page-title">
    <p class="text-secondary">Bonjour <?= e($_SESSION['pseudo'] ?? '') ?> 👋</p>
    <h1 id="page-title">Mes likes</h1>
    <p class="text-secondary">Bientôt disponible : ta playlist « Mes likes ».</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
