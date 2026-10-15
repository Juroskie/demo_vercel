<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$errors  = [];
$done    = false;
$tokenOk = false;
$reset   = null;

if ($token !== '' && ctype_xdigit($token)) {
    $stmt = $pdo->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = ? AND expires_at > NOW()');
    $stmt->execute([hash('sha256', $token)]);
    $reset   = $stmt->fetch();
    $tokenOk = (bool) $reset;
}

if ($tokenOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$reset['user_id']]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Reset Password</title>
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
        <h1>Reset Password</h1>

        <?php if ($done): ?>
            <p class="subtitle">Your password has been changed.</p>
            <a class="primary-btn" href="user-login.php" style="display:flex;align-items:center;justify-content:center;">LOGIN</a>
        <?php elseif (!$tokenOk): ?>
            <p class="subtitle">This reset link is invalid or has expired.</p>
            <a class="primary-btn" href="forgot-password.php" style="display:flex;align-items:center;justify-content:center;">REQUEST A NEW LINK</a>
        <?php else: ?>
            <p class="subtitle">Choose a new password for your account.</p>
            <?php if ($errors): ?>
                <p class="login-note" style="color:#a44343;text-align:left;"><?= h(implode(' ', $errors)) ?></p>
            <?php endif; ?>
            <form action="reset-password.php" method="post">
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password" placeholder="At least 6 characters" required>
                <label for="confirm">Confirm Password</label>
                <input id="confirm" name="confirm" type="password" placeholder="Repeat your password" required>
                <button type="submit">SAVE NEW PASSWORD</button>
            </form>
            <div class="login-links"><a href="user-login.php">Back to Login</a></div>
        <?php endif; ?>
    </main>
</body>

</html>
