<?php
/**
 * En-tête commun.
 * Variables attendues (optionnelles) avant l'include :
 *   $pageTitle (string)  titre de l'onglet
 *   $activeNav (?string) 'swipe' | 'likes' | 'profil' — null sur les pages publiques (pas de navigation)
 */
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Accueil';
$activeNav = $activeNav ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MusicSwipe — swipe, découvre, crée ta playlist.">
    <title><?= e($pageTitle) ?> · MusicSwipe</title>
    <?php if (function_exists('csrf_token') && session_status() === PHP_SESSION_ACTIVE): ?>
        <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/components.css')) ?>">
</head>
<body class="<?= $activeNav !== null ? 'has-nav' : '' ?>">
    <a class="skip-link" href="#contenu">Aller au contenu</a>
    <?php if ($activeNav !== null) {
        require __DIR__ . '/nav.php';
    } ?>
    <main id="contenu" class="page">
