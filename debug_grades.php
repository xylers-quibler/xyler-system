<?php
require_once __DIR__ . '/includes/db.php';

// Check student enrollment
echo "=== Student Enrollment Check ===\n";
$student = $conn->query("SELECT id, username, first_name, last_name FROM users WHERE username = 'student'")->fetch_assoc();
echo "Student: " . $student['username'] . " (ID: " . $student['id'] . ")\n\n";

$enrollment = $conn->query("SELECT ss.id as ss_id, s.code, s.name, ss.sy_id, ss.sem_id, ss.status 
    FROM student_subjects ss 
    JOIN subjects s ON ss.subject_id = s.id 
    WHERE ss.student_id = " . $student['id'] . " AND s.code = 'ENG 101'");
    
if($enrollment->num_rows > 0) {
    while($row = $enrollment->fetch_assoc()) {
        echo "Enrolled in: " . $row['code'] . " - " . $row['name'] . "\n";
        echo "Student Subject ID: " . $row['ss_id'] . "\n";
        echo "SY ID: " . $row['sy_id'] . ", Sem ID: " . $row['sem_id'] . "\n";
        echo "Status: " . $row['status'] . "\n\n";
    }
} else {
    echo "NOT enrolled in ENG 101\n\n";
}

// Check grades for this student_subject
echo "=== Grades Check ===\n";
if($enrollment->num_rows > 0) {
    $enrollment->data_seek(0);
    $row = $enrollment->fetch_assoc();
    $grades = $conn->query("SELECT * FROM grades WHERE student_subject_id = " . $row['ss_id']);
    if($grades->num_rows > 0) {
        while($g = $grades->fetch_assoc()) {
            echo "Grade ID: " . $g['id'] . "\n";
            echo "Student Subject ID: " . $g['student_subject_id'] . "\n";
            echo "1st: " . $g['grade_1st'] . "\n";
            echo "2nd: " . $g['grade_2nd'] . "\n";
            echo "3rd: " . $g['grade_3rd'] . "\n";
            echo "4th: " . ($g['grade_4th'] ?? 'N/A') . "\n";
            echo "Final: " . $g['grade_final'] . "\n";
            echo "Remarks: " . $g['remarks'] . "\n\n";
        }
    } else {
        echo "No grades found for student_subject_id: " . $row['ss_id'] . "\n\n";
    }
}

// Check teacher assignment
echo "=== Teacher Assignment Check ===\n";
$teacher = $conn->query("SELECT id, username FROM users WHERE username = 'teacher'")->fetch_assoc();
echo "Teacher: " . $teacher['username'] . " (ID: " . $teacher['id'] . ")\n\n";

$assignment = $conn->query("SELECT ta.*, s.code, s.name, ta.sy_id, ta.sem_id 
    FROM teacher_assignments ta 
    JOIN subjects s ON ta.subject_id = s.id 
    WHERE ta.teacher_id = " . $teacher['id'] . " AND s.code = 'ENG 101'");
    
if($assignment->num_rows > 0) {
    while($row = $assignment->fetch_assoc()) {
        echo "Assigned to: " . $row['code'] . " - " . $row['name'] . "\n";
        echo "Teacher Assignment ID: " . $row['id'] . "\n";
        echo "SY ID: " . $row['sy_id'] . ", Sem ID: " . $row['sem_id'] . "\n\n";
    }
} else {
    echo "Teacher NOT assigned to ENG 101\n\n";
}

// Check all grades table
echo "=== All Grades in Database ===\n";
$all_grades = $conn->query("SELECT g.*, ss.student_id, u.username as student_username, s.code as subject_code 
    FROM grades g 
    JOIN student_subjects ss ON g.student_subject_id = ss.id 
    JOIN users u ON ss.student_id = u.id 
    JOIN subjects s ON ss.subject_id = s.id");
    
if($all_grades->num_rows > 0) {
    while($g = $all_grades->fetch_assoc()) {
        echo "Student: " . $g['student_username'] . " | Subject: " . $g['subject_code'] . "\n";
        echo "Student Subject ID: " . $g['student_subject_id'] . "\n";
        echo "Grades: 1st=" . $g['grade_1st'] . ", 2nd=" . $g['grade_2nd'] . ", 3rd=" . $g['grade_3rd'] . ", Final=" . $g['grade_final'] . "\n";
        echo "Remarks: " . $g['remarks'] . "\n\n";
    }
} else {
    echo "No grades in database at all\n\n";
}
?>
