<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

// On a local XAMPP server, email usually isn't set up, so the reset link is shown
// on screen. Set this to false when the site goes online and mail() works.
$SHOW_LINK_ON_SCREEN = true;

$message = '';
$error   = '';
$link    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
            $pdo->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
            )->execute([$user['id'], hash('sha256', $token)]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base   = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $url    = $base . '/reset-password.php?token=' . $token;

            @mail($email, "Rhon's Fade password reset",
                "Use this link to choose a new password (valid for 1 hour):\n\n" . $url);

            if ($SHOW_LINK_ON_SCREEN) {
                $link = $url;
            }
        }
        // Same message whether or not the email exists, so accounts can't be guessed.
        $message = 'If that email has an account, a reset link has been sent. It works for 1 hour.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Forgot Password</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
</head>

<body class="login-page">
    <main class="login-card">
        <div class="logo-wrap"><img src="images/logo.png" alt="Rhon's Fade Logo"></div>
        <p class="eyebrow">CUSTOMER PORTAL</p>
        <h1>Forgot Password</h1>
        <p class="subtitle">Enter your email and we'll send you a link to set a new password.</p>

        <?php if ($error): ?>
            <p class="login-note" style="color:#a44343;text-align:left;"><?= h($error) ?></p>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="notice"><?= h($message) ?>
                <?php if ($link): ?>
                    <br><br><strong>Local testing link:</strong><br><a href="<?= h($link) ?>"><?= h($link) ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="forgot-password.php" method="post">
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" required
                value="<?= h($_POST['email'] ?? '') ?>">
            <button type="submit">SEND RESET LINK</button>
        </form>
        <div class="login-links"><a href="user-login.php">Back to Login</a></div>
    </main>
</body>

</html>
