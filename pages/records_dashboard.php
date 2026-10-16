<?php
require_once __DIR__ . '/../includes/auth.php';

$records_msg = "";
$r_view_stud = null; $r_transcript = null;

if (isset($_SESSION['role']) && $_SESSION['role'] == 'records') {
    if (isset($_POST['r_search_btn'])) {
        $term = $conn->real_escape_string($_POST['search_term']);
        $r_results = $conn->query("SELECT * FROM users WHERE role_id=2 AND is_approved=1 AND (first_name LIKE '%$term%' OR last_name LIKE '%$term%' OR username LIKE '%$term%')");
    }
    if (isset($_GET['view_stud'])) {
        $sid = intval($_GET['view_stud']);
        $r_view_stud = $conn->query("SELECT * FROM users WHERE id=$sid")->fetch_assoc();
        // Get all subjects taken regardless of SY and sem
        $r_transcript = $conn->query("SELECT ss.*, s.code, s.name as subject_name, s.units, sy.name as sy_name, sem.name as sem_name, g.grade_1st, g.grade_2nd, g.grade_3rd, g.grade_4th, g.grade_final, g.remarks 
            FROM student_subjects ss 
            JOIN subjects s ON ss.subject_id = s.id 
            JOIN school_years sy ON ss.sy_id = sy.id 
            JOIN semesters sem ON ss.sem_id = sem.id 
            LEFT JOIN grades g ON g.student_subject_id = ss.id 
            WHERE ss.student_id = $sid AND ss.status = 'Enrolled'
            ORDER BY sy.name DESC, sem.id DESC, s.code ASC");
    }
    if (isset($_POST['edit_student_btn'])) {
        $sid = intval($_POST['edit_sid']);
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $middle_name = $conn->real_escape_string($_POST['middle_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $suffix = $conn->real_escape_string($_POST['suffix']);
        $birthdate = $conn->real_escape_string($_POST['birthdate']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $contact_number = $conn->real_escape_string($_POST['contact_number']);
        $address = $conn->real_escape_string($_POST['address']);
        $department = $conn->real_escape_string($_POST['department']);
        $course = $conn->real_escape_string($_POST['course']);
        
        // Handle profile picture upload
        $profile_pic = $_POST['current_profile_pic'];
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if(in_array($ext, $allowed)) {
                $new_filename = 'profile_' . $sid . '_' . time() . '.' . $ext;
                $upload_path = __DIR__ . '/../uploads/' . $new_filename;
                
                // Create uploads directory if it doesn't exist
                if(!is_dir(__DIR__ . '/../uploads')) {
                    mkdir(__DIR__ . '/../uploads', 0777, true);
                }
                
                if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    $profile_pic = $new_filename;
                }
            }
        }
        
        // Handle password update if provided
        $password_field = '';
        if(!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_field = ", password='$password'";
        }
        
        $sql = "UPDATE users SET username='$username', email='$email', first_name='$first_name', middle_name='$middle_name', last_name='$last_name', suffix='$suffix', birthdate='$birthdate', gender='$gender', contact_number='$contact_number', address='$address', department='$department', course='$course', profile_pic='$profile_pic'$password_field WHERE id=$sid";
        if($conn->query($sql)) {
            $records_msg = "<div class='success'>Student info updated.</div>";
            $r_view_stud = $conn->query("SELECT * FROM users WHERE id=$sid")->fetch_assoc();
        } else {
            $records_msg = "<div class='error'>Error updating student info: " . $conn->error . "</div>";
        }
    }
    if (isset($_POST['edit_grade_btn'])) {
        $ssid = intval($_POST['edit_ssid']);
        $g1 = floatval($_POST['grade_1st']);
        $g2 = floatval($_POST['grade_2nd']);
        $g3 = floatval($_POST['grade_3rd']);
        $g4 = floatval($_POST['grade_4th']);
        
        // ALWAYS auto-compute final grade from 1st, 2nd, 3rd, 4th grades
        $grades = array_filter([$g1, $g2, $g3, $g4], function($val) { return $val !== null && $val !== ''; });
        if(count($grades) > 0) {
            $gf = array_sum($grades) / count($grades);
        } else {
            $gf = 0;
        }
        
        // ALWAYS auto-compute remarks based on final grade (75+ = PASSED, below 75 = FAILED)
        if($gf >= 75) $rem = 'PASSED';
        else $rem = 'FAILED';
        $rem = $conn->real_escape_string($rem);
        $chk = $conn->query("SELECT id FROM grades WHERE student_subject_id=$ssid");
        if($chk->num_rows > 0) {
            $conn->query("UPDATE grades SET grade_1st=$g1, grade_2nd=$g2, grade_3rd=$g3, grade_4th=$g4, grade_final=$gf, remarks='$rem' WHERE student_subject_id=$ssid");
        } else {
            $conn->query("INSERT INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks) VALUES ($ssid, $g1, $g2, $g3, $g4, $gf, '$rem')");
        }
        $records_msg = "<div class='success'>Grade updated. Remarks auto-computed.</div>";
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
    <style>
        .print-only { display: none; }
        @media print {
            .no-print { display: none !important; }
            .header-links, .btn { display: none !important; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .print-only { display: block !important; }
            #tor-print-view { 
                display: block !important; 
                border: none !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 20px !important;
            }
            .dashboard-container > div:not(#tor-print-view) { display: none !important; }
            .header { display: none !important; }
        }
    </style>
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
        <?php echo $records_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Records</span></h2>

        <?php if (!isset($_GET['view_stud'])): ?>
        <div class="card">
            <h3>Search Student</h3>
            <form method="POST" class="form-row" style="max-width:500px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group" style="flex:2;"><input type="text" name="search_term" placeholder="Search by name or username..." required></div>
                <div class="form-group"><button type="submit" name="r_search_btn" class="btn">Search</button></div>
            </form>
        </div>

        <?php if(isset($r_results) && $r_results->num_rows > 0): ?>
        <div class="card">
            <h3>Search Results</h3>
            <table>
                <tr><th>Name</th><th>Username</th><th>Course</th><th>Action</th></tr>
                <?php while($r=$r_results->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                    <td><?php echo htmlspecialchars($r['course']); ?></td>
                    <td><a href="?action=records&view_stud=<?php echo $r['id']; ?>" class="btn btn-sm btn-success">View TOR</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <button onclick="window.location.href='?action=records'" class="btn btn-sm no-print" style="margin-bottom:15px;">&larr; Back to Search</button>

        <?php if($r_view_stud): ?>
        <div class="card">
            <div class="form-row" style="justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="border:none; margin:0;">Transcript of Records</h3>
                    <p style="color:#666;">
                        <strong>Name:</strong> <?php echo htmlspecialchars($r_view_stud['first_name'].' '.$r_view_stud['middle_name'].' '.$r_view_stud['last_name'].($r_view_stud['suffix']?', '.$r_view_stud['suffix']:'')); ?><br>
                        <strong>Course:</strong> <?php echo $r_view_stud['course']; ?> | <strong>Year Level:</strong> <?php echo $r_view_stud['year_level']; ?>
                    </p>
                </div>
                <div class="no-print" style="display:flex; gap:10px;">
                    <button onclick="printTOR()" class="btn btn-primary">Print TOR</button>
                    <button onclick="viewPDF()" class="btn btn-success">PDF View</button>
                </div>
            </div>
        </div>

        <!-- TOR Print/PDF View -->
        <div id="tor-print-view" class="print-only" style="display:none; padding:40px; background:white; border:1px solid #ddd; margin:20px 0;">
            <div style="text-align:center; margin-bottom:30px;">
                <h2 style="margin:0; font-size:24px;">OFFICIAL TRANSCRIPT OF RECORDS</h2>
                <p style="margin:5px 0; font-size:14px;">School Management System</p>
                <hr style="border:1px solid #000; margin:20px 0;">
            </div>
            <div style="margin-bottom:30px; font-size:14px;">
                <p style="margin:5px 0;"><strong>Student Name:</strong> <?php echo htmlspecialchars($r_view_stud['first_name'].' '.$r_view_stud['middle_name'].' '.$r_view_stud['last_name'].($r_view_stud['suffix']?', '.$r_view_stud['suffix']:'')); ?></p>
                <p style="margin:5px 0;"><strong>Course:</strong> <?php echo $r_view_stud['course']; ?></p>
                <p style="margin:5px 0;"><strong>Year Level:</strong> <?php echo $r_view_stud['year_level']; ?></p>
                <p style="margin:5px 0;"><strong>Date Generated:</strong> <?php echo date('F j, Y'); ?></p>
            </div>
            <table style="width:100%; border-collapse:collapse; margin-bottom:30px; font-size:12px;">
                <thead>
                    <tr style="background:#f5f5f5; border-bottom:2px solid #000;">
                        <th style="border:1px solid #000; padding:8px; text-align:center;">SY</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Sem</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Code</th>
                        <th style="border:1px solid #000; padding:8px; text-align:left;">Subject</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Units</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">1st</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">2nd</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">3rd</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">4th</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Final</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(isset($r_transcript) && $r_transcript->num_rows > 0):
                        $r_transcript->data_seek(0);
                        while($t=$r_transcript->fetch_assoc()): ?>
                    <tr>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['sy_name']; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['sem_name']; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['code']; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:left;"><?php echo $t['subject_name']; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['units']; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['grade_1st'] ?? '-'; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['grade_2nd'] ?? '-'; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['grade_3rd'] ?? '-'; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo $t['grade_4th'] ?? '-'; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center; font-weight:bold;"><?php echo $t['grade_final'] ?? '-'; ?></td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;"><?php echo htmlspecialchars($t['remarks'] ?? '-'); ?></td>
                    </tr>
                    <?php endwhile; 
                    else: ?>
                    <tr>
                        <td colspan="11" style="border:1px solid #000; padding:20px; text-align:center;">No subjects found for this student.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:50px; text-align:right; font-size:14px;">
                <p style="margin:0;">_________________________</p>
                <p style="margin:5px 0;">Registrar / Records Officer</p>
            </div>
        </div>

        <div class="card">
            <h4>All Subjects Taken</h4>
            <?php if($r_transcript && $r_transcript->num_rows > 0): ?>
            <?php $r_transcript->data_seek(0); ?>
            <table>
                <tr><th>SY</th><th>Sem</th><th>Code</th><th>Subject</th><th>Units</th><th>1st</th><th>2nd</th><th>3rd</th><th>4th</th><th>Final</th><th>Remarks</th><th class="no-print">Action</th></tr>
                <?php while($t=$r_transcript->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $t['sy_name']; ?></td>
                    <td><?php echo $t['sem_name']; ?></td>
                    <td><?php echo $t['code']; ?></td>
                    <td><?php echo $t['subject_name']; ?></td>
                    <td style="text-align:center;"><?php echo $t['units']; ?></td>
                    <td style="text-align:center;"><?php echo $t['grade_1st'] ?? '-'; ?></td>
                    <td style="text-align:center;"><?php echo $t['grade_2nd'] ?? '-'; ?></td>
                    <td style="text-align:center;"><?php echo $t['grade_3rd'] ?? '-'; ?></td>
                    <td style="text-align:center;"><?php echo $t['grade_4th'] ?? '-'; ?></td>
                    <td style="text-align:center;"><?php echo $t['grade_final'] ?? '-'; ?></td>
                    <td><?php echo htmlspecialchars($t['remarks'] ?? '-'); ?></td>
                    <td class="no-print">
                        <button type="button" onclick="openGradeModal(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['subject_name']); ?>', <?php echo $t['grade_1st'] ?? 0; ?>, <?php echo $t['grade_2nd'] ?? 0; ?>, <?php echo $t['grade_3rd'] ?? 0; ?>, <?php echo $t['grade_4th'] ?? 0; ?>)" class="btn btn-sm btn-warning">Edit Grade</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: echo "<small>No subjects found.</small>"; endif; ?>
        </div>

        <div class="card no-print">
            <h4>Edit Student Information</h4>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="edit_sid" value="<?php echo $r_view_stud['id']; ?>">
                <input type="hidden" name="current_profile_pic" value="<?php echo $r_view_stud['profile_pic']; ?>">
                
                <div class="form-group">
                    <label>Profile Picture</label>
                    <div class="profile-pic-container" style="text-align:center; margin-bottom:10px;">
                        <img src="<?php if($r_view_stud['profile_pic'] && $r_view_stud['profile_pic'] != 'default.png' && file_exists(__DIR__ . '/../uploads/' . $r_view_stud['profile_pic'])) echo 'uploads/' . $r_view_stud['profile_pic']; else echo 'https://via.placeholder.com/120'; ?>" id="editPicPreview" class="profile-pic-preview" style="width:120px; height:120px; object-fit:cover; border-radius:50%; border:3px solid #ddd;">
                    </div>
                    <input type="file" name="profile_pic" id="edit_profile_pic" accept="image/*" onchange="previewEditPic(this)">
                </div>
                
                <fieldset><legend>Account</legend>
                    <div class="form-row">
                        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?php echo $r_view_stud['username']; ?>" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo $r_view_stud['email']; ?>" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Leave blank to keep current"></div>
                        <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" placeholder="Leave blank to keep current"></div>
                    </div>
                </fieldset>
                
                <fieldset><legend>Personal</legend>
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?php echo $r_view_stud['first_name']; ?>" required></div>
                        <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" value="<?php echo $r_view_stud['middle_name']; ?>"></div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $r_view_stud['last_name']; ?>" required></div>
                        <div class="form-group" style="flex:0.3;"><label>Suffix</label><select name="suffix"><option value="">None</option><option value="Jr" <?php echo $r_view_stud['suffix']=='Jr'?'selected':''; ?>>Jr</option><option value="Sr" <?php echo $r_view_stud['suffix']=='Sr'?'selected':''; ?>>Sr</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Birthdate</label><input type="date" name="birthdate" value="<?php echo $r_view_stud['birthdate']; ?>" required></div>
                        <div class="form-group"><label>Gender</label><select name="gender" required><option value="">Select...</option><option value="Male" <?php echo $r_view_stud['gender']=='Male'?'selected':''; ?>>Male</option><option value="Female" <?php echo $r_view_stud['gender']=='Female'?'selected':''; ?>>Female</option></select></div>
                        <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" value="<?php echo $r_view_stud['contact_number']; ?>"></div>
                    </div>
                    <div class="form-group"><label>Address</label><textarea name="address" rows="2"><?php echo $r_view_stud['address']; ?></textarea></div>
                </fieldset>
                
                <fieldset><legend>Assignment</legend>
                    <div class="form-row">
                        <div class="form-group"><label>Department</label><select name="department"><option value="">Select...</option><?php $dp=$conn->query("SELECT * FROM departments ORDER BY name"); while($d=$dp->fetch_assoc()) echo "<option value='".$d['name']."' ".($r_view_stud['department']==$d['name']?'selected':'').">".$d['name']."</option>"; ?></select></div>
                        <div class="form-group"><label>Course</label><select name="course"><option value="">Select...</option><?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()) echo "<option value='".$c['code']."' ".($r_view_stud['course']==$c['code']?'selected':'').">".$c['code']." - ".$c['name']."</option>"; ?></select></div>
                    </div>
                </fieldset>
                
                <button type="submit" name="edit_student_btn" class="btn btn-success">Update Info</button>
            </form>
        </div>

        <!-- Grade Edit Modal -->
        <div id="gradeEditModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
            <div style="background:white;padding:20px;margin:100px auto;width:500px;max-width:90%;">
                <h4>Edit Grades</h4>
                <p><strong>Subject:</strong> <span id="editSubjectName"></span></p>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="edit_ssid" id="editSSID">
                    <div class="form-row">
                        <div class="form-group"><label>1st Grading</label><input type="number" step="0.01" name="grade_1st" id="editG1" required></div>
                        <div class="form-group"><label>2nd Grading</label><input type="number" step="0.01" name="grade_2nd" id="editG2" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>3rd Grading</label><input type="number" step="0.01" name="grade_3rd" id="editG3" required></div>
                        <div class="form-group"><label>4th Grading</label><input type="number" step="0.01" name="grade_4th" id="editG4" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Final Grade</label><input type="number" step="0.01" name="grade_final" id="editGF" readonly style="background:#f5f5f5;"></div>
                        <div class="form-group"><label>Remarks</label><input type="text" name="remarks" id="editRemarks" readonly style="background:#f5f5f5;"></div>
                    </div>
                    <p style="font-size:12px;color:#666;">* Final grade and remarks are auto-calculated (75+ = PASSED, below 75 = FAILED)</p>
                    <div style="margin-top:15px;">
                        <button type="submit" name="edit_grade_btn" class="btn btn-success">Save Changes</button>
                        <button type="button" onclick="closeGradeModal()" class="btn" style="background:#6c757d;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        function printTOR() {
            var printView = document.getElementById('tor-print-view');
            printView.style.display = 'block';
            window.print();
            printView.style.display = 'none';
        }
        
        function viewPDF() {
            var printView = document.getElementById('tor-print-view');
            var originalContent = document.body.innerHTML;
            
            // Hide everything except the print view
            document.body.innerHTML = '';
            document.body.appendChild(printView);
            printView.style.display = 'block';
            printView.style.border = 'none';
            
            // Print (user can save as PDF)
            window.print();
            
            // Restore original content
            document.body.innerHTML = originalContent;
            printView.style.display = 'none';
            printView.style.border = '1px solid #ddd';
        }
        
        function previewEditPic(input) {
            if(input.files && input.files[0]) {
                var r = new FileReader();
                r.onload = function(e) {
                    document.getElementById('editPicPreview').src = e.target.result;
                }
                r.readAsDataURL(input.files[0]);
            }
        }

        function openGradeModal(ssid, subjectName, g1, g2, g3, g4) {
            document.getElementById('editSSID').value = ssid;
            document.getElementById('editSubjectName').textContent = subjectName;
            document.getElementById('editG1').value = g1 || 0;
            document.getElementById('editG2').value = g2 || 0;
            document.getElementById('editG3').value = g3 || 0;
            document.getElementById('editG4').value = g4 || 0;
            calculateFinalGrade();
            document.getElementById('gradeEditModal').style.display = 'block';
        }

        function closeGradeModal() {
            document.getElementById('gradeEditModal').style.display = 'none';
        }

        function calculateFinalGrade() {
            var g1 = parseFloat(document.getElementById('editG1').value) || 0;
            var g2 = parseFloat(document.getElementById('editG2').value) || 0;
            var g3 = parseFloat(document.getElementById('editG3').value) || 0;
            var g4 = parseFloat(document.getElementById('editG4').value) || 0;
            
            var grades = [g1, g2, g3, g4].filter(function(val) { return val !== null && val !== '' && val !== 0; });
            var finalGrade = 0;
            
            if(grades.length > 0) {
                finalGrade = grades.reduce(function(a, b) { return a + b; }, 0) / grades.length;
            }
            
            document.getElementById('editGF').value = finalGrade.toFixed(2);
            
            if(finalGrade >= 75) {
                document.getElementById('editRemarks').value = 'PASSED';
            } else {
                document.getElementById('editRemarks').value = 'FAILED';
            }
        }

        // Add event listeners to grade inputs for auto-calculation
        document.addEventListener('DOMContentLoaded', function() {
            var gradeInputs = ['editG1', 'editG2', 'editG3', 'editG4'];
            gradeInputs.forEach(function(id) {
                var input = document.getElementById(id);
                if(input) {
                    input.addEventListener('input', calculateFinalGrade);
                    input.addEventListener('change', calculateFinalGrade);
                }
            });
        });
    </script>
</body>
</html>
