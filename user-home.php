<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_user_login();

$services = $pdo->query('SELECT id, name, description, price, image FROM services ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Home</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <header class="user-header">
        <nav class="user-nav"><a class="user-brand" href="user-home.php"><img src="images/logo.png" alt="Logo">
                <div><strong>RHON'S FADE</strong><small>BARBERSHOP</small></div>
            </a>
            <div class="user-nav-links"><a href="user-home.php">Home</a><a href="services.php">Services</a><a
                    href="booking.php">Book Now</a><a href="my-bookings.php">My Bookings</a><a class="nav-login"
                    href="logout.php">Log out</a></div>
        </nav>
    </header>
    <main class="user-main">
        <section class="hero">
            <div>
                <p class="eyebrow">WELCOME BACK, <?= h(strtoupper($_SESSION['user_name'])) ?></p>
                <h1>Clean Cuts. Sharp Style. Your Time.</h1>
                <p>Book your appointment at Rhon's Fade Barbershop and get a professional cut that fits your style.</p>
                <a class="hero-btn" href="booking.php">BOOK AN APPOINTMENT</a>
            </div><img src="images/logo.png" alt="Rhon's Fade Logo">
        </section>
        <section id="services">
            <div class="page-title">
                <p class="section-label">OUR SERVICES</p>
                <h2>Choose Your Style</h2>
            </div>
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
        </section>
    </main>
    <footer class="user-footer">© 2026 Rhon's Fade Barbershop</footer>
</body>

</html>
