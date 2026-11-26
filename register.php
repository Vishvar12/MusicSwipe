<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

require_guest($pdo);

$values = ['pseudo' => '', 'email' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $pseudo   = trim((string) ($_POST['pseudo'] ?? ''));
    $email    = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');
    $values   = ['pseudo' => $pseudo, 'email' => $email];

    // Validation serveur (la validation HTML peut être contournée)
    $pseudoLength = mb_strlen($pseudo);
    if ($pseudoLength < 2 || $pseudoLength > 30) {
        $errors['pseudo'] = 'Le pseudo doit contenir entre 2 et 30 caractères.';
    }
    if ($email === '' || mb_strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Saisis une adresse email valide.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif (strlen($password) > 72) {
        $errors['password'] = 'Le mot de passe ne doit pas dépasser 72 caractères.';
    }
    if ($confirm !== $password) {
        $errors['password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
    }

    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn()) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }
    }

    if (!$errors) {
        try {
            // Compte + playlist « Mes likes » : tout ou rien
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, pseudo) VALUES (:email, :hash, :pseudo)');
            $stmt->execute([
                'email'  => $email,
                'hash'   => password_hash($password, PASSWORD_DEFAULT),
                'pseudo' => $pseudo,
            ]);
            $userId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO playlists (user_id, name) VALUES (:uid, :name)');
            $stmt->execute(['uid' => $userId, 'name' => 'Mes likes']);

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                // Deux inscriptions simultanées avec le même email
                $errors['email'] = 'Cet email est déjà utilisé.';
            } else {
                error_log('MusicSwipe register : ' . $e->getMessage());
                $errors['form'] = 'Une erreur est survenue. Réessaie dans un instant.';
            }
        }

        if (!$errors) {
            login_user($userId, $pseudo);
            redirect('onboarding.php');
        }
    }
}

$pageTitle = 'Créer un compte';
$pageScripts = ['assets/js/forms.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="auth" aria-labelledby="auth-title">
    <a class="logo" href="index.php">
        <span class="logo__mark"><?= icon('music', 20) ?></span>
        <span class="logo__text">MusicSwipe</span>
    </a>

    <h1 id="auth-title">Crée ton compte</h1>

    <?php if (isset($errors['form'])): ?>
        <p class="alert alert--error" role="alert"><?= icon('alert', 20) ?><span><?= e($errors['form']) ?></span></p>
    <?php elseif ($errors): ?>
        <p class="visually-hidden" role="alert">Le formulaire contient des erreurs.</p>
    <?php endif; ?>

    <form class="form" method="post" action="register.php" data-loading-text="Création du compte…">
        <?= csrf_field() ?>
        <?= form_field('pseudo', 'Pseudo', 'text', $values['pseudo'], $errors['pseudo'] ?? null,
            'required minlength="2" maxlength="30" autocomplete="nickname" placeholder="ex. Camélia"') ?>
        <?= form_field('email', 'Email', 'email', $values['email'], $errors['email'] ?? null,
            'required maxlength="255" autocomplete="email" placeholder="ex. camelia@mail.fr"') ?>
        <?= form_field('password', 'Mot de passe', 'password', '', $errors['password'] ?? null,
            'required minlength="8" maxlength="72" autocomplete="new-password"', '8 caractères minimum.') ?>
        <?= form_field('password_confirm', 'Confirmation du mot de passe', 'password', '', $errors['password_confirm'] ?? null,
            'required minlength="8" maxlength="72" autocomplete="new-password"') ?>
        <button class="btn btn--primary btn--block" type="submit">Créer mon compte</button>
    </form>

    <p class="link-row text-small">
        <span class="text-secondary">Déjà inscrit ?</span>
        <a href="login.php">Se connecter</a>
    </p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
