<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_user_login();

// Handle cancellation (only the owner can cancel their own booking)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $bookingId = (int) $_POST['cancel_booking_id'];
    $stmt = $pdo->prepare(
        'UPDATE bookings SET status = "cancelled" WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$bookingId, $_SESSION['user_id']]);

    header('Location: my-bookings.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT b.id, b.booking_date, b.booking_time, b.status, s.name AS service_name
     FROM bookings b
     JOIN services s ON s.id = b.service_id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC, b.booking_time DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | My Bookings</title>
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
            <p class="section-label">CUSTOMER PORTAL</p>
            <h2>My Bookings</h2>
        </div>

        <?php if (!$bookings): ?>
            <div class="info-card"><span>You don't have any bookings yet. <a href="booking.php">Book one now.</a></span></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong>#RF-<?= str_pad((string) $b['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= h($b['service_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($b['booking_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($b['booking_time'])) ?></td>
                                <td><span class="status <?= h($b['status']) ?>"><?= ucfirst($b['status']) ?></span></td>
                                <td>
                                    <?php if ($b['status'] !== 'cancelled'): ?>
                                        <details class="cancel-details">
                                            <summary>Cancel Booking</summary>
                                            <div class="cancel-confirm">
                                                <p>Cancel this appointment?</p>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="cancel_booking_id" value="<?= (int) $b['id'] ?>">
                                                    <button type="submit" class="confirm-cancel"
                                                        style="border:0;font:inherit;cursor:pointer;">Yes, Cancel</button>
                                                </form>
                                            </div>
                                        </details>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <footer class="user-footer">© 2026 Rhon's Fade Barbershop</footer>

    <style>
        .cancel-details { min-width: 130px; }
        .cancel-details summary {
            list-style: none; display: inline-block; padding: 8px 12px;
            border: 1px solid #8b6b3f; border-radius: 7px; color: #8b6b3f;
            background: #fff; font-size: 13px; font-weight: 700; cursor: pointer;
        }
        .cancel-details summary::-webkit-details-marker { display: none; }
        .cancel-details summary:hover { background: #8b6b3f; color: #fff; }
    </style>
</body>

</html>
