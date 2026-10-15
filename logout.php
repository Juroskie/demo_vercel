<?php
require_once __DIR__ . '/includes/auth.php';

$wasAdmin = !empty($_SESSION['admin_id']);

$_SESSION = [];
session_destroy();

header('Location: ' . ($wasAdmin ? 'admin-login.php' : 'user-login.php'));
exit;
