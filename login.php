<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_guest($pdo);

// Limite de tentatives : après 5 échecs, la connexion est bloquée 5 minutes (compteur de session).
// Limite connue : un client qui supprime ses cookies repart de zéro (voir docs/security-audit.md).
const LOGIN_MAX_FAILURES = 5;
const LOGIN_LOCK_SECONDS = 300;

$email = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);

    if ($lockedUntil > time()) {
        $minutes = (int) ceil(($lockedUntil - time()) / 60);
        $error = 'Trop de tentatives. Réessaie dans ' . $minutes . ($minutes > 1 ? ' minutes.' : ' minute.');
    } elseif ($email === '' || $password === '') {
        $error = 'Renseigne ton email et ton mot de passe.';
    } else {
        $stmt = $pdo->prepare('SELECT id, pseudo, password_hash FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Message volontairement identique : on ne révèle pas si l'email existe
        if ($user === false || !password_verify($password, $user['password_hash'])) {
            $error = 'Email ou mot de passe incorrect.';
            $_SESSION['login_failures'] = (int) ($_SESSION['login_failures'] ?? 0) + 1;
            if ($_SESSION['login_failures'] >= LOGIN_MAX_FAILURES) {
                $_SESSION['login_locked_until'] = time() + LOGIN_LOCK_SECONDS;
                $_SESSION['login_failures'] = 0;
                $error = 'Trop de tentatives. Réessaie dans 5 minutes.';
            }
        } else {
            unset($_SESSION['login_failures'], $_SESSION['login_locked_until']);
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                $stmt->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
            }
            login_user((int) $user['id'], $user['pseudo']);
            redirect(user_home($pdo, (int) $user['id']));
        }
    }
}

$pageTitle = 'Connexion';
$pageScripts = ['assets/js/forms.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="auth" aria-labelledby="auth-title">
    <a class="logo" href="index.php">
        <span class="logo__mark"><?= icon('music', 20) ?></span>
        <span class="logo__text">MusicSwipe</span>
    </a>

    <div class="auth__intro">
        <h1 id="auth-title">Bon retour !</h1>
        <p class="text-secondary">Connecte-toi pour reprendre tes découvertes.</p>
    </div>

    <?php if ($error !== null): ?>
        <p class="alert alert--error" role="alert"><?= icon('alert', 20) ?><span><?= e($error) ?></span></p>
    <?php endif; ?>

    <form class="form" method="post" action="login.php" data-loading-text="Connexion…">
        <?= csrf_field() ?>
        <?= form_field('email', 'Email', 'email', $email, null,
            'required maxlength="255" autocomplete="email" placeholder="ex. camelia@mail.fr"') ?>
        <?= form_field('password', 'Mot de passe', 'password', '', null,
            'required autocomplete="current-password"') ?>
        <button class="btn btn--primary btn--block" type="submit">Se connecter</button>
    </form>

    <p class="link-row text-small">
        <span class="text-secondary">Pas encore de compte ?</span>
        <a href="register.php">Créer un compte</a>
    </p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
