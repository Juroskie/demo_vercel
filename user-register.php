<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, $phone, $hash]);

        // Log the new user straight in
        $_SESSION['user_id']   = (int) $pdo->lastInsertId();
        $_SESSION['user_name'] = $fullName;

        header('Location: user-home.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Register</title>
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
        <h1>Create Account</h1>
        <p class="subtitle">Register to book your next haircut.</p>

        <?php if ($errors): ?>
            <p class="login-note" style="color:#a44343;text-align:left;">
                <?= h(implode(' ', $errors)) ?>
            </p>
        <?php endif; ?>

        <form action="user-register.php" method="post">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Your full name" required
                value="<?= h($_POST['full_name'] ?? '') ?>">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@example.com" required
                value="<?= h($_POST['email'] ?? '') ?>">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="09XXXXXXXXX" required
                value="<?= h($_POST['phone'] ?? '') ?>">
            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>
            <button type="submit">CREATE ACCOUNT</button>
        </form>
        <div class="login-links"><a href="user-login.php">Already have an account? Login</a></div>
    </main>
</body>

</html>
