<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']   = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: admin-dashboard.php');
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
    <title>Rhon's Fade | Admin Login</title>
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
        <p class="eyebrow">ADMINISTRATOR</p>
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to manage Rhon's Fade Barbershop.</p>
        <div class="role-switch"><a href="user-login.php">User Login</a><a class="active" href="admin-login.php">Admin
                Login</a></div>

        <?php if ($error): ?>
            <p class="login-note" style="color:#a44343;text-align:left;"><?= h($error) ?></p>
        <?php endif; ?>

        <form action="admin-login.php" method="post">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="admin@rhonsfade.com" required
                value="<?= h($_POST['email'] ?? '') ?>">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">LOGIN TO DASHBOARD</button>
        </form>
        <div class="login-links"><a href="user-login.php">User Login</a></div>
    </main>
</body>

</html>
