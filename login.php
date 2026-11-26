<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

require_guest($pdo);

$email = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Renseigne ton email et ton mot de passe.';
    } else {
        $stmt = $pdo->prepare('SELECT id, pseudo, password_hash FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Message volontairement identique : on ne révèle pas si l'email existe
        if ($user === false || !password_verify($password, $user['password_hash'])) {
            $error = 'Email ou mot de passe incorrect.';
        } else {
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
