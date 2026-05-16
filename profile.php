<?php
require_once __DIR__ . '/../includes/auth.php';

$uid=$_SESSION['user_id']; $pd=$conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc(); $psrc=$pd['profile_pic']=='default.png'?'https://via.placeholder.com/120':'uploads/'.$pd['profile_pic'];

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
    <title>School Management System - Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h2>School Management System</h2>
        <div class="header-links">
            <a href="index.php">Dashboard</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>
    <div class="dashboard-container">
        <div class="auth-wrapper" style="min-height:auto;padding:0;"><div class="form-box profile-box">
            <h2>Profile Settings</h2><?php echo $profile_msg; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="profile-pic-container"><img src="<?php echo $psrc; ?>?t=<?php echo time(); ?>" id="picPreview" class="profile-pic-preview"><br><input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewPic(this)"><small>Leave empty to keep current</small></div>
                <fieldset><legend>Account</legend><div class="form-row"><div class="form-group"><label>Username</label><input type="text" value="<?php echo $pd['username']; ?>" disabled></div><div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo $pd['email']; ?>" required></div></div></fieldset>
                <fieldset><legend>Personal</legend>
                    <div class="form-row"><div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?php echo $pd['first_name']; ?>" required></div><div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" value="<?php echo $pd['middle_name']; ?>"></div><div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $pd['last_name']; ?>" required></div><div class="form-group" style="flex:0.3;"><label>Suffix</label><select name="suffix"><option value="">None</option><option value="Jr" <?php if($pd['suffix']=='Jr') echo 'selected'; ?>>Jr</option><option value="Sr" <?php if($pd['suffix']=='Sr') echo 'selected'; ?>>Sr</option></select></div></div>
                    <div class="form-row"><div class="form-group"><label>Birthdate</label><input type="date" name="birthdate" value="<?php echo $pd['birthdate']; ?>" required></div><div class="form-group"><label>Gender</label><select name="gender" required><option value="Male" <?php if($pd['gender']=='Male') echo 'selected'; ?>>Male</option><option value="Female" <?php if($pd['gender']=='Female') echo 'selected'; ?>>Female</option></select></div><div class="form-group"><label>Contact</label><input type="text" name="contact_number" value="<?php echo $pd['contact_number']; ?>" required></div></div>
                    <div class="form-group"><label>Address</label><textarea name="address" rows="2" required><?php echo $pd['address']; ?></textarea></div>
                </fieldset>
                <fieldset><legend>Assignment</legend>
                    <div class="form-group"><label>Role</label><input type="text" value="<?php echo ucfirst($_SESSION['role']); ?>" disabled style="background:#f2f2f2;"></div>
                    <div class="form-group"><label>Department</label><select name="department" id="department" onchange="filterCourses(this.value)"><option value="">Select...</option><?php $dp=$conn->query("SELECT * FROM departments ORDER BY name"); while($d=$dp->fetch_assoc()) echo "<option value='".$d['name']."' ".($pd['department']==$d['name']?'selected':'').">".$d['name']."</option>"; ?></select></div>
                    <div class="form-group"><label>Course</label><select name="course" id="course"><option value="">Select...</option><?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()) echo "<option value='".$c['code']."' ".($pd['course']==$c['code']?'selected':'').">".$c['code']." - ".$c['name']."</option>"; ?></select></div>
                    <div id="year_level_group" class="form-group"><label>Year Level</label><select name="year_level" id="year_level"><option value="">Select...</option><option value="1" <?php if($pd['year_level']==1) echo 'selected'; ?>>1st</option><option value="2" <?php if($pd['year_level']==2) echo 'selected'; ?>>2nd</option><option value="3" <?php if($pd['year_level']==3) echo 'selected'; ?>>3rd</option><option value="4" <?php if($pd['year_level']==4) echo 'selected'; ?>>4th</option></select></div>
                </fieldset>
                <button type="submit" name="update_profile_btn" class="btn btn-success">Update Profile</button>
                <a href="index.php" class="btn" style="background:#6c757d;text-align:center;margin-top:10px;">Cancel</a>
            </form>
        </div></div>
    </div>
    <script>
    const allCourses = <?php echo json_encode($all_courses); ?>;
    function filterCourses(dept) {
        let sel = document.getElementById("course"); if(!sel) return;
        sel.innerHTML = '<option value="">Select...</option>';
        allCourses.forEach(c => { if(c.department == dept) sel.innerHTML += `<option value="${c.code}">${c.code} - ${c.name}</option>`; });
    }
    function previewPic(input) { if(input.files && input.files[0]) { var r = new FileReader(); r.onload = e => { document.getElementById('picPreview').src = e.target.result; }; r.readAsDataURL(input.files[0]); } }
    window.onload = function() { filterCourses(document.getElementById('department')?.value || ''); };
    </script>
</body>
</html>
