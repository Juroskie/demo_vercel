<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_user_login();

$errors  = [];
$selectedService = (int) ($_POST['service_id'] ?? $_GET['service_id'] ?? 0);
$services = $pdo->query('SELECT id, name, price FROM services ORDER BY id')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $date      = trim($_POST['date'] ?? '');
    $time      = trim($_POST['time'] ?? '');

    if ($serviceId <= 0 || $date === '' || $time === '') {
        $errors[] = 'Please fill in all fields.';
    }

    if (!$errors && $date < date('Y-m-d')) {
        $errors[] = 'Please choose today or a future date.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM services WHERE id = ?');
        $check->execute([$serviceId]);
        if (!$check->fetch()) {
            $errors[] = 'Please choose a valid service.';
        }
    }

    if (!$errors) {
        $taken = $pdo->prepare(
            "SELECT id FROM bookings WHERE booking_date = ? AND booking_time = ? AND status <> 'cancelled'"
        );
        $taken->execute([$date, $time]);
        if ($taken->fetch()) {
            $errors[] = 'That time is already booked. Please choose another time.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO bookings (user_id, service_id, booking_date, booking_time, status)
             VALUES (?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$_SESSION['user_id'], $serviceId, $date, $time]);

        header('Location: my-bookings.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Book Appointment</title>
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
            <div class="user-nav-links"><a href="user-home.php">Home</a><a href="services.php">Services</a><a href="booking.php">Book Now</a><a
                    href="my-bookings.php">My Bookings</a><a class="nav-login" href="logout.php">Log out</a></div>
        </nav>
    </header>
    <main class="user-main">
        <div class="page-title">
            <p class="section-label">APPOINTMENT</p>
            <h2>Book Your Appointment</h2>
        </div>

        <?php if ($errors): ?>
            <p class="login-note" style="color:#a44343;">
                <?= h(implode(' ', $errors)) ?>
            </p>
        <?php endif; ?>

        <div class="booking-card">
            <form action="booking.php" method="post">
                <div class="booking-grid">
                    <div class="field"><label>Full Name</label>
                        <input type="text" value="<?= h($_SESSION['user_name']) ?>" disabled>
                    </div>
                    <div class="field"><label>Service</label>
                        <select name="service_id" required>
                            <option value="">Select a service</option>
                            <?php foreach ($services as $svc): ?>
                                <option value="<?= (int) $svc['id'] ?>" <?= $selectedService === (int) $svc['id'] ? 'selected' : '' ?>>
                                    <?= h($svc['name']) ?> — ₱<?= number_format((float) $svc['price'], 0) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Date</label><input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= h($_POST['date'] ?? '') ?>" required></div>
                    <div class="field"><label>Time</label><input type="time" name="time" value="<?= h($_POST['time'] ?? '') ?>" required></div>
                </div><button class="booking-submit" type="submit">CONFIRM BOOKING</button>
            </form>
        </div>
    </main>
    <footer class="user-footer">© 2026 Rhon's Fade Barbershop</footer>
</body>

</html>
