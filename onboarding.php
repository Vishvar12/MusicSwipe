<?php
declare(strict_types=1);

// Page provisoire (T1) : protégée. Le choix des genres arrive au ticket T2.
require __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Tes genres préférés';
$activeNav = null;
require __DIR__ . '/includes/header.php';
?>
<section class="coming-soon" aria-labelledby="page-title">
    <p class="text-secondary">Bonjour <?= e($_SESSION['pseudo'] ?? '') ?> 👋</p>
    <h1 id="page-title">Qu'est-ce que tu écoutes ?</h1>
    <p class="text-secondary">Bientôt disponible : le choix de tes genres préférés.</p>
    <a class="btn btn--primary" href="swipe.php">Continuer</a>
    <form action="logout.php" method="post">
        <?= csrf_field() ?>
        <button class="btn btn--ghost" type="submit">Se déconnecter</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
