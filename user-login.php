<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']   = (int) $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: user-home.php');
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | User Login</title>
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
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to book and manage your appointments.</p>
        <div class="role-switch"><a class="active" href="user-login.php">User Login</a><a href="admin-login.php">Admin
                Login</a></div>

        <?php if ($error): ?>
            <p class="login-note" style="color:#a44343;text-align:left;"><?= h($error) ?></p>
        <?php endif; ?>

        <form action="user-login.php" method="post">
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" required
                value="<?= h($_POST['email'] ?? '') ?>">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter your password" required>
            <p class="forgot"><a href="forgot-password.php">Forgot Password?</a></p>
            <button type="submit">LOGIN</button>
        </form>
        <div class="login-links"><a href="user-register.php">Create Account</a><span>•</span><a
                href="admin-login.php">Admin Login</a></div>
        <p class="login-note">Connected to MySQL — real accounts, real sessions.</p>
    </main>
</body>

</html>
