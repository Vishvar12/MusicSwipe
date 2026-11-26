<?php
/**
 * Navigation des pages privées.
 * - Mobile (< 1024 px) : barre d'onglets fixée en bas.
 * - Desktop (≥ 1024 px) : en-tête en haut avec logo et déconnexion.
 * L'onglet actif est signalé par aria-current, un texte en gras et un indicateur (pas seulement la couleur).
 */
$navItems = [
    'swipe'  => ['href' => 'swipe.php',  'label' => 'Découvrir', 'icon' => 'cards'],
    'likes'  => ['href' => 'likes.php',  'label' => 'Mes likes', 'icon' => 'heart'],
    'profil' => ['href' => 'profil.php', 'label' => 'Profil',    'icon' => 'user'],
];
?>
<header class="site-header">
    <a class="logo" href="swipe.php">
        <span class="logo__mark"><?= icon('music', 20) ?></span>
        <span class="logo__text">MusicSwipe</span>
    </a>
    <nav class="site-header__nav" aria-label="Navigation principale">
        <?php foreach ($navItems as $key => $item): ?>
            <a class="nav-link<?= $key === $activeNav ? ' is-active' : '' ?>" href="<?= e($item['href']) ?>"
               <?= $key === $activeNav ? 'aria-current="page"' : '' ?>>
                <?= icon($item['icon'], 20) ?>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <form class="site-header__logout" action="logout.php" method="post">
        <?= csrf_field() ?>
        <button class="btn btn--secondary btn--sm" type="submit">
            <?= icon('logout', 18) ?>
            <span>Se déconnecter</span>
        </button>
    </form>
</header>

<nav class="tabbar" aria-label="Navigation principale (mobile)">
    <?php foreach ($navItems as $key => $item): ?>
        <a class="tabbar__item<?= $key === $activeNav ? ' is-active' : '' ?>" href="<?= e($item['href']) ?>"
           <?= $key === $activeNav ? 'aria-current="page"' : '' ?>>
            <?= icon($item['icon'], 24) ?>
            <span><?= e($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
