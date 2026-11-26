<?php
declare(strict_types=1);

// Page provisoire (T1) : protégée et reliée à la navigation. Contenu livré dans un ticket suivant.
require __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Profil';
$activeNav = 'profil';
require __DIR__ . '/includes/header.php';
?>
<section class="coming-soon" aria-labelledby="page-title">
    <p class="text-secondary">Bonjour <?= e($_SESSION['pseudo'] ?? '') ?> 👋</p>
    <h1 id="page-title">Profil</h1>
    <p class="text-secondary">Bientôt disponible : ton tableau de bord.</p>
    <form action="logout.php" method="post">
        <?= csrf_field() ?>
        <button class="btn btn--secondary" type="submit"><?= icon('logout', 18) ?><span>Se déconnecter</span></button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
