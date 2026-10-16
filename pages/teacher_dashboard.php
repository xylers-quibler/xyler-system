<?php
require_once __DIR__ . '/../includes/auth.php';

$teacher_msg = "";
$tid = $_SESSION['user_id'];
$tsy = isset($_GET['sy_id']) ? intval($_GET['sy_id']) : 1;
$tsem = isset($_GET['sem_id']) ? intval($_GET['sem_id']) : 1;
$view_ta_id = isset($_GET['view_class']) ? intval($_GET['view_class']) : 0;

if (isset($_SESSION['role']) && $_SESSION['role'] == 'teacher') {
    // Check if grade_4th column exists
    $column_check = $conn->query("SHOW COLUMNS FROM grades LIKE 'grade_4th'");
    $has_grade_4th = $column_check->num_rows > 0;
    
    if (isset($_POST['save_grades_btn'])) {
        foreach($_POST['g1'] as $ssid => $g1) {
            $g2 = $_POST['g2'][$ssid] ?? null; $g3 = $_POST['g3'][$ssid] ?? null; $g4 = $_POST['g4'][$ssid] ?? null; $gf = $_POST['gf'][$ssid] ?? null;
            
            // Auto-compute final grade if not provided
            if($gf === null || $gf === '') {
                if($has_grade_4th) {
                    $grades = array_filter([$g1, $g2, $g3, $g4], function($val) { return $val !== null && $val !== ''; });
                } else {
                    $grades = array_filter([$g1, $g2, $g3], function($val) { return $val !== null && $val !== ''; });
                }
                if(count($grades) > 0) {
                    $gf = array_sum($grades) / count($grades);
                } else {
                    $gf = 0;
                }
            }
            // Auto-compute remarks based on final grade
            if($gf >= 75) $rem = 'Passed';
            else $rem = 'Failed';
            $rem = $conn->real_escape_string($rem);
            $chk = $conn->query("SELECT id FROM grades WHERE student_subject_id=$ssid");
            if($chk->num_rows > 0) {
                if($has_grade_4th) {
                    $conn->query("UPDATE grades SET grade_1st=$g1, grade_2nd=$g2, grade_3rd=$g3, grade_4th=$g4, grade_final=$gf, remarks='$rem' WHERE student_subject_id=$ssid");
                } else {
                    $conn->query("UPDATE grades SET grade_1st=$g1, grade_2nd=$g2, grade_3rd=$g3, grade_final=$gf, remarks='$rem' WHERE student_subject_id=$ssid");
                }
            } else {
                if($has_grade_4th) {
                    $conn->query("INSERT INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks) VALUES ($ssid, $g1, $g2, $g3, $g4, $gf, '$rem')");
                } else {
                    $conn->query("INSERT INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_final, remarks) VALUES ($ssid, $g1, $g2, $g3, $gf, '$rem')");
                }
            }
        }
        $teacher_msg = "<div class='success'>Grades saved. Remarks auto-computed.</div>";
    }
}

$name = $_SESSION['name']; $role = $_SESSION['role'];
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
        <?php echo $teacher_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Faculty</span></h2>

        <div class="card">
            <form method="GET" class="form-row" style="align-items:flex-end;">
                <input type="hidden" name="action" value="teacher">
                <div class="form-group"><label>School Year</label><select name="sy_id" onchange="this.form.submit()"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($sy=$sys->fetch_assoc()) echo "<option value='".$sy['id']."' ".($tsy==$sy['id']?'selected':'').">".$sy['name']."</option>"; ?></select></div>
                <div class="form-group"><label>Semester</label><select name="sem_id" onchange="this.form.submit()"><?php $sms=$conn->query("SELECT * FROM semesters"); while($s=$sms->fetch_assoc()) echo "<option value='".$s['id']."' ".($tsem==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div>
            </form>
        </div>

        <?php if ($view_ta_id == 0): ?>
            <div class="card">
                <h3>My Class Schedule</h3>
                <table>
                    <tr><th>Code</th><th>Subject</th><th>Schedule</th><th>Room</th><th>Action</th></tr>
                    <?php 
                    $sched = $conn->query("SELECT ta.*, s.code, s.name FROM teacher_assignments ta JOIN subjects s ON ta.subject_id=s.id WHERE ta.teacher_id=$tid AND ta.sy_id=$tsy AND ta.sem_id=$tsem");
                    if($sched->num_rows > 0):
                        while($row = $sched->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['code']; ?></strong></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo htmlspecialchars($row['schedule']); ?></td>
                        <td><?php echo htmlspecialchars($row['room']); ?></td>
                        <td><a href="?action=teacher&view_class=<?php echo $row['id']; ?>&sy_id=<?php echo $tsy; ?>&sem_id=<?php echo $tsem; ?>" class="btn btn-sm btn-success">Encode Grades</a></td>
                    </tr>
                        <?php endwhile; 
                    else: echo "<tr><td colspan='5' style='text-align:center'>No classes assigned.</td></tr>"; endif; ?>
                </table>
            </div>

        <?php else: ?>
            <?php 
            $ta_info = $conn->query("SELECT ta.*, s.code, s.name, sy.name as sy_name, sem.name as sem_name FROM teacher_assignments ta JOIN subjects s ON ta.subject_id=s.id JOIN school_years sy ON ta.sy_id=sy.id JOIN semesters sem ON ta.sem_id=sem.id WHERE ta.id=$view_ta_id")->fetch_assoc();
            if(!$ta_info) { echo "<div class='error'>Class not found.</div>"; }
            else {
            ?>
            <div class="no-print" style="margin-bottom:15px;">
                <a href="?action=teacher&sy_id=<?php echo $tsy; ?>&sem_id=<?php echo $tsem; ?>" class="btn btn-sm">&larr; Back to Schedule</a>
            </div>

            <div id="printable-area">
                <div class="grade-header">
                    <h3>Class Record / Grade Sheet</h3>
                    <p><strong><?php echo $ta_info['sy_name']; ?> - <?php echo $ta_info['sem_name']; ?></strong></p>
                    <h2 style="margin:5px 0;"><?php echo $ta_info['code'] . " - " . $ta_info['name']; ?></h2>
                    <p>Schedule: <?php echo htmlspecialchars($ta_info['schedule']); ?> | Room: <?php echo htmlspecialchars($ta_info['room']); ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th style="width:25%;">Student Name</th>
                                <th style="width:8%;">1st</th>
                                <th style="width:8%;">2nd</th>
                                <th style="width:8%;">3rd</th>
                                <?php if($has_grade_4th): ?><th style="width:8%;">4th</th><?php endif; ?>
                                <th style="width:10%;">Final</th>
                                <th style="width:25%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($has_grade_4th) {
                                $students = $conn->query("SELECT ss.id as ss_id, CONCAT(u.last_name, ', ', u.first_name) as fullname, g.grade_1st, g.grade_2nd, g.grade_3rd, g.grade_4th, g.grade_final, g.remarks FROM student_subjects ss JOIN users u ON ss.student_id=u.id LEFT JOIN grades g ON g.student_subject_id = ss.id WHERE ss.subject_id=".$ta_info['subject_id']." AND ss.sy_id=".$ta_info['sy_id']." AND ss.sem_id=".$ta_info['sem_id']." AND ss.status='Enrolled' ORDER BY u.last_name ASC");
                            } else {
                                $students = $conn->query("SELECT ss.id as ss_id, CONCAT(u.last_name, ', ', u.first_name) as fullname, g.grade_1st, g.grade_2nd, g.grade_3rd, g.grade_final, g.remarks FROM student_subjects ss JOIN users u ON ss.student_id=u.id LEFT JOIN grades g ON g.student_subject_id = ss.id WHERE ss.subject_id=".$ta_info['subject_id']." AND ss.sy_id=".$ta_info['sy_id']." AND ss.sem_id=".$ta_info['sem_id']." AND ss.status='Enrolled' ORDER BY u.last_name ASC");
                            }
                            
                            if($students->num_rows > 0):
                                $ctr = 1;
                                while($s = $students->fetch_assoc()): ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $ctr++; ?></td>
                                <td><?php echo htmlspecialchars($s['fullname']); ?></td>
                                <td><input type="number" step="0.01" name="g1[<?php echo $s['ss_id']; ?>]" value="<?php echo $s['grade_1st'] ?? ''; ?>" style="width:100%;"></td>
                                <td><input type="number" step="0.01" name="g2[<?php echo $s['ss_id']; ?>]" value="<?php echo $s['grade_2nd'] ?? ''; ?>" style="width:100%;"></td>
                                <td><input type="number" step="0.01" name="g3[<?php echo $s['ss_id']; ?>]" value="<?php echo $s['grade_3rd'] ?? ''; ?>" style="width:100%;"></td>
                                <?php if($has_grade_4th): ?><td><input type="number" step="0.01" name="g4[<?php echo $s['ss_id']; ?>]" value="<?php echo $s['grade_4th'] ?? ''; ?>" style="width:100%;"></td><?php endif; ?>
                                <td><input type="number" step="0.01" name="gf[<?php echo $s['ss_id']; ?>]" value="<?php echo $s['grade_final'] ?? ''; ?>" style="width:100%;"></td>
                                <td><input type="text" name="remarks[<?php echo $s['ss_id']; ?>]" value="<?php echo htmlspecialchars($s['remarks'] ?? ''); ?>" style="width:100%;"></td>
                            </tr>
                                <?php endwhile; 
                            else: echo "<tr><td colspan='".($has_grade_4th?8:7)."' style='text-align:center'>No students enrolled yet.</td></tr>"; endif; ?>
                        </tbody>
                    </table>
                
                <div class="no-print" style="margin-top:20px; text-align:right;">
                    <button type="submit" name="save_grades_btn" class="btn btn-success">Save Grades</button>
                    <button onclick="window.print()" class="btn btn-primary">Print Grade Sheet</button>
                </div>
                </form>
            </div>
            <?php } ?>
        <?php endif; ?>
    </div>
</body>
</html>
