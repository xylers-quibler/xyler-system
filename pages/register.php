<?php
require_once __DIR__ . '/../includes/auth.php';

// Ensure all_courses is available
if (!isset($all_courses) || $all_courses === null) {
    $all_courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper"><div class="form-box register-box">
        <h2>Registration E-Form</h2><?php echo $reg_msg; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="profile-pic-container"><img src="https://via.placeholder.com/120" id="picPreview" class="profile-pic-preview"><br><input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewPic(this)"></div>
            <fieldset><legend>Account</legend><div class="form-row"><div class="form-group"><label>Username</label><input type="text" name="username" required></div><div class="form-group"><label>Email</label><input type="email" name="email" required></div></div><div class="form-row"><div class="form-group"><label>Password</label><input type="password" name="password" required></div><div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" required></div></div></fieldset>
            <fieldset><legend>Personal</legend><div class="form-row"><div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div><div class="form-group"><label>Middle</label><input type="text" name="middle_name"></div><div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div><div class="form-group" style="flex:0.3;"><label>Suffix</label><select name="suffix"><option value="">None</option><option value="Jr">Jr</option><option value="Sr">Sr</option></select></div></div><div class="form-row"><div class="form-group"><label>Birthdate</label><input type="date" name="birthdate" required></div><div class="form-group"><label>Gender</label><select name="gender" required><option value="">Select...</option><option value="Male">Male</option><option value="Female">Female</option></select></div><div class="form-group"><label>Contact</label><input type="text" name="contact_number" required></div></div><div class="form-group"><label>Address</label><textarea name="address" rows="2" required></textarea></div></fieldset>
            <fieldset><legend>Assignment</legend><div class="form-group"><label>Registering As</label><select name="role_id" id="role_id" required onchange="toggleFields()"><option value="2" selected>Student</option><option value="3">Dean</option><option value="4">Finance</option><option value="5">Teacher</option><option value="6">Cashier</option><option value="7">Records</option></select></div>
                <div id="department_group" class="form-group"><label>Department</label><select name="department" id="department" onchange="filterCourses(this.value)"><option value="">Select...</option><?php $dp=$conn->query("SELECT * FROM departments ORDER BY name"); while($d=$dp->fetch_assoc()) echo "<option value='".$d['name']."'>".$d['name']."</option>"; ?></select></div>
                <div id="course_group" class="form-group"><label>Course</label><select name="course" id="course"><option value="">Select...</option><?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()) echo "<option value='".$c['code']."'>".$c['code']." - ".$c['name']."</option>"; ?></select></div>
                <div id="year_level_group" class="form-group"><label>Year Level</label><select name="year_level" id="year_level"><option value="">Select...</option><option value="1">1st</option><option value="2">2nd</option><option value="3">3rd</option><option value="4">4th</option></select></div>
            </fieldset>
            <button type="submit" name="register_btn" class="btn">Submit E-Form</button>
        </form>
        <div class="switch-link">Already have an account? <a href="index.php">Login Here</a></div>
    </div></div>
    <script>
    const allCourses = <?php echo json_encode($all_courses); ?>;
    function filterCourses(dept) {
        let sel = document.getElementById("course"); if(!sel) return;
        sel.innerHTML = '<option value="">Select...</option>';
        allCourses.forEach(c => { if(c.department == dept) sel.innerHTML += `<option value="${c.code}">${c.code} - ${c.name}</option>`; });
    }
    function previewPic(input) { if(input.files && input.files[0]) { var r = new FileReader(); r.onload = e => { document.getElementById('picPreview').src = e.target.result; }; r.readAsDataURL(input.files[0]); } }
    function toggleFields() {
        let r = document.getElementById("role_id"); if(!r) return; let role = r.value;
        let dg=document.getElementById("department_group"), cg=document.getElementById("course_group"), yg=document.getElementById("year_level_group");
        dg.style.display="none"; cg.style.display="none"; yg.style.display="none";
        if(role=="2"){dg.style.display="block";cg.style.display="block";yg.style.display="block";}
        else if(role=="3"||role=="5"){dg.style.display="block";}
    }
    window.onload = function() { toggleFields(); filterCourses(document.getElementById('department')?.value || ''); };
    </script>
</body>
</html>
