<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin_login();

$totalBookings = (int) $pdo->query('SELECT COUNT(*) c FROM bookings')->fetch()['c'];
$pending       = (int) $pdo->query("SELECT COUNT(*) c FROM bookings WHERE status='pending'")->fetch()['c'];
$confirmed     = (int) $pdo->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch()['c'];
$customerCount = (int) $pdo->query('SELECT COUNT(*) c FROM users')->fetch()['c'];

$bookings = $pdo->query(
    'SELECT b.id, b.booking_date, b.booking_time, b.status,
            u.full_name, u.phone, s.name AS service_name
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN services s ON s.id = b.service_id
     ORDER BY b.booking_date DESC, b.booking_time DESC'
)->fetchAll();

$customers = $pdo->query('SELECT full_name, email, phone, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$services  = $pdo->query('SELECT id, name, description, price, image FROM services ORDER BY id')->fetchAll();

// Handle service image upload / removal (one service at a time)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_service_id'])) {
    $serviceId = (int) $_POST['image_service_id'];
    $stmt = $pdo->prepare('SELECT image FROM services WHERE id = ?');
    $stmt->execute([$serviceId]);
    $row = $stmt->fetch();
    $uploadDir = __DIR__ . '/images/services/';
    $flash = '';

    if (!$row) {
        $flash = 'Service not found.';
    } elseif (isset($_POST['remove_image'])) {
        if (!empty($row['image']) && is_file(__DIR__ . '/' . $row['image'])) {
            @unlink(__DIR__ . '/' . $row['image']);
        }
        $pdo->prepare('UPDATE services SET image = NULL WHERE id = ?')->execute([$serviceId]);
        $flash = 'Image removed.';
    } elseif (!isset($_FILES['service_image']) || $_FILES['service_image']['error'] !== UPLOAD_ERR_OK) {
        $flash = 'Choose an image file first (max 3 MB).';
    } else {
        $file = $_FILES['service_image'];
        $info = @getimagesize($file['tmp_name']);
        $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

        if ($file['size'] > 3 * 1024 * 1024) {
            $flash = 'That image is larger than 3 MB.';
        } elseif (!$info || !isset($types[$info[2]])) {
            $flash = 'Use a JPG, PNG or WEBP image.';
        } else {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $name = 'service-' . $serviceId . '-' . bin2hex(random_bytes(4)) . '.' . $types[$info[2]];
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
                if (!empty($row['image']) && is_file(__DIR__ . '/' . $row['image'])) {
                    @unlink(__DIR__ . '/' . $row['image']);
                }
                $pdo->prepare('UPDATE services SET image = ? WHERE id = ?')
                    ->execute(['images/services/' . $name, $serviceId]);
                $flash = 'Image saved.';
            } else {
                $flash = 'Could not save the image. Check that the images/services folder is writable.';
            }
        }
    }
    $_SESSION['flash'] = $flash;
    header('Location: admin-dashboard.php#services');
    exit;
}

// Handle status changes from the dashboard (confirm / cancel a booking)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $allowed = ['pending', 'confirmed', 'cancelled'];
    $newStatus = $_POST['new_status'];
    if (in_array($newStatus, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, (int) $_POST['booking_id']]);
    }
    header('Location: admin-dashboard.php#bookings');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rhon's Fade | Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="brand">

                <div>
                    <h2>RHON'S FADE</h2><span>ADMIN PANEL</span>
                </div>
            </div>
            <nav class="navigation"><a class="active" href="#dashboard">⌂ <span>Dashboard</span></a><a
                    href="#bookings">▣ <span>Bookings</span></a><a href="#customers">♙ <span>Customers</span></a><a
                    href="#services">✂ <span>Services</span></a></nav>
            <div class="sidebar-bottom">
                <div class="admin-profile">
                    <div class="avatar"><?= h(strtoupper(substr($_SESSION['admin_name'], 0, 1))) ?></div>
                    <div><strong><?= h($_SESSION['admin_name']) ?></strong><small>Admin Account</small></div>
                </div><a class="logout" href="logout.php">↪ Log out</a>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div>
                    <p class="top-label">ADMINISTRATION</p>
                    <h1>Dashboard</h1>
                </div>
                <div class="top-user">
                    <div class="avatar"><?= h(strtoupper(substr($_SESSION['admin_name'], 0, 1))) ?></div>
                    <div><strong><?= h($_SESSION['admin_name']) ?></strong><small>Admin Account</small></div>
                </div>
            </header>
            <section class="welcome" id="dashboard">
                <div>
                    <p class="welcome-label">RHON'S FADE BARBERSHOP</p>
                    <h2>Welcome to your Admin Dashboard</h2>
                    <p>Manage appointments, customers, and services from one place.</p>
                </div><img src="images/logo.png" alt="Logo">
            </section>
           <section class="stats">
    <div class="stat">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
        </div>
        <div><span>Total Bookings</span><strong><?= $totalBookings ?></strong><small>All appointments</small></div>
    </div>
    <div class="stat">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12M6 21h12M7 3c0 5 5 6 5 9s-5 4-5 9M17 3c0 5-5 6-5 9s5 4 5 9"/></svg>
        </div>
        <div><span>Pending</span><strong><?= $pending ?></strong><small>Need attention</small></div>
    </div>
    <div class="stat">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
        </div>
        <div><span>Confirmed</span><strong><?= $confirmed ?></strong><small>Confirmed bookings</small></div>
    </div>
    <div class="stat">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.7a3.5 3.5 0 010 6.6M18 14.3c2.2.6 3.5 2.4 3.5 5.7"/></svg>
        </div>
        <div><span>Customers</span><strong><?= $customerCount ?></strong><small>Registered customers</small></div>
    </div>
</section>
            <section class="section" id="bookings">
                <div class="section-header">
                    <div>
                        <p class="section-label">APPOINTMENTS</p>
                        <h2>Booking Management</h2>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$bookings): ?>
                                <tr><td colspan="7">No bookings yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><strong>#RF-<?= str_pad((string) $b['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td>
                                        <div class="customer">
                                            <div class="customer-avatar">
                                                <?= h(strtoupper(substr($b['full_name'], 0, 2))) ?>
                                            </div>
                                            <div><strong><?= h($b['full_name']) ?></strong><small><?= h($b['phone']) ?></small></div>
                                        </div>
                                    </td>
                                    <td><?= h($b['service_name']) ?></td>
                                    <td><?= date('M j, Y', strtotime($b['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($b['booking_time'])) ?></td>
                                    <td><span class="status <?= h($b['status']) ?>"><?= ucfirst($b['status']) ?></span></td>
                                    <td>
                                        <form method="post" style="display:flex;gap:6px;">
                                            <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                                            <select name="new_status" style="height:32px;border:1px solid var(--border);border-radius:6px;">
                                                <option value="pending"   <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" style="border:0;border-radius:6px;background:var(--black);color:var(--gold);padding:0 10px;cursor:pointer;">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="section" id="customers">
                <div class="section-header">
                    <div>
                        <p class="section-label">CUSTOMER MANAGEMENT</p>
                        <h2>Customers</h2>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$customers): ?>
                                <tr><td colspan="4">No customers yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td><?= h($c['full_name']) ?></td>
                                    <td><?= h($c['email']) ?></td>
                                    <td><?= h($c['phone']) ?></td>
                                    <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="section" id="services">
                <div class="section-header">
                    <div>
                        <p class="section-label">SERVICE MENU</p>
                        <h2>Services</h2>
                    </div>
                </div>
                <?php if (!empty($_SESSION['flash'])): ?>
                    <p class="flash"><?= h($_SESSION['flash']) ?></p>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>
                <div class="service-grid">
                    <?php foreach ($services as $svc): ?>
                        <article class="service">
                            <?php if (!empty($svc['image'])): ?>
                                <div class="service-image"><img src="<?= h($svc['image']) ?>" alt="<?= h($svc['name']) ?>"></div>
                            <?php else: ?>
                                <div class="service-top">✂</div>
                            <?php endif; ?>
                            <div class="service-body">
                                <h3><?= h($svc['name']) ?></h3>
                                <p><?= h($svc['description']) ?></p>
                                <strong>₱<?= number_format((float) $svc['price'], 0) ?></strong>
                                <form class="image-form" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="image_service_id" value="<?= (int) $svc['id'] ?>">
                                    <input type="file" name="service_image" accept="image/jpeg,image/png,image/webp">
                                    <div class="image-actions">
                                        <button type="submit">Upload image</button>
                                        <?php if (!empty($svc['image'])): ?>
                                            <button type="submit" name="remove_image" value="1" class="danger"
                                                onclick="return confirm('Remove this image?')">Remove</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <footer class="user-footer">© 2026 Rhon's Fade Barbershop — Admin Management System</footer>
        </main>
    </div>
</body>

</html>