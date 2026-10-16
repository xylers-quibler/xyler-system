<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="header">
        <div>
            <h2>School Management System</h2>
            <span class="role-badge"><?php echo strtoupper($_SESSION['role']); ?></span>
        </div>
        <div class="header-links">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a href="?action=logout">Logout</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="dashboard-container">
