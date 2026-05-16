<?php
session_start();

// --- CSRF TOKEN GENERATION ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- CSRF VERIFICATION (Global) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<div style='color:red; font-weight:bold; text-align:center; padding:20px;'>⛔ Security Error: Invalid Request Detected. Please refresh and try again.</div>");
    }
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (isset($_GET['action']) && $_GET['action'] == 'logout') { logAction($conn, $_SESSION['user_id'] ?? 0, "Logged out"); session_destroy(); header("Location: index.php"); exit(); }

$login_msg = "";
if (isset($_POST['login_btn'])) {
    $username = $conn->real_escape_string($_POST['username']); $password = $_POST['password'];
    $result = $conn->query("SELECT u.id, u.password, u.is_approved, r.role_name, u.first_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = '$username'");
    if ($result->num_rows == 1) {
        $ud = $result->fetch_assoc();
        if (password_verify($password, $ud['password'])) {
            if ($ud['is_approved'] == 1) { $_SESSION['user_id'] = $ud['id']; $_SESSION['role'] = $ud['role_name']; $_SESSION['name'] = $ud['first_name']; logAction($conn, $ud['id'], "Logged in"); header("Location: index.php"); exit(); }
            else if ($ud['is_approved'] == 2) $login_msg = "<div class='error'>Registration declined.</div>";
            else if ($ud['is_approved'] == 3) $login_msg = "<div class='error'>Account Suspended.</div>";
            else $login_msg = "<div class='error'>Account pending approval.</div>";
        } else $login_msg = "<div class='error'>Incorrect password.</div>";
    } else $login_msg = "<div class='error'>Account not found.</div>";
}

$reg_msg = "";
if (isset($_POST['register_btn'])) {
    $profile_pic = "default.png";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) { $td="uploads/"; if(!is_dir($td))mkdir($td,0777,true); $fn=time().'_'.basename($_FILES["profile_pic"]["name"]); if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"],$td.$fn))$profile_pic=$fn; }
    
    // Check Duplicate
    $user_check = $conn->real_escape_string($_POST['username']);
    $email_check = $conn->real_escape_string($_POST['email']);
    $dup_check = $conn->query("SELECT id FROM users WHERE username='$user_check' OR email='$email_check'");
    if($dup_check === FALSE || $dup_check->num_rows > 0) {
        $reg_msg = "<div class='error'>Username or Email is already taken.</div>";
    } else {
        $sql = "INSERT INTO users (role_id, username, password, email, first_name, middle_name, last_name, suffix, birthdate, gender, contact_number, address, department, course, year_level, profile_pic, is_approved) VALUES ('".$conn->real_escape_string($_POST['role_id'])."', '".$conn->real_escape_string($_POST['username'])."', '".password_hash($_POST['password'], PASSWORD_DEFAULT)."', '".$conn->real_escape_string($_POST['email'])."', '".$conn->real_escape_string($_POST['first_name'])."', '".$conn->real_escape_string($_POST['middle_name'])."', '".$conn->real_escape_string($_POST['last_name'])."', '".$conn->real_escape_string($_POST['suffix'])."', '".$conn->real_escape_string($_POST['birthdate'])."', '".$conn->real_escape_string($_POST['gender'])."', '".$conn->real_escape_string($_POST['contact_number'])."', '".$conn->real_escape_string($_POST['address'])."', '".$_POST['department']."', '".$_POST['course']."', '".$_POST['year_level']."', '$profile_pic', 0)";
        if ($conn->query($sql)) { $reg_msg = "<div class='success'>Registration successful! Waiting for Admin Approval.</div>"; logAction($conn, NULL, "New registration"); } else $reg_msg = "<div class='error'>Database error.</div>";
    }
}

$profile_msg = "";
if (isset($_POST['update_profile_btn'])) {
    $uid = $_SESSION['user_id']; $ppu = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) { $td="uploads/"; if(!is_dir($td))mkdir($td,0777,true); $fn=time().'_'.basename($_FILES["profile_pic"]["name"]); if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"],$td.$fn))$ppu=", profile_pic='$fn'"; }
    if ($conn->query("UPDATE users SET email='".$conn->real_escape_string($_POST['email'])."', first_name='".$conn->real_escape_string($_POST['first_name'])."', middle_name='".$conn->real_escape_string($_POST['middle_name'])."', last_name='".$conn->real_escape_string($_POST['last_name'])."', suffix='".$conn->real_escape_string($_POST['suffix'])."', birthdate='".$conn->real_escape_string($_POST['birthdate'])."', gender='".$conn->real_escape_string($_POST['gender'])."', contact_number='".$conn->real_escape_string($_POST['contact_number'])."', address='".$conn->real_escape_string($_POST['address'])."', department='".$_POST['department']."', course='".$_POST['course']."', year_level='".$_POST['year_level']."' $ppu WHERE id=$uid")) { $_SESSION['name'] = $_POST['first_name']; $profile_msg = "<div class='success'>Profile updated.</div>"; }
}
?>
