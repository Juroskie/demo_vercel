<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$loggedIn = !empty($_SESSION['user_id']);
$services = $pdo->query('SELECT id, name, description, price, image FROM services ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Services</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <header class="user-header">
        <nav class="user-nav">
            <a class="user-brand" href="<?= $loggedIn ? 'user-home.php' : 'services.php' ?>"><img src="images/logo.png" alt="Logo">
                <div><strong>RHON'S FADE</strong><small>BARBERSHOP</small></div>
            </a>
            <div class="user-nav-links">
                <?php if ($loggedIn): ?>
                    <a href="user-home.php">Home</a><a href="services.php">Services</a><a href="booking.php">Book Now</a><a
                        href="my-bookings.php">My Bookings</a><a class="nav-login" href="logout.php">Log out</a>
                <?php else: ?>
                    <a href="services.php">Services</a><a href="user-register.php">Create Account</a><a class="nav-login"
                        href="user-login.php">Log in</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="user-main">
        <div class="page-title">
            <p class="section-label">OUR SERVICES</p>
            <h2>Choose Your Style</h2>
        </div>

        <?php if (!$services): ?>
            <div class="info-card"><span>No services are available yet. Please check back soon.</span></div>
        <?php else: ?>
            <div class="service-grid">
                <?php foreach ($services as $svc): ?>
                    <article class="service">
                        <?php if (!empty($svc['image'])): ?>
                            <div class="service-image">
                                <img src="<?= h($svc['image']) ?>" alt="<?= h($svc['name']) ?>" loading="lazy">
                            </div>
                        <?php else: ?>
                            <div class="service-top">✂</div>
                        <?php endif; ?>
                        <div class="service-body">
                            <h3><?= h($svc['name']) ?></h3>
                            <p><?= h($svc['description']) ?></p>
                            <div class="service-price">₱<?= number_format((float) $svc['price'], 0) ?></div>
                            <a href="booking.php?service_id=<?= (int) $svc['id'] ?>" class="book-btn">Book Now</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <footer class="user-footer">© 2026 Rhon's Fade Barbershop</footer>
</body>

</html>
