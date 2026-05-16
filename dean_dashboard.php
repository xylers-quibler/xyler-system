<?php
require_once __DIR__ . '/../includes/auth.php';

$dean_msg = "";
if (isset($_SESSION['role']) && $_SESSION['role'] == 'dean') {
    if (isset($_POST['add_subject_btn'])) { $c=$conn->real_escape_string($_POST['sub_code']); $n=$conn->real_escape_string($_POST['sub_name']); $u=intval($_POST['sub_units']); $p=$conn->real_escape_string($_POST['sub_prereq'] ?? ''); $conn->query("INSERT INTO subjects (code, name, units, prerequisite) VALUES ('$c','$n',$u,'$p')"); $dean_msg="<div class='success'>Subject added.</div>"; }
    if (isset($_POST['edit_subject_btn'])) { $id=intval($_POST['edit_sub_id']); $c=$conn->real_escape_string($_POST['sub_code']); $n=$conn->real_escape_string($_POST['sub_name']); $u=intval($_POST['sub_units']); $p=$conn->real_escape_string($_POST['sub_prereq'] ?? ''); $conn->query("UPDATE subjects SET code='$c', name='$n', units=$u, prerequisite='$p' WHERE id=$id"); $dean_msg="<div class='success'>Subject updated.</div>"; }
    if (isset($_POST['delete_subject_btn'])) { $conn->query("DELETE FROM subjects WHERE id=".intval($_POST['del_sub_id'])); $dean_msg="<div class='error'>Subject deleted.</div>"; }
    if (isset($_POST['add_course_btn'])) { $c=$conn->real_escape_string($_POST['course_code']); $n=$conn->real_escape_string($_POST['course_name']); $d=$conn->real_escape_string($_POST['course_dept']); $u=intval($_POST['course_units']); $fpu=floatval($_POST['course_fee_per_unit'] ?? 500.00); $mf=floatval($_POST['course_misc_fee'] ?? 50.00); $conn->query("INSERT INTO courses (code, name, department, max_units, fee_per_unit, misc_fee) VALUES ('$c','$n','$d',$u,$fpu,$mf)"); $dean_msg="<div class='success'>Course added.</div>"; }
    if (isset($_POST['edit_course_btn'])) { $id=intval($_POST['edit_course_id']); $c=$conn->real_escape_string($_POST['course_code']); $n=$conn->real_escape_string($_POST['course_name']); $d=$conn->real_escape_string($_POST['course_dept']); $u=intval($_POST['course_units']); $fpu=floatval($_POST['course_fee_per_unit'] ?? 500.00); $mf=floatval($_POST['course_misc_fee'] ?? 50.00); $conn->query("UPDATE courses SET code='$c', name='$n', department='$d', max_units=$u, fee_per_unit=$fpu, misc_fee=$mf WHERE id=$id"); $dean_msg="<div class='success'>Course updated.</div>"; }
    if (isset($_POST['delete_course_btn'])) { $conn->query("DELETE FROM courses WHERE id=".intval($_POST['del_course_id'])); $dean_msg="<div class='error'>Course deleted.</div>"; }
    if (isset($_POST['add_section_btn'])) { $n=$conn->real_escape_string($_POST['sec_name']); $c=$conn->real_escape_string($_POST['sec_course']); $s=intval($_POST['sec_sy']); $sm=intval($_POST['sec_sem']); $conn->query("INSERT INTO sections (name, course_code, sy_id, sem_id) VALUES ('$n','$c',$s,$sm)"); $dean_msg="<div class='success'>Section created.</div>"; }
    if (isset($_POST['del_section_btn'])) { $conn->query("DELETE FROM sections WHERE id=".intval($_POST['del_sec_id'])); $dean_msg="<div class='error'>Section deleted.</div>"; }
    if (isset($_POST['add_sec_sub_btn'])) { $s=intval($_POST['sec_id']); $sub=intval($_POST['sec_sub']); $conn->query("INSERT INTO section_subjects (section_id, subject_id) VALUES ($s,$sub)"); $dean_msg="<div class='success'>Subject assigned.</div>"; }
    if (isset($_POST['del_sec_sub_btn'])) { $conn->query("DELETE FROM section_subjects WHERE id=".intval($_POST['del_sec_sub_id'])); $dean_msg="<div class='error'>Assignment removed.</div>"; }
    if (isset($_POST['add_cs_btn'])) { $c=intval($_POST['cs_course']); $sub=intval($_POST['cs_subject']); $s=intval($_POST['cs_sy']); $sm=intval($_POST['cs_sem']); $y=intval($_POST['cs_yr']); $conn->query("INSERT INTO course_subjects (course_id, subject_id, sy_id, sem_id, yearlevel) VALUES ($c,$sub,$s,$sm,$y)"); $dean_msg="<div class='success'>Subject added to offering.</div>"; }
    if (isset($_POST['del_cs_btn'])) { $conn->query("DELETE FROM course_subjects WHERE id=".intval($_POST['del_cs_id'])); $dean_msg="<div class='error'>Removed from offering.</div>"; }
    if (isset($_POST['add_ta_btn'])) { $t=intval($_POST['ta_teacher']); $sub=intval($_POST['ta_subject']); $s=intval($_POST['ta_sy']); $sm=intval($_POST['ta_sem']); $sch=$conn->real_escape_string($_POST['ta_schedule']); $r=$conn->real_escape_string($_POST['ta_room']); $conn->query("INSERT INTO teacher_assignments (teacher_id, subject_id, sy_id, sem_id, schedule, room) VALUES ($t,$sub,$s,$sm,'$sch','$r')"); $dean_msg="<div class='success'>Teacher assigned.</div>"; }
    if (isset($_POST['edit_ta_btn'])) { $id=intval($_POST['edit_ta_id']); $t=intval($_POST['ta_teacher']); $sub=intval($_POST['ta_subject']); $s=intval($_POST['ta_sy']); $sm=intval($_POST['ta_sem']); $sch=$conn->real_escape_string($_POST['ta_schedule']); $r=$conn->real_escape_string($_POST['ta_room']); $conn->query("UPDATE teacher_assignments SET teacher_id=$t, subject_id=$sub, schedule='$sch', room='$r' WHERE id=$id"); $dean_msg="<div class='success'>Assignment updated.</div>"; }
    if (isset($_POST['del_ta_btn'])) { $conn->query("DELETE FROM teacher_assignments WHERE id=".intval($_POST['del_ta_id'])); $dean_msg="<div class='error'>Assignment deleted.</div>"; }
    if (isset($_POST['assign_subject_btn'])) { $st=intval($_POST['assign_student']); $sub=intval($_POST['assign_subject']); $s=intval($_POST['assign_sy']); $sm=intval($_POST['assign_sem']); $conn->query("INSERT INTO student_subjects (student_id, subject_id, sy_id, sem_id) VALUES ($st,$sub,$s,$sm)"); $dean_msg="<div class='success'>Subject assigned to student.</div>"; }
    if (isset($_POST['remove_student_subject_btn'])) { $conn->query("DELETE FROM student_subjects WHERE id=".intval($_POST['del_ss_id'])); $dean_msg="<div class='error'>Subject removed from student.</div>"; }
}

$name = $_SESSION['name']; $role = $_SESSION['role'];
$tab = $_GET['tab'] ?? 'subjects';
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
        <?php echo $dean_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Dean</span></h2>
        <div class="dean-tabs">
            <a href="?action=dean&tab=subjects" class="btn btn-sm <?php echo $tab=='subjects'?'active':''; ?>">📚 Subjects</a>
            <a href="?action=dean&tab=courses" class="btn btn-sm <?php echo $tab=='courses'?'active':''; ?>">🎓 Courses</a>
            <a href="?action=dean&tab=blocks" class="btn btn-sm <?php echo $tab=='blocks'?'active':''; ?>">🧱 Block Sections</a>
            <a href="?action=dean&tab=enrollment" class="btn btn-sm <?php echo $tab=='enrollment'?'active':''; ?>">👥 Enrollment</a>
            <a href="?action=dean&tab=teachers" class="btn btn-sm <?php echo $tab=='teachers'?'active':''; ?>">👨‍🏫 Schedules</a>
        </div>

        <?php if($tab == 'subjects'): ?>
        <div class="card"><h3>Subjects & Prerequisites</h3>
            <?php $es = isset($_GET['edit_sub']) ? $conn->query("SELECT * FROM subjects WHERE id=".intval($_GET['edit_sub']))->fetch_assoc() : null; ?>
            <form method="POST" class="form-row" style="align-items:flex-end;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group"><label>Code</label><input type="text" name="sub_code" value="<?php echo $es['code']??''; ?>" required></div>
                <div class="form-group" style="flex:2"><label>Name</label><input type="text" name="sub_name" value="<?php echo $es['name']??''; ?>" required></div>
                <div class="form-group"><label>Units</label><input type="number" name="sub_units" value="<?php echo $es['units']??3; ?>" min="1" required></div>
                <div class="form-group" style="flex:2"><label>Prerequisite</label><input type="text" name="sub_prereq" value="<?php echo $es['prerequisite']??''; ?>" placeholder="e.g. IT 101, MATH 101, or None"></div>
                <div class="form-group"><?php if($es): ?><input type="hidden" name="edit_sub_id" value="<?php echo $es['id']; ?>"><button type="submit" name="edit_subject_btn" class="btn btn-sm">Update</button><a href="?action=dean&tab=subjects" class="btn btn-sm" style="background:#6c757d">X</a><?php else: ?><button type="submit" name="add_subject_btn" class="btn btn-sm btn-success">Add</button><?php endif; ?></div>
            </form>
            <table><tr><th>Code</th><th>Name</th><th>Units</th><th>Prereq</th><th>Action</th></tr>
            <?php $subs=$conn->query("SELECT * FROM subjects ORDER BY code"); while($s=$subs->fetch_assoc()): ?>
            <tr><td><?php echo $s['code']; ?></td><td><?php echo $s['name']; ?></td><td><?php echo $s['units']; ?></td><td><?php echo $s['prerequisite']?"<span style='color:red'>".htmlspecialchars($s['prerequisite'])."</span>":"None"; ?></td><td><a href="?action=dean&tab=subjects&edit_sub=<?php echo $s['id']; ?>" class="btn btn-sm">Edit</a><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_sub_id" value="<?php echo $s['id']; ?>"><button type="submit" name="delete_subject_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr>
            <?php endwhile; ?></table>
        </div>

        <?php elseif($tab == 'courses'): ?>
        <div class="card"><h3>Courses</h3>
            <?php $ec = isset($_GET['edit_course']) ? $conn->query("SELECT * FROM courses WHERE id=".intval($_GET['edit_course']))->fetch_assoc() : null; ?>
            <form method="POST" class="form-row" style="align-items:flex-end;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group"><label>Code</label><input type="text" name="course_code" value="<?php echo $ec['code']??''; ?>" required></div>
                <div class="form-group" style="flex:2"><label>Name</label><input type="text" name="course_name" value="<?php echo $ec['name']??''; ?>" required></div>
                <div class="form-group" style="flex:2"><label>Department</label><input type="text" name="course_dept" value="<?php echo $ec['department']??''; ?>" required></div>
                <div class="form-group"><label>Max Units</label><input type="number" name="course_units" value="<?php echo $ec['max_units']??24; ?>" min="1" required></div>
                <div class="form-group"><label>Fee/Unit (₱)</label><input type="number" step="0.01" name="course_fee_per_unit" value="<?php echo $ec['fee_per_unit']??500.00; ?>" required></div>
                <div class="form-group"><label>Misc Fee (₱)</label><input type="number" step="0.01" name="course_misc_fee" value="<?php echo $ec['misc_fee']??50.00; ?>" required></div>
                <div class="form-group"><?php if($ec): ?><input type="hidden" name="edit_course_id" value="<?php echo $ec['id']; ?>"><button type="submit" name="edit_course_btn" class="btn btn-sm">Update</button><a href="?action=dean&tab=courses" class="btn btn-sm" style="background:#6c757d">X</a><?php else: ?><button type="submit" name="add_course_btn" class="btn btn-sm btn-success">Add</button><?php endif; ?></div>
            </form>
            <table><tr><th>Code</th><th>Name</th><th>Department</th><th>Max Units</th><th>Fee/Unit</th><th>Misc Fee</th><th>Action</th></tr>
            <?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()): ?>
            <tr><td><?php echo $c['code']; ?></td><td><?php echo $c['name']; ?></td><td><?php echo $c['department']; ?></td><td><?php echo $c['max_units']; ?></td><td>₱<?php echo number_format($c['fee_per_unit'] ?? 500.00, 2); ?></td><td>₱<?php echo number_format($c['misc_fee'] ?? 50.00, 2); ?></td><td><a href="?action=dean&tab=courses&edit_course=<?php echo $c['id']; ?>" class="btn btn-sm">Edit</a><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_course_id" value="<?php echo $c['id']; ?>"><button type="submit" name="delete_course_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr>
            <?php endwhile; ?></table>
        </div>

        <?php elseif($tab == 'blocks'): ?>
        <div class="grid-2">
            <div class="card"><h3>Create Block Section</h3>
                <form method="POST" class="form-row">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group"><label>Name</label><input type="text" name="sec_name" placeholder="e.g. BSIT 1A" required></div>
                    <div class="form-group"><label>Course</label><select name="sec_course" required><?php $crs=$conn->query("SELECT * FROM courses"); while($c=$crs->fetch_assoc()) echo "<option value='".$c['code']."'>".$c['code']."</option>"; ?></select></div>
                    <div class="form-group"><label>SY</label><select name="sec_sy"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($s=$sys->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['name']."</option>"; ?></select></div>
                    <div class="form-group"><label>Sem</label><select name="sec_sem"><?php $sms=$conn->query("SELECT * FROM semesters"); while($s=$sms->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['name']."</option>"; ?></select></div>
                    <div class="form-group" style="align-self:flex-end;"><button type="submit" name="add_section_btn" class="btn btn-sm btn-success">Create</button></div>
                </form>
                <table><tr><th>Section</th><th>Course</th><th>Action</th></tr>
                <?php $secs=$conn->query("SELECT * FROM sections ORDER BY name"); while($s=$secs->fetch_assoc()): ?>
                <tr><td><?php echo $s['name']; ?></td><td><?php echo $s['course_code']; ?></td><td><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_sec_id" value="<?php echo $s['id']; ?>"><button type="submit" name="del_section_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr>
                <?php endwhile; if($secs->num_rows==0) echo "<tr><td colspan='3' style='text-align:center'>None.</td></tr>"; ?></table>
            </div>
            <div class="card"><h3>Assign Subjects to Section</h3>
                <form method="POST" class="form-row" style="margin-bottom:10px;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group"><label>Section</label><select name="sec_id" required><?php $secs=$conn->query("SELECT * FROM sections ORDER BY name"); while($s=$secs->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['name']."</option>"; ?></select></div>
                    <div class="form-group"><label>Subject</label><select name="sec_sub" required><option value="">Select...</option><?php $subs=$conn->query("SELECT * FROM subjects ORDER BY code"); while($s=$subs->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['code']."</option>"; ?></select></div>
                    <div class="form-group" style="align-self:flex-end;"><button type="submit" name="add_sec_sub_btn" class="btn btn-sm btn-success">Add</button></div>
                </form>
                <table><tr><th>Section</th><th>Subject Code</th><th>Action</th></tr>
                <?php $ss=$conn->query("SELECT sec_sub.id, sec.name as sec_name, sub.code FROM section_subjects sec_sub JOIN sections sec ON sec_sub.section_id=sec.id JOIN subjects sub ON sec_sub.subject_id=sub.id ORDER BY sec.name, sub.code"); while($r=$ss->fetch_assoc()): ?>
                <tr><td><?php echo $r['sec_name']; ?></td><td><?php echo $r['code']; ?></td><td><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_sec_sub_id" value="<?php echo $r['id']; ?>"><button type="submit" name="del_sec_sub_btn" class="btn btn-danger btn-sm">Remove</button></form></td></tr>
                <?php endwhile; if($ss->num_rows==0) echo "<tr><td colspan='3' style='text-align:center'>None.</td></tr>"; ?></table>
            </div>
        </div>

        <?php elseif($tab == 'enrollment'): ?>
        <div class="card"><h3>Enrollment</h3>
            <?php $esy=$_GET['f_sy']??1; $esem=$_GET['f_sem']??1; ?>
            <form method="GET" class="form-row" style="align-items:flex-end;"><input type="hidden" name="action" value="dean"><input type="hidden" name="tab" value="enrollment">
                <div class="form-group"><label>Course</label><select name="f_course"><option value="">All</option><?php $crs=$conn->query("SELECT * FROM courses"); while($c=$crs->fetch_assoc()) echo "<option value='".$c['code']."' ".(($_GET['f_course']??'')==$c['code']?'selected':'').">".$c['code']."</option>"; ?></select></div>
                <div class="form-group"><label>Year</label><select name="f_yr"><option value="">All</option><?php for($i=1;$i<=4;$i++) echo "<option value='$i' ".(($_GET['f_yr']??'')==$i?'selected':'').">$i</option>"; ?></select></div>
                <div class="form-group"><label>SY</label><select name="f_sy"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($s=$sys->fetch_assoc()) echo "<option value='".$s['id']."' ".($esy==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div>
                <div class="form-group"><label>Sem</label><select name="f_sem"><?php $sms=$conn->query("SELECT * FROM semesters"); while($s=$sms->fetch_assoc()) echo "<option value='".$s['id']."' ".($esem==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div>
                <div class="form-group"><button type="submit" class="btn btn-sm">Filter</button></div>
            </form>
            <?php 
            $w="u.role_id=2 AND u.is_approved=1"; 
            if(!empty($_GET['f_course'])) $w.=" AND u.course='".$conn->real_escape_string($_GET['f_course'])."'"; 
            if(!empty($_GET['f_yr'])) $w.=" AND u.year_level=".intval($_GET['f_yr']); 
            $enr=$conn->query("SELECT u.id, u.first_name, u.last_name, u.course, u.year_level FROM users u WHERE $w ORDER BY u.last_name"); 
            ?>
            <table><tr><th>Name</th><th>Course</th><th>Year</th><th>Subjects</th><th>Action</th></tr><?php while($e=$enr->fetch_assoc()): 
                $ssubs=$conn->query("SELECT ss.id, s.code, s.name FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=".$e['id']." AND ss.sy_id=$esy AND ss.sem_id=$esem");
                $subj_list=array();
                while($sb=$ssubs->fetch_assoc()) $subj_list[]=$sb['code'];
            ?><tr><td><?php echo htmlspecialchars($e['first_name'].' '.$e['last_name']); ?></td><td><?php echo $e['course']??'N/A'; ?></td><td><?php echo $e['year_level']?:'N/A'; ?></td><td><?php echo implode(', ', $subj_list)?:'None'; ?></td><td><button type="button" onclick="document.getElementById('assign_<?php echo $e['id']; ?>').style.display='block'" class="btn btn-sm btn-success">Assign Subject</button></td></tr>
            <?php endwhile; if($enr->num_rows==0) echo "<tr><td colspan='5' style='text-align:center'>None.</td></tr>"; ?></table>
            
            <?php $enr->data_seek(0); while($e=$enr->fetch_assoc()): ?>
            <div id="assign_<?php echo $e['id']; ?>" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
                <div style="background:white;padding:20px;margin:100px auto;width:400px;max-width:90%;">
                    <h4>Assign Subject to <?php echo htmlspecialchars($e['first_name'].' '.$e['last_name']); ?></h4>
                    <form method="POST" class="form-row" style="margin-bottom:10px;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="assign_student" value="<?php echo $e['id']; ?>">
                        <input type="hidden" name="assign_sy" value="<?php echo $esy; ?>">
                        <input type="hidden" name="assign_sem" value="<?php echo $esem; ?>">
                        <div class="form-group" style="flex:1"><label>Subject</label><select name="assign_subject" required><option value="">Select...</option><?php $subs=$conn->query("SELECT * FROM subjects ORDER BY code"); while($s=$subs->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['code']." - ".$s['name']."</option>"; ?></select></div>
                        <div class="form-group"><button type="submit" name="assign_subject_btn" class="btn btn-sm btn-success">Add</button></div>
                    </form>
                    <h5>Current Subjects</h5>
                    <table style="font-size:12px;"><tr><th>Code</th><th>Name</th><th>Action</th></tr>
                    <?php $csubs=$conn->query("SELECT ss.id, s.code, s.name FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=".$e['id']." AND ss.sy_id=$esy AND ss.sem_id=$esem"); while($cs=$csubs->fetch_assoc()): ?>
                    <tr><td><?php echo $cs['code']; ?></td><td><?php echo $cs['name']; ?></td><td><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_ss_id" value="<?php echo $cs['id']; ?>"><button type="submit" name="remove_student_subject_btn" class="btn btn-danger btn-sm">Remove</button></form></td></tr>
                    <?php endwhile; if($csubs->num_rows==0) echo "<tr><td colspan='3' style='text-align:center'>None.</td></tr>"; ?></table>
                    <button type="button" onclick="document.getElementById('assign_<?php echo $e['id']; ?>').style.display='none'" class="btn btn-sm" style="background:#6c757d;margin-top:10px;">Close</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php elseif($tab == 'teachers'): ?>
        <div class="card"><h3>Teacher Schedules</h3>
            <?php $tsy=$_GET['f_sy']??1; $tsem=$_GET['f_sem']??1; $eta=isset($_GET['edit_ta'])?$conn->query("SELECT * FROM teacher_assignments WHERE id=".intval($_GET['edit_ta']))->fetch_assoc():null; ?>
            <form method="GET" class="form-row" style="margin-bottom:10px;"><input type="hidden" name="action" value="dean"><input type="hidden" name="tab" value="teachers"><div class="form-group"><label>SY</label><select name="f_sy" onchange="this.form.submit()"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($s=$sys->fetch_assoc()) echo "<option value='".$s['id']."' ".($tsy==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div><div class="form-group"><label>Sem</label><select name="f_sem" onchange="this.form.submit()"><?php $sms=$conn->query("SELECT * FROM semesters"); while($s=$sms->fetch_assoc()) echo "<option value='".$s['id']."' ".($tsem==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div></form>
            <form method="POST" class="form-row" style="align-items:flex-end;"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="ta_sy" value="<?php echo $tsy; ?>"><input type="hidden" name="ta_sem" value="<?php echo $tsem; ?>"><?php if($eta): ?><input type="hidden" name="edit_ta_id" value="<?php echo $eta['id']; ?>"><?php endif; ?>
                <div class="form-group"><label>Teacher</label><select name="ta_teacher" required><option value="">Select...</option><?php $tch=$conn->query("SELECT id, first_name, last_name FROM users WHERE role_id=5 AND is_approved=1 ORDER BY last_name"); while($t=$tch->fetch_assoc()) echo "<option value='".$t['id']."' ".(($eta['teacher_id']??'')==$t['id']?'selected':'').">".htmlspecialchars($t['last_name'].', '.$t['first_name'])."</option>"; ?></select></div>
                <div class="form-group"><label>Subject</label><select name="ta_subject" required><option value="">Select...</option><?php $tas=$conn->query("SELECT * FROM subjects ORDER BY code"); while($ts=$tas->fetch_assoc()) echo "<option value='".$ts['id']."' ".(($eta['subject_id']??'')==$ts['id']?'selected':'').">".$ts['code']."</option>"; ?></select></div>
                <div class="form-group"><label>Schedule</label><input type="text" name="ta_schedule" value="<?php echo $eta['schedule']??''; ?>" required></div>
                <div class="form-group"><label>Room</label><input type="text" name="ta_room" value="<?php echo $eta['room']??''; ?>" required></div>
                <div class="form-group"><?php if($eta): ?><button type="submit" name="edit_ta_btn" class="btn btn-sm">Update</button><a href="?action=dean&tab=teachers&f_sy=<?php echo $tsy; ?>&f_sem=<?php echo $tsem; ?>" class="btn btn-sm" style="background:#6c757d">X</a><?php else: ?><button type="submit" name="add_ta_btn" class="btn btn-sm btn-success">Assign</button><?php endif; ?></div>
            </form>
            <table><tr><th>Teacher</th><th>Subject</th><th>Schedule</th><th>Room</th><th>Action</th></tr><?php $tal=$conn->query("SELECT ta.*, CONCAT(u.last_name,', ',u.first_name) as tn, s.code FROM teacher_assignments ta JOIN users u ON ta.teacher_id=u.id JOIN subjects s ON ta.subject_id=s.id WHERE ta.sy_id=$tsy AND ta.sem_id=$tsem"); while($ta=$tal->fetch_assoc()): ?><tr><td><?php echo htmlspecialchars($ta['tn']); ?></td><td><?php echo $ta['code']; ?></td><td><?php echo htmlspecialchars($ta['schedule']); ?></td><td><?php echo htmlspecialchars($ta['room']); ?></td><td><a href="?action=dean&tab=teachers&edit_ta=<?php echo $ta['id']; ?>&f_sy=<?php echo $tsy; ?>&f_sem=<?php echo $tsem; ?>" class="btn btn-sm">Edit</a><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_ta_id" value="<?php echo $ta['id']; ?>"><button type="submit" name="del_ta_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr><?php endwhile; if($tal->num_rows==0) echo "<tr><td colspan='5' style='text-align:center'>None.</td></tr>"; ?></table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
