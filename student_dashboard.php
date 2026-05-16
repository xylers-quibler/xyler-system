<?php
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && isset($_GET['action']) && $_GET['action'] === 'print_exam_permit') {
    $pe_sy = intval($_GET['sy_id'] ?? 0);
    $pe_sem = intval($_GET['sem_id'] ?? 0);
    $pe_sid = (int) $_SESSION['user_id'];
    if ($pe_sy < 1 || $pe_sem < 1) {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title><link rel="stylesheet" href="assets/css/style.css"></head><body><div class="dashboard-container"><p>Invalid school year or semester.</p><a href="index.php" class="btn">Back</a></div></body></html>';
        exit;
    }
    $pe_acc = $conn->query("SELECT * FROM student_accounts WHERE student_id=$pe_sid AND sy_id=$pe_sy AND sem_id=$pe_sem")->fetch_assoc();
    $pe_user = $conn->query("SELECT first_name, last_name, course, year_level FROM users WHERE id=$pe_sid")->fetch_assoc();
    $pe_sy_row = $conn->query("SELECT name FROM school_years WHERE id=$pe_sy")->fetch_assoc();
    $pe_sem_row = $conn->query("SELECT name FROM semesters WHERE id=$pe_sem")->fetch_assoc();
    $pe_due = $pe_acc ? max(0, (float) $pe_acc['total_fees'] - (float) $pe_acc['amount_paid']) : 1;
    if (!$pe_acc || $pe_due > 0) {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Exam Permit</title><link rel="stylesheet" href="assets/css/style.css"></head><body><div class="dashboard-container"><p>You must have a zero balance before printing an exam permit.</p><a href="index.php" class="btn">Back</a></div></body></html>';
        exit;
    }
    $pe_name = htmlspecialchars(($pe_user['first_name'] ?? '') . ' ' . ($pe_user['last_name'] ?? ''));
    $pe_course = htmlspecialchars($pe_user['course'] ?? '');
    $pe_year = htmlspecialchars((string) ($pe_user['year_level'] ?? ''));
    $pe_sy_name = htmlspecialchars($pe_sy_row['name'] ?? '');
    $pe_sem_name = htmlspecialchars($pe_sem_row['name'] ?? '');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Permit</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="no-print" style="text-align:center; margin:16px;">
        <button type="button" onclick="window.print()" class="btn btn-success">Print</button>
        <a href="index.php" class="btn">Close</a>
    </div>
    <div id="printable-area">
        <h2 style="text-align:center; margin-bottom:8px;">Examination Permit</h2>
        <p style="text-align:center; font-size:14px; margin-bottom:24px;">School Management System</p>
        <p><strong>Student:</strong> <?php echo $pe_name; ?></p>
        <p><strong>Course / Year:</strong> <?php echo $pe_course; ?> — <?php echo $pe_year; ?></p>
        <p><strong>Term:</strong> <?php echo $pe_sy_name; ?> — <?php echo $pe_sem_name; ?></p>
        <hr style="margin:20px 0;">
        <p style="font-size:18px;"><strong>Status:</strong> APPROVED — Amount due: ₱<?php echo number_format($pe_due, 2); ?></p>
        <p style="margin-top:24px;">This student is cleared to take examinations for the term indicated above.</p>
        <p style="margin-top:40px; font-size:12px;">Generated <?php echo date('F j, Y g:i a'); ?></p>
    </div>
</body>
</html>
    <?php
    exit;
}

// Load system settings from database
$sys_settings_query = $conn->query("SELECT * FROM system_settings WHERE id=1");
if ($sys_settings_query && $sys_settings_query->num_rows > 0) {
    $sys_settings = $sys_settings_query->fetch_assoc();
} else {
    $sys_settings = ['fee_per_unit' => 500.00, 'misc_fee' => 50.00];
}

$name = $_SESSION['name']; $role = $_SESSION['role'];
$sd=$conn->query("SELECT course, year_level FROM users WHERE id=".$_SESSION['user_id'])->fetch_assoc();
$sm=24; if($sd['course']){ $cm=$conn->query("SELECT max_units FROM courses WHERE code='".$sd['course']."'")->fetch_assoc(); if($cm)$sm=$cm['max_units']; }
// Default to most recent school year and first semester
$default_sy = $conn->query("SELECT id FROM school_years ORDER BY name DESC LIMIT 1")->fetch_assoc()['id'] ?? 1;
$asy=isset($_GET['sy_id'])?$_GET['sy_id']:$default_sy; $asem=isset($_GET['sem_id'])?$_GET['sem_id']:1; $csid=$_SESSION['user_id'];

// Get student's course tuition for display
$course_tuition = $conn->query("SELECT fee_per_unit, misc_fee FROM courses WHERE code='".$conn->real_escape_string($sd['course'])."'")->fetch_assoc();

// Recalculate student account if enrolled subjects exist but account is missing or zero
$acc_check = $conn->query("SELECT * FROM student_accounts WHERE student_id=$csid AND sy_id=$asy AND sem_id=$asem");
if ($acc_check->num_rows == 0) {
    // Get student's course to use course-specific tuition
    $student_course = $conn->query("SELECT course FROM users WHERE id=$csid")->fetch_assoc();
    $course_tuition = $conn->query("SELECT fee_per_unit, misc_fee FROM courses WHERE code='".$conn->real_escape_string($student_course['course'])."'")->fetch_assoc();
    
    // Use course-specific tuition or fall back to system settings
    $fee_per_unit = $course_tuition['fee_per_unit'] ?? $sys_settings['fee_per_unit'];
    $misc_fee = $course_tuition['misc_fee'] ?? $sys_settings['misc_fee'];
    
    $subs_check = $conn->query("SELECT s.units FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=$csid AND ss.sy_id=$asy AND ss.sem_id=$asem AND ss.status='Enrolled'");
    if ($subs_check->num_rows > 0) {
        $total_units = 0;
        while ($s = $subs_check->fetch_assoc()) {
            $total_units += $s['units'];
        }
        $new_total = ($total_units * $fee_per_unit) + $misc_fee;
        $conn->query("INSERT INTO student_accounts (student_id, sy_id, sem_id, total_fees, balance) VALUES ($csid, $asy, $asem, $new_total, $new_total)");
    }
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
    <div class="header">
        <h2>School Management System</h2>
        <div class="header-links">
            <a href="?action=profile">⚙️ Profile</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Student</span></h2>
        <div class="card" style="margin-bottom:16px;">
            <h3 style="margin-top:0;">Your program</h3>
            <p style="margin:0; color:#444;"><strong>Course:</strong> <?php echo htmlspecialchars($sd['course'] ?? '—'); ?> &nbsp;|&nbsp; <strong>Year level:</strong> <?php echo htmlspecialchars((string) ($sd['year_level'] ?? '—')); ?></p>
            <p style="margin:12px 0 0; font-size:14px; color:#666;">Subjects and class schedule are assigned by the Dean's office. For enrollment changes, contact the Registrar or your program coordinator.</p>
        </div>
        <div class="card"><form method="GET" action="" class="form-row" style="align-items: flex-end;">
            <div class="form-group"><label>School Year</label><select name="sy_id" required><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($sy=$sys->fetch_assoc()){$sel=(isset($_GET['sy_id'])&&$_GET['sy_id']==$sy['id'])?'selected':''; echo "<option value='".$sy['id']."' $sel>".$sy['name']."</option>";} ?></select></div>
            <div class="form-group"><label>Semester</label><select name="sem_id" required><?php $sems=$conn->query("SELECT * FROM semesters ORDER BY id"); while($sem=$sems->fetch_assoc()){$sel=(isset($_GET['sem_id'])&&$_GET['sem_id']==$sem['id'])?'selected':''; echo "<option value='".$sem['id']."' $sel>".$sem['name']."</option>";} ?></select></div>
            <div class="form-group"><button type="submit" class="btn">View records</button></div>
        </form></div>

        <div class="alert-box">Program max units: <strong><?php echo $sm; ?></strong> (for reference) · Tuition: <strong>₱<?php echo number_format($course_tuition['fee_per_unit'] ?? $sys_settings['fee_per_unit'], 2); ?>/unit + ₱<?php echo number_format($course_tuition['misc_fee'] ?? $sys_settings['misc_fee'], 2); ?> misc</strong></div>

        <div class="card">
            <h3>📅 My class schedule</h3>
            <?php $sched=$conn->query("SELECT ta.schedule, ta.room, s.code, s.name, CONCAT(u.last_name, ', ', u.first_name) as teacher FROM student_subjects ss JOIN teacher_assignments ta ON ss.subject_id=ta.subject_id AND ss.sy_id=ta.sy_id AND ss.sem_id=ta.sem_id JOIN subjects s ON ta.subject_id=s.id JOIN users u ON ta.teacher_id=u.id WHERE ss.student_id=$csid AND ss.status='Enrolled' AND ss.sy_id=$asy AND ss.sem_id=$asem ORDER BY ta.schedule"); ?>
            <table><tr><th>Schedule</th><th>Subject</th><th>Professor</th><th>Room</th></tr>
            <?php while($sc=$sched->fetch_assoc()): ?>
            <tr><td style="font-weight:bold;"><?php echo htmlspecialchars($sc['schedule']); ?></td><td><?php echo htmlspecialchars($sc['code']); ?></td><td><?php echo htmlspecialchars($sc['teacher']); ?></td><td><?php echo htmlspecialchars($sc['room']); ?></td></tr>
            <?php endwhile; if($sched->num_rows==0) echo "<tr><td colspan='4' style='text-align:center'>No schedule for this term yet. When the Dean assigns you to subjects with a teacher schedule, it will appear here.</td></tr>"; ?>
            </table>
        </div>

        <div class="card"><h3>My subjects</h3><table><thead><tr><th>Code</th><th>Subject name</th><th>Units</th><th>Status</th></tr></thead><tbody>
            <?php $mys=$conn->query("SELECT s.code, s.name, s.units, ss.status FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE ss.student_id = $csid AND ss.sy_id = $asy AND ss.sem_id = $asem"); $tu=0;
            if($mys->num_rows > 0) { while($r=$mys->fetch_assoc()) { if($r['status']=='Enrolled') $tu+=$r['units']; echo "<tr><td>".htmlspecialchars($r['code'])."</td><td>".htmlspecialchars($r['name'])."</td><td>".$r['units']."</td><td><span class='role-badge' style='font-size:12px;'>".htmlspecialchars($r['status'])."</span></td></tr>"; } } else echo "<tr><td colspan='4' style='text-align:center;'>No subjects for this term yet.</td></tr>"; ?>
        </tbody></table>
            <?php $col=($tu>$sm)?'color:red;':'color: var(--primary-blue);'; echo "<div style='margin-top: 15px; font-weight: bold; $col'>Enrolled units: $tu / $sm</div>"; ?>
        </div>

        <div class="card"><h3>Accounts & Billing</h3>
                <?php
                $acc = $conn->query("SELECT * FROM student_accounts WHERE student_id=$csid AND sy_id=$asy AND sem_id=$asem");
                if ($acc->num_rows > 0) {
                    $ad = $acc->fetch_assoc();
                    $tf = (float) $ad['total_fees'];
                    $ap = (float) $ad['amount_paid'];
                    
                    // Calculate discount breakdown
                    $discount_amt = 0;
                    $original_total = $tf;
                    if($ad['discount_type'] == 'fixed') {
                        $discount_amt = floatval($ad['discount_value']);
                        $original_total = $tf + $discount_amt;
                    } elseif($ad['discount_type'] == 'percent') {
                        $discount_amt = $original_total * (floatval($ad['discount_value']) / 100);
                        $original_total = $tf + $discount_amt;
                    }
                    
                    $due = max(0, $tf - $ap);
                    $permit_ok = $due <= 0 ? 'Approved' : 'Pending';
                    if (abs((float) $ad['balance'] - $due) > 0.009 || strcasecmp((string) $ad['permit_status'], $permit_ok) !== 0) {
                        $aid = (int) $ad['id'];
                        $esc_permit = $conn->real_escape_string($permit_ok);
                        $conn->query("UPDATE student_accounts SET balance=$due, permit_status='$esc_permit' WHERE id=$aid");
                        $ad['balance'] = $due;
                        $ad['permit_status'] = $permit_ok;
                    }
                    $pbox = strtolower($ad['permit_status']);
                    $bal_style = $due > 0 ? 'text-align:right;color:red;font-weight:bold;' : 'text-align:right;';
                    
                    echo "<table>";
                    if($ad['discount_type'] != 'none') {
                        echo "<tr><td><strong>Original Fees:</strong></td><td style='text-align:right;'>₱ " . number_format($original_total, 2) . "</td></tr>";
                        echo "<tr><td><strong>Discount (" . ucfirst($ad['discount_type']) . " " . $ad['discount_value'] . "):</strong></td><td style='text-align:right;color:blue;'>-₱ " . number_format($discount_amt, 2) . "</td></tr>";
                    }
                    echo "<tr><td><strong>Total Fees:</strong></td><td style='text-align:right;color:red;font-weight:bold;'>₱ " . number_format($tf, 2) . "</td></tr><tr><td><strong>Paid:</strong></td><td style='text-align:right;color:green;'>₱ " . number_format($ap, 2) . "</td></tr><tr><td><strong>Balance:</strong></td><td style='" . $bal_style . "'>₱ " . number_format($due, 2) . "</td></tr></table>";
                    echo "<div class='permit-box $pbox'><h3>Exam Permit</h3><p style='font-size:24px;font-weight:bold;margin:10px 0;'>" . htmlspecialchars($ad['permit_status']) . "</p>";
                    if ($ad['permit_status'] === 'Pending') {
                        echo "<p style='font-size:12px;'>Settle balance at cashier.</p>";
                    } else {
                        echo "<p style='font-size:12px;'>Cleared.</p>";
                    }
                    if ($due <= 0) {
                        echo "<p class='no-print' style='margin-top:14px;'><a href='index.php?action=print_exam_permit&amp;sy_id=" . (int) $asy . "&amp;sem_id=" . (int) $asem . "' target='_blank' rel='noopener' class='btn btn-success btn-sm'>Print exam permit</a></p>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No billing records.</p>";
                }
                ?>
            </div>

        <div class="card"><h3>Statement of Account</h3>
                <?php
                // Get student account information
                $student_account = $conn->query("SELECT sa.*, sy.name as sy_name, sem.name as sem_name, u.year_level 
                    FROM student_accounts sa 
                    JOIN school_years sy ON sa.sy_id = sy.id 
                    JOIN semesters sem ON sa.sem_id = sem.id 
                    JOIN users u ON sa.student_id = u.id 
                    WHERE sa.student_id = $csid AND sa.sy_id = $asy AND sa.sem_id = $asem")->fetch_assoc();
                
                // Get payments
                $payments = $conn->query("SELECT p.*, sy.name as sy_name, sem.name as sem_name, u.year_level 
                    FROM payments p 
                    JOIN school_years sy ON p.sy_id = sy.id 
                    JOIN semesters sem ON p.sem_id = sem.id 
                    JOIN users u ON p.student_id = u.id 
                    WHERE p.student_id = $csid AND p.sy_id = $asy AND p.sem_id = $asem
                    ORDER BY p.date_paid ASC");
                
                echo "<table style='font-size:12px;'><tr><th>SY</th><th>Sem</th><th>Year</th><th>Description</th><th>O.R. Date</th><th>O.R. #</th><th>Debit</th><th>Credit</th><th>Balance</th></tr>";
                
                $running_balance = 0;
                
                if ($student_account) {
                    // Calculate original fees (before discount)
                    $base_total = floatval($student_account['total_fees']);
                    $discount_amt = 0;
                    if($student_account['discount_type'] == 'fixed') {
                        $discount_amt = floatval($student_account['discount_value']);
                    } elseif($student_account['discount_type'] == 'percent') {
                        $discount_amt = $base_total * (floatval($student_account['discount_value']) / 100);
                    }
                    $original_total = $base_total + $discount_amt;
                    
                    // Show original fees as starting balance
                    $running_balance = $original_total;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($student_account['sy_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($student_account['sem_name']) . "</td>";
                    echo "<td>" . $student_account['year_level'] . "</td>";
                    echo "<td>Original Fees</td>";
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                    echo "<td style='color:red;'>₱" . number_format($original_total, 2) . "</td>";
                    echo "<td>-</td>";
                    echo "<td style='font-weight:bold;'>₱" . number_format($running_balance, 2) . "</td>";
                    echo "</tr>";
                    
                    // Show tuition charge as debit
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($student_account['sy_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($student_account['sem_name']) . "</td>";
                    echo "<td>" . $student_account['year_level'] . "</td>";
                    echo "<td>Tuition & Fees</td>";
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                    echo "<td style='color:red;'>₱" . number_format($base_total, 2) . "</td>";
                    echo "<td>-</td>";
                    echo "<td style='font-weight:bold;'>₱" . number_format($running_balance, 2) . "</td>";
                    echo "</tr>";
                    
                    // Show discount as credit if applicable
                    if($student_account['discount_type'] != 'none' && $discount_amt > 0) {
                        $running_balance -= $discount_amt;
                        $discount_desc = ucfirst($student_account['discount_type']) . ' ' . $student_account['discount_value'];
                        if($student_account['discount_type'] == 'percent') $discount_desc .= '%';
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($student_account['sy_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($student_account['sem_name']) . "</td>";
                        echo "<td>" . $student_account['year_level'] . "</td>";
                        echo "<td>Scholarship Discount ($discount_desc)</td>";
                        echo "<td>-</td>";
                        echo "<td>-</td>";
                        echo "<td>-</td>";
                        echo "<td style='color:green;'>₱" . number_format($discount_amt, 2) . "</td>";
                        echo "<td style='font-weight:bold;'>₱" . number_format($running_balance, 2) . "</td>";
                        echo "</tr>";
                    }
                }
                
                // Add payments as credit entries
                if ($payments->num_rows > 0) {
                    while ($row = $payments->fetch_assoc()) {
                        $running_balance -= $row['amount'];
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['sy_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['sem_name']) . "</td>";
                        echo "<td>" . $row['year_level'] . "</td>";
                        echo "<td>Tuition Payment</td>";
                        echo "<td>" . date("M d, Y", strtotime($row['date_paid'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['or_number']) . "</td>";
                        echo "<td>-</td>";
                        echo "<td style='color:green;'>₱" . number_format($row['amount'], 2) . "</td>";
                        echo "<td style='font-weight:bold;'>₱" . number_format($running_balance, 2) . "</td>";
                        echo "</tr>";
                    }
                }
                
                echo "</table>";
                ?>
            </div>

        <div class="card"><h3>My Grades</h3>
                <?php
                // Check if grade_4th column exists
                $column_check = $conn->query("SHOW COLUMNS FROM grades LIKE 'grade_4th'");
                $has_grade_4th = $column_check->num_rows > 0;
                
                if($has_grade_4th) {
                    $grades = $conn->query("SELECT g.*, s.code, s.name as subject_name, s.units, sy.name as sy_name, sem.name as sem_name
                        FROM grades g
                        JOIN student_subjects ss ON g.student_subject_id = ss.id
                        JOIN subjects s ON ss.subject_id = s.id
                        JOIN school_years sy ON ss.sy_id = sy.id
                        JOIN semesters sem ON ss.sem_id = sem.id
                        WHERE ss.student_id = $csid AND ss.sy_id = $asy AND ss.sem_id = $asem
                        ORDER BY s.code");
                } else {
                    $grades = $conn->query("SELECT g.*, s.code, s.name as subject_name, s.units, sy.name as sy_name, sem.name as sem_name
                        FROM grades g
                        JOIN student_subjects ss ON g.student_subject_id = ss.id
                        JOIN subjects s ON ss.subject_id = s.id
                        JOIN school_years sy ON ss.sy_id = sy.id
                        JOIN semesters sem ON ss.sem_id = sem.id
                        WHERE ss.student_id = $csid AND ss.sy_id = $asy AND ss.sem_id = $asem
                        ORDER BY s.code");
                }
                
                if ($grades->num_rows > 0) {
                    if($has_grade_4th) {
                        echo "<table style='font-size:12px;'><tr><th>SY</th><th>Sem</th><th>Code</th><th>Subject</th><th>Units</th><th>1st</th><th>2nd</th><th>3rd</th><th>4th</th><th>Final</th><th>Remarks</th></tr>";
                    } else {
                        echo "<table style='font-size:12px;'><tr><th>SY</th><th>Sem</th><th>Code</th><th>Subject</th><th>Units</th><th>1st</th><th>2nd</th><th>3rd</th><th>Final</th><th>Remarks</th></tr>";
                    }
                    while ($g = $grades->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($g['sy_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($g['sem_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($g['code']) . "</td>";
                        echo "<td>" . htmlspecialchars($g['subject_name']) . "</td>";
                        echo "<td>" . $g['units'] . "</td>";
                        echo "<td>" . ($g['grade_1st'] ? number_format($g['grade_1st'], 2) : '-') . "</td>";
                        echo "<td>" . ($g['grade_2nd'] ? number_format($g['grade_2nd'], 2) : '-') . "</td>";
                        echo "<td>" . ($g['grade_3rd'] ? number_format($g['grade_3rd'], 2) : '-') . "</td>";
                        if($has_grade_4th) {
                            echo "<td>" . ($g['grade_4th'] ? number_format($g['grade_4th'], 2) : '-') . "</td>";
                        }
                        echo "<td style='font-weight:bold;'>" . ($g['grade_final'] ? number_format($g['grade_final'], 2) : '-') . "</td>";
                        echo "<td>" . ($g['remarks'] ? htmlspecialchars($g['remarks']) : '-') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No grades posted yet for selected term.</p>";
                }
                ?>
            </div>
    </div>
</body>
</html>
