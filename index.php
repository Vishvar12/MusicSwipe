<?php
declare(strict_types=1);

// Page publique. Ticket T1 : rediriger vers swipe.php si une session est déjà active.
$pageTitle = 'Accueil';
$activeNav = null;
require __DIR__ . '/includes/header.php';
?>
<section class="home" aria-labelledby="home-title">
    <div class="home__logo">
        <span class="logo">
            <span class="logo__mark"><?= icon('music', 20) ?></span>
            <span class="logo__text">MusicSwipe</span>
        </span>
    </div>

    <div class="home__hero" aria-hidden="true">
        <span class="swipe-btn swipe-btn--dislike"><?= icon('x', 28) ?></span>
        <span class="home__cover"><?= icon('music', 56) ?></span>
        <span class="swipe-btn swipe-btn--like"><?= icon('heart', 28) ?></span>
    </div>

    <div class="home__intro">
        <h1 id="home-title">Swipe, découvre, crée ta playlist</h1>
        <p class="text-secondary">
            Écoute 30 secondes, swipe à droite si tu aimes : tes coups de cœur arrivent
            automatiquement dans « Mes likes ».
        </p>
        <ul class="home__benefits">
            <li class="home__benefit">
                <span class="home__benefit-icon"><?= icon('play', 18) ?></span>
                <span>Des extraits de 30 s, un seul à la fois</span>
            </li>
            <li class="home__benefit">
                <span class="home__benefit-icon"><?= icon('heart', 18) ?></span>
                <span>J'aime ou je passe, en un geste</span>
            </li>
            <li class="home__benefit">
                <span class="home__benefit-icon"><?= icon('list', 18) ?></span>
                <span>Ta playlist triée, filtrée et annotée</span>
            </li>
        </ul>
    </div>

    <div class="home__actions">
        <a class="btn btn--primary btn--block" href="register.php">Créer un compte</a>
        <a class="btn btn--secondary btn--block" href="login.php">J'ai déjà un compte</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
