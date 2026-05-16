<?php
// --- ROUTER ---
require_once __DIR__ . '/includes/auth.php';

$action = $_GET['action'] ?? '';

if (isset($_SESSION['user_id'])) {
    // User is logged in
    if ($action == 'logout') {
        logAction($conn, $_SESSION['user_id'] ?? 0, "Logged out");
        session_destroy();
        header("Location: index.php");
        exit();
    } elseif ($action == 'profile') {
        require_once __DIR__ . '/pages/profile.php';
    } elseif ($action == 'admin') {
        require_once __DIR__ . '/pages/admin_dashboard.php';
    } elseif ($action == 'dean') {
        require_once __DIR__ . '/pages/dean_dashboard.php';
    } elseif ($action == 'finance') {
        require_once __DIR__ . '/pages/finance_dashboard.php';
    } elseif ($action == 'teacher') {
        require_once __DIR__ . '/pages/teacher_dashboard.php';
    } elseif ($action == 'cashier') {
        require_once __DIR__ . '/pages/cashier_dashboard.php';
    } elseif ($action == 'records') {
        require_once __DIR__ . '/pages/records_dashboard.php';
    } else {
        // Default to role-based dashboard
        $role = $_SESSION['role'] ?? '';
        if ($role == 'admin') {
            require_once __DIR__ . '/pages/admin_dashboard.php';
        } elseif ($role == 'student') {
            require_once __DIR__ . '/pages/student_dashboard.php';
        } elseif ($role == 'dean') {
            require_once __DIR__ . '/pages/dean_dashboard.php';
        } elseif ($role == 'finance') {
            require_once __DIR__ . '/pages/finance_dashboard.php';
        } elseif ($role == 'teacher') {
            require_once __DIR__ . '/pages/teacher_dashboard.php';
        } elseif ($role == 'cashier') {
            require_once __DIR__ . '/pages/cashier_dashboard.php';
        } elseif ($role == 'records') {
            require_once __DIR__ . '/pages/records_dashboard.php';
        } else {
            require_once __DIR__ . '/pages/student_dashboard.php';
        }
    }
} else {
    // User is not logged in
    if ($action == 'register') {
        require_once __DIR__ . '/pages/register.php';
    } else {
        require_once __DIR__ . '/pages/login.php';
    }
}
?>