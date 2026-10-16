<?php
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "school_system";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// =====================================================
// AUTO-CREATE TABLES
// =====================================================
$conn->query("CREATE TABLE IF NOT EXISTS school_years (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(20) NOT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS semesters (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(20) NOT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS subjects (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, units INT NOT NULL, prerequisite_id INT DEFAULT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS student_subjects (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, subject_id INT NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL, status VARCHAR(20) DEFAULT 'Enrolled')");
$conn->query("CREATE TABLE IF NOT EXISTS student_accounts (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL, total_fees DECIMAL(10,2) DEFAULT 0, amount_paid DECIMAL(10,2) DEFAULT 0, balance DECIMAL(10,2) DEFAULT 0, permit_status VARCHAR(20) DEFAULT 'Pending', discount_type ENUM('none','fixed','percent') DEFAULT 'none', discount_value DECIMAL(10,2) DEFAULT 0)");
$conn->query("CREATE TABLE IF NOT EXISTS courses (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, department VARCHAR(100) NOT NULL, max_units INT DEFAULT 24)");
$conn->query("CREATE TABLE IF NOT EXISTS course_subjects (id INT AUTO_INCREMENT PRIMARY KEY, course_id INT NOT NULL, subject_id INT NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL, yearlevel INT DEFAULT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS teacher_assignments (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, subject_id INT NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL, schedule VARCHAR(100) DEFAULT '', room VARCHAR(50) DEFAULT '')");
$conn->query("CREATE TABLE IF NOT EXISTS departments (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS system_settings (id INT AUTO_INCREMENT PRIMARY KEY, fee_per_unit DECIMAL(10,2) DEFAULT 500.00, misc_fee DECIMAL(10,2) DEFAULT 50.00)");
$conn->query("CREATE TABLE IF NOT EXISTS activity_logs (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT DEFAULT NULL, action_text VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS sections (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, course_code VARCHAR(20) NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS section_subjects (id INT AUTO_INCREMENT PRIMARY KEY, section_id INT NOT NULL, subject_id INT NOT NULL)");
$conn->query("CREATE TABLE IF NOT EXISTS payments (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, sy_id INT NOT NULL, sem_id INT NOT NULL, amount DECIMAL(10,2) NOT NULL, date_paid TIMESTAMP DEFAULT CURRENT_TIMESTAMP, cashier_id INT, or_number VARCHAR(50))");
$conn->query("CREATE TABLE IF NOT EXISTS grades (id INT AUTO_INCREMENT PRIMARY KEY, student_subject_id INT NOT NULL, grade_1st DECIMAL(5,2) DEFAULT NULL, grade_2nd DECIMAL(5,2) DEFAULT NULL, grade_3rd DECIMAL(5,2) DEFAULT NULL, grade_final DECIMAL(5,2) DEFAULT NULL, remarks VARCHAR(50) DEFAULT NULL)");

// Users table
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    role_id INT NOT NULL DEFAULT 2, 
    username VARCHAR(100) NOT NULL, 
    password VARCHAR(255) NOT NULL, 
    email VARCHAR(150) NOT NULL, 
    first_name VARCHAR(100) NOT NULL, 
    middle_name VARCHAR(100) DEFAULT NULL, 
    last_name VARCHAR(100) NOT NULL, 
    suffix VARCHAR(10) DEFAULT NULL, 
    birthdate DATE DEFAULT NULL, 
    gender ENUM('Male','Female') DEFAULT NULL, 
    contact_number VARCHAR(20) DEFAULT NULL, 
    address TEXT DEFAULT NULL, 
    department VARCHAR(100) DEFAULT NULL, 
    course VARCHAR(100) DEFAULT NULL, 
    year_level INT DEFAULT NULL, 
    profile_pic VARCHAR(255) DEFAULT 'default.png', 
    is_approved TINYINT(1) DEFAULT 0, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
)");

// Roles table
$conn->query("CREATE TABLE IF NOT EXISTS roles (id INT AUTO_INCREMENT PRIMARY KEY, role_name VARCHAR(50) NOT NULL)");

// Column Checks for Updates
$col1 = $conn->query("SHOW COLUMNS FROM subjects LIKE 'prerequisite_id'"); if($col1->num_rows == 0) $conn->query("ALTER TABLE subjects ADD prerequisite_id INT DEFAULT NULL");
$col2 = $conn->query("SHOW COLUMNS FROM courses LIKE 'max_units'"); if($col2->num_rows == 0) $conn->query("ALTER TABLE courses ADD max_units INT DEFAULT 24");
$col3 = $conn->query("SHOW COLUMNS FROM student_accounts LIKE 'discount_type'"); if($col3->num_rows == 0) $conn->query("ALTER TABLE student_accounts ADD COLUMN discount_type ENUM('none','fixed','percent') DEFAULT 'none', ADD COLUMN discount_value DECIMAL(10,2) DEFAULT 0");

// FIX: Ensure Payments table has columns
$pay_check = $conn->query("SHOW COLUMNS FROM payments LIKE 'amount'");
if($pay_check->num_rows > 0) {
    $or_check = $conn->query("SHOW COLUMNS FROM payments LIKE 'or_number'");
    if($or_check->num_rows == 0) {
        $conn->query("ALTER TABLE payments ADD COLUMN or_number VARCHAR(50) AFTER date_paid, ADD COLUMN cashier_id INT AFTER or_number");
    }
}

$sy_check = $conn->query("SELECT COUNT(*) as total FROM school_years");
if ($sy_check->fetch_assoc()['total'] == 0) {
    $conn->query("INSERT INTO school_years (name) VALUES ('2023-2024'), ('2024-2025')");
    $conn->query("INSERT INTO semesters (name) VALUES ('1st Semester'), ('2nd Semester')");
    $conn->query("INSERT INTO subjects (code, name, units) VALUES ('IT 101', 'Introduction to Computing', 3), ('IT 102', 'Computer Programming 1', 3), ('IT 103', 'Web Development', 3), ('MATH 101', 'College Algebra', 3), ('ENG 101', 'English Communication', 3)");
    $conn->query("INSERT INTO courses (code, name, department, max_units) VALUES ('BSIT','BS Information Technology','College of IT', 24), ('BSCS','BS Computer Science','College of IT', 25), ('BSBA','BS Business Administration','College of Business', 21)");
    $conn->query("INSERT INTO departments (name) VALUES ('College of IT'), ('College of Business'), ('College of Engineering'), ('College of Arts')");
    $conn->query("INSERT INTO system_settings (fee_per_unit, misc_fee) VALUES (500.00, 50.00)");
    $conn->query("INSERT INTO roles (role_name) VALUES ('admin'), ('student'), ('dean'), ('finance'), ('teacher'), ('cashier'), ('records')");
}

// Ensure roles exist
$roles_check = $conn->query("SELECT COUNT(*) as total FROM roles");
if ($roles_check->fetch_assoc()['total'] == 0) {
    $conn->query("INSERT INTO roles (role_name) VALUES ('admin'), ('student'), ('dean'), ('finance'), ('teacher'), ('cashier'), ('records')");
}

// Create default admin user if not exists
$admin_check = $conn->query("SELECT id FROM users WHERE username='admin'");
if ($admin_check->num_rows == 0) {
    $admin_pass = password_hash("admin123", PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (role_id, username, password, email, first_name, middle_name, last_name, is_approved) VALUES (1, 'admin', '$admin_pass', 'admin@school.edu', 'System', '', 'Administrator', 1)");
}

// FIX: Fallback for system settings if query fails or returns null
$sys_settings_query = $conn->query("SELECT * FROM system_settings LIMIT 1");
$sys_settings = $sys_settings_query ? $sys_settings_query->fetch_assoc() : ['fee_per_unit' => 500.00, 'misc_fee' => 50.00];

$all_courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
?>
