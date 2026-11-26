<?php
declare(strict_types=1);

/**
 * En-têtes HTTP de sécurité, envoyés par toutes les pages (inclus par auth.php).
 *
 * Content-Security-Policy : le navigateur ne charge que les sources prévues.
 *  - scripts : uniquement nos fichiers (aucun script inline, aucun script externe)
 *  - styles / polices : nos fichiers + Google Fonts (Inter)
 *  - images : nos fichiers + picsum (pochettes de test, redirigées vers fastly.picsum.photos)
 *  - audio : nos fichiers + SoundHelix (extraits de test)
 *  - fetch : uniquement notre API ; formulaires : uniquement vers notre site
 *  - frame-ancestors 'none' : le site ne peut pas être affiché dans un cadre (anti-clickjacking)
 */
if (!headers_sent()) {
    header_remove('X-Powered-By'); // ne pas révéler la version de PHP

    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self'; "
        . "style-src 'self' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "img-src 'self' data: https://picsum.photos https://fastly.picsum.photos; "
        . "media-src 'self' https://www.soundhelix.com; "
        . "connect-src 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'none'; "
        . "base-uri 'self'; "
        . "object-src 'none'");
    header('X-Frame-Options: DENY');                         // équivalent pour les anciens navigateurs
    header('X-Content-Type-Options: nosniff');               // pas de « devinette » du type de fichier
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
