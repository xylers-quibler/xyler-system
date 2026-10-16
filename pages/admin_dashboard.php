<?php
require_once __DIR__ . '/../includes/auth.php';

$admin_msg = "";

// Ensure sys_settings is available
if (!isset($sys_settings) || $sys_settings === null) {
    $sys_settings = ['fee_per_unit' => 500.00, 'misc_fee' => 50.00];
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    if (isset($_POST['approve_user_btn'])) { $id=intval($_POST['target_user_id']); $conn->query("UPDATE users SET is_approved = 1 WHERE id = $id"); $admin_msg = "<div class='success'>Approved.</div>"; logAction($conn, $_SESSION['user_id'], "Approved ID $id"); }
    if (isset($_POST['decline_user_btn'])) { $id=intval($_POST['target_user_id']); $conn->query("UPDATE users SET is_approved = 2 WHERE id = $id"); $admin_msg = "<div class='error'>Declined.</div>"; }
    if (isset($_POST['suspend_user_btn'])) { $id=intval($_POST['mng_user_id']); $conn->query("UPDATE users SET is_approved = 3 WHERE id = $id"); $admin_msg = "<div class='error'>Suspended.</div>"; logAction($conn, $_SESSION['user_id'], "Suspended ID $id"); }
    if (isset($_POST['unsuspend_user_btn'])) { $id=intval($_POST['mng_user_id']); $conn->query("UPDATE users SET is_approved = 1 WHERE id = $id"); $admin_msg = "<div class='success'>Unsuspended.</div>"; }
    if (isset($_POST['reset_pass_btn'])) { $id=intval($_POST['mng_user_id']); $np=password_hash("password123", PASSWORD_DEFAULT); $conn->query("UPDATE users SET password = '$np' WHERE id = $id"); $admin_msg = "<div class='success'>Pass reset.</div>"; logAction($conn, $_SESSION['user_id'], "Reset pass ID $id"); }
    if (isset($_POST['save_settings_btn'])) { $conn->query("UPDATE system_settings SET fee_per_unit = ".floatval($_POST['fee_per_unit']).", misc_fee = ".floatval($_POST['misc_fee'])." WHERE id=1"); $admin_msg = "<div class='success'>Settings saved.</div>"; logAction($conn, $_SESSION['user_id'], "Updated fees"); $sys_settings = $conn->query("SELECT * FROM system_settings LIMIT 1")->fetch_assoc(); }
    if (isset($_POST['add_dept_btn'])) { $n=$conn->real_escape_string($_POST['dept_name']); $conn->query("INSERT INTO departments (name) VALUES ('$n')"); $admin_msg = "<div class='success'>Dept added.</div>"; }
    if (isset($_POST['del_dept_btn'])) { $conn->query("DELETE FROM departments WHERE id=".intval($_POST['del_dept_id'])); }
    if (isset($_POST['add_crs_btn'])) { $c=$conn->real_escape_string($_POST['crs_code']); $n=$conn->real_escape_string($_POST['crs_name']); $d=$conn->real_escape_string($_POST['crs_dept']); $m=intval($_POST['crs_max']); $conn->query("INSERT INTO courses (code, name, department, max_units) VALUES ('$c','$n','$d',$m)"); $admin_msg = "<div class='success'>Course added.</div>"; $all_courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC); }
    if (isset($_POST['del_crs_btn'])) { $conn->query("DELETE FROM courses WHERE id=".intval($_POST['del_crs_id'])); $all_courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC); }
    if (isset($_POST['update_assignment_btn'])) {
        $uid = intval($_POST['assign_user_id']);
        $dept = $conn->real_escape_string($_POST['assign_department']);
        $course = $conn->real_escape_string($_POST['assign_course']);
        $year = intval($_POST['assign_year_level']);
        $conn->query("UPDATE users SET department='$dept', course='$course', year_level=$year WHERE id=$uid");
        $admin_msg = "<div class='success'>Assignment updated.</div>";
        logAction($conn, $_SESSION['user_id'], "Updated assignment for ID $uid");
    }
}

$name = $_SESSION['name']; $role = $_SESSION['role'];
$tab = $_GET['tab'] ?? 'users';
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
        <?php echo $admin_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Administrator</span></h2>
        <div class="admin-tabs">
            <a href="?tab=users" class="btn btn-sm <?php echo $tab=='users'?'active':''; ?>">👥 Active Users</a>
            <a href="?tab=pending" class="btn btn-sm <?php echo $tab=='pending'?'active':''; ?>">⏳ Pending</a>
            <a href="?tab=settings" class="btn btn-sm <?php echo $tab=='settings'?'active':''; ?>">⚙️ Settings</a>
            <a href="?tab=base_data" class="btn btn-sm <?php echo $tab=='base_data'?'active':''; ?>">🏫 Base Data</a>
            <a href="?tab=logs" class="btn btn-sm <?php echo $tab=='logs'?'active':''; ?>">📜 Logs</a>
        </div>

        <?php if($tab == 'users'): ?>
        <div class="card"><h3>User Management</h3>
            <table><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Action</th></tr>
            <?php $users = $conn->query("SELECT u.id, u.first_name, u.last_name, u.username, r.role_name, u.is_approved FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id != ".$_SESSION['user_id']." ORDER BY u.is_approved, u.last_name");
            while($u = $users->fetch_assoc()): $st = $u['is_approved'] == 1 ? '<span style="color:green">Active</span>' : ($u['is_approved'] == 3 ? '<span style="color:red">Suspended</span>' : '<span style="color:orange">Pending</span>'); ?>
            <tr><td><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></td><td><?php echo $u['username']; ?></td><td><?php echo ucfirst($u['role_name']); ?></td><td><?php echo $st; ?></td>
                <td><?php if($u['is_approved']==1): ?><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="mng_user_id" value="<?php echo $u['id']; ?>"><button type="submit" name="suspend_user_btn" class="btn btn-danger btn-sm">Suspend</button></form>
                    <?php elseif($u['is_approved']==3): ?><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="mng_user_id" value="<?php echo $u['id']; ?>"><button type="submit" name="unsuspend_user_btn" class="btn btn-success btn-sm">Unsuspend</button></form><?php endif; ?>
                    <form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="mng_user_id" value="<?php echo $u['id']; ?>"><button type="submit" name="reset_pass_btn" class="btn btn-sm">Reset Pass</button></form></td></tr>
            <?php endwhile; ?></table>
        </div>

        <?php elseif($tab == 'pending'): ?>
        <div class="card"><h3>Pending Accounts</h3>
            <?php $pu = $conn->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.is_approved = 0 ORDER BY u.created_at DESC");
            if($pu->num_rows > 0): ?><div class="approval-grid">
                <?php while($p = $pu->fetch_assoc()): $ps = $p['profile_pic'] == 'default.png' ? 'https://via.placeholder.com/80' : 'uploads/'.$p['profile_pic']; ?>
                <div class="user-card"><img src="<?php echo $ps; ?>" class="user-card-pic"><h4><?php echo htmlspecialchars($p['first_name'].' '.$p['last_name']); ?></h4><p><strong>Role:</strong> <?php echo ucfirst($p['role_name']); ?></p><p><strong>Course:</strong> <?php echo $p['course'] ?? 'N/A'; ?></p><div style="clear:both;"></div><div class="user-card-actions"><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="target_user_id" value="<?php echo $p['id']; ?>"><button type="submit" name="approve_user_btn" class="btn btn-success btn-sm">✅ Approve</button></form><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="target_user_id" value="<?php echo $p['id']; ?>"><button type="submit" name="decline_user_btn" class="btn btn-danger btn-sm">❌ Decline</button></form></div></div>
                <?php endwhile; ?></div><?php else: echo "<p style='text-align:center;color:#666;'>None.</p>"; endif; ?>
        </div>

        <?php elseif($tab == 'settings'): ?>
        <div class="card"><h3>System Settings</h3><div class="alert-box">Changes apply to all new student enrollments instantly.</div>
            <form method="POST" class="form-row" style="max-width:500px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group"><label>Fee Per Unit (₱)</label><input type="number" step="0.01" name="fee_per_unit" value="<?php echo $sys_settings['fee_per_unit']; ?>" required></div>
                <div class="form-group"><label>Misc Fee (₱)</label><input type="number" step="0.01" name="misc_fee" value="<?php echo $sys_settings['misc_fee']; ?>" required></div>
                <div class="form-group" style="align-self:flex-end;"><button type="submit" name="save_settings_btn" class="btn btn-success">Save</button></div>
            </form>
        </div>

        <?php elseif($tab == 'base_data'): ?>
        <div class="grid-2">
            <div class="card"><h3>Departments</h3><form method="POST" class="form-row" style="margin-bottom:10px;"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><div class="form-group"><input type="text" name="dept_name" placeholder="New Dept" required></div><div class="form-group" style="flex:0.2;"><button type="submit" name="add_dept_btn" class="btn btn-sm btn-success">Add</button></div></form><table><tr><th>Name</th><th>Action</th></tr><?php $dps=$conn->query("SELECT * FROM departments ORDER BY name"); while($d=$dps->fetch_assoc()): ?><tr><td><?php echo $d['name']; ?></td><td><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_dept_id" value="<?php echo $d['id']; ?>"><button type="submit" name="del_dept_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr><?php endwhile; ?></table></div>
            <div class="card"><h3>Courses</h3><form method="POST" class="form-row" style="margin-bottom:10px;flex-wrap:wrap;"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><div class="form-group" style="flex:0.3;"><input type="text" name="crs_code" placeholder="Code" required></div><div class="form-group"><input type="text" name="crs_name" placeholder="Name" required></div><div class="form-group"><select name="crs_dept" required><option value="">Dept...</option><?php $dp=$conn->query("SELECT * FROM departments"); while($d=$dp->fetch_assoc()) echo "<option value='".$d['name']."'>".$d['name']."</option>"; ?></select></div><div class="form-group" style="flex:0.2;"><input type="number" name="crs_max" value="24" required></div><div class="form-group" style="flex:0.2;"><button type="submit" name="add_crs_btn" class="btn btn-sm btn-success">Add</button></div></form><table><tr><th>Code</th><th>Max Units</th><th>Action</th></tr><?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()): ?><tr><td><?php echo $c['code']; ?></td><td><?php echo $c['max_units']; ?></td><td><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"><input type="hidden" name="del_crs_id" value="<?php echo $c['id']; ?>"><button type="submit" name="del_crs_btn" class="btn btn-danger btn-sm">Del</button></form></td></tr><?php endwhile; ?></table></div>
        </div>

        <?php elseif($tab == 'logs'): ?>
        <div class="card"><h3>Activity Logs</h3><table><tr><th>Time</th><th>User</th><th>Action</th></tr><?php $logs=$conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100"); while($l=$logs->fetch_assoc()): ?><tr><td style="font-size:12px;"><?php echo $l['created_at']; ?></td><td><?php echo $l['user_id']?:'Sys'; ?></td><td><?php echo htmlspecialchars($l['action_text']); ?></td></tr><?php endwhile; if($logs->num_rows==0) echo "<tr><td colspan='3' style='text-align:center'>None.</td></tr>"; ?></table></div>
        <?php endif; ?>
    </div>
</body>
</html>
