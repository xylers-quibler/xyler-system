<?php
require_once __DIR__ . '/../includes/auth.php';

// Load system settings from database
$sys_settings_query = $conn->query("SELECT * FROM system_settings WHERE id=1");
if ($sys_settings_query && $sys_settings_query->num_rows > 0) {
    $sys_settings = $sys_settings_query->fetch_assoc();
} else {
    $sys_settings = ['fee_per_unit' => 500.00, 'misc_fee' => 50.00];
}

$cashier_msg = "";
$c_view_stud = null; $c_stud_acc = null; $c_hist = null; $c_results = null; $c_report = null;

if (isset($_SESSION['role']) && $_SESSION['role'] == 'cashier') {
    if (isset($_POST['c_search_btn'])) {
        $term = $conn->real_escape_string($_POST['search_term']);
        $c_results = $conn->query("SELECT * FROM users WHERE role_id=2 AND is_approved=1 AND (first_name LIKE '%$term%' OR last_name LIKE '%$term%' OR username LIKE '%$term%')");
    }
    if (isset($_POST['update_settings_btn'])) {
        $fpu=floatval($_POST['fee_per_unit']);
        $mf=floatval($_POST['misc_fee']);
        $conn->query("UPDATE system_settings SET fee_per_unit=$fpu, misc_fee=$mf WHERE id=1");
        $cashier_msg="<div class='success'>Tuition settings updated. New rates will apply to new enrollments and account recalculations.</div>";
    }
    if (isset($_POST['adjust_balance_btn'])) {
        $aid=intval($_POST['adj_acc_id']);
        $new_bal=floatval($_POST['new_balance']);
        $acc=$conn->query("SELECT * FROM student_accounts WHERE id=$aid")->fetch_assoc();
        $new_total=floatval($acc['total_fees'])+(floatval($new_bal)-floatval($acc['balance']));
        $permit=$new_bal<=0?'Approved':'Pending';
        $conn->query("UPDATE student_accounts SET balance=$new_bal, total_fees=$new_total, permit_status='$permit' WHERE id=$aid");
        $cashier_msg="<div class='success'>Balance adjusted.</div>";
    }
    if (isset($_GET['view_stud'])) {
        $sid = intval($_GET['view_stud']); $syid = intval($_GET['sy_id']); $semid = intval($_GET['sem_id']);
        $c_view_stud = $conn->query("SELECT * FROM users WHERE id=$sid")->fetch_assoc();
        $acc_check = $conn->query("SELECT * FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid");
        if($acc_check->num_rows > 0) $c_stud_acc = $acc_check->fetch_assoc();
        else {
            // Get student's course to use course-specific tuition
            $student_course = $conn->query("SELECT course FROM users WHERE id=$sid")->fetch_assoc();
            $course_tuition = $conn->query("SELECT fee_per_unit, misc_fee FROM courses WHERE code='".$conn->real_escape_string($student_course['course'])."'")->fetch_assoc();
            
            // Use course-specific tuition or fall back to system settings
            $fee_per_unit = $course_tuition['fee_per_unit'] ?? $sys_settings['fee_per_unit'];
            $misc_fee = $course_tuition['misc_fee'] ?? $sys_settings['misc_fee'];
            
            $subs=$conn->query("SELECT s.units FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=$sid AND ss.sy_id=$syid AND ss.sem_id=$semid AND ss.status='Enrolled'"); $tu=0;
            while($s=$subs->fetch_assoc()) $tu+=$s['units'];
            $tot=($tu*$fee_per_unit)+$misc_fee;
            $conn->query("INSERT INTO student_accounts (student_id, sy_id, sem_id, total_fees, balance) VALUES ($sid, $syid, $semid, $tot, $tot)");
            $c_stud_acc = $conn->query("SELECT * FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid")->fetch_assoc();
        }
        $c_hist = $conn->query("SELECT * FROM payments WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid ORDER BY date_paid DESC");
    }
    if (isset($_POST['c_pay_btn'])) {
        $sid=intval($_POST['pay_sid']); $syid=intval($_POST['pay_sy']); $semid=intval($_POST['pay_sem']); $amt=floatval($_POST['pay_amt']);
        $or="OR-".date('YmdHis')."-".$sid;
        $conn->query("INSERT INTO payments (student_id, sy_id, sem_id, amount, or_number, cashier_id) VALUES ($sid, $syid, $semid, $amt, '$or', ".$_SESSION['user_id'].")");
        $acc=$conn->query("SELECT id, amount_paid, total_fees FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid")->fetch_assoc();
        $new_paid=floatval($acc['amount_paid'])+$amt;
        $raw_due=floatval($acc['total_fees'])-$new_paid;
        $new_bal=max(0,$raw_due);
        $permit=$raw_due<=0?'Approved':'Pending';
        $conn->query("UPDATE student_accounts SET amount_paid=$new_paid, balance=$new_bal, permit_status='$permit' WHERE id=".$acc['id']);
        $cashier_msg="<div class='success'>Payment recorded. OR: $or</div>";
    }
    if (isset($_GET['print_receipt'])) {
        $or = $conn->real_escape_string($_GET['print_receipt']);
        $q = $conn->query("SELECT p.*, u.first_name, u.last_name, sy.name as sy_name, sem.name as sem_name FROM payments p JOIN users u ON p.student_id=u.id JOIN school_years sy ON p.sy_id=sy.id JOIN semesters sem ON p.sem_id=sem.id WHERE p.or_number='$or'");
        if($q && $q->num_rows > 0) {
            $pay = $q->fetch_assoc();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @media print {
            .no-print { display: none; }
            #receipt-area { border: none; }
        }
    </style>
</head>
<body>
    <div id="receipt-area" style="max-width:400px; margin:20px auto; padding:20px; border:1px solid #ccc;">
        <h3 style="text-align:center;">OFFICIAL RECEIPT</h3>
        <p style="text-align:center;"><strong><?php echo $pay['sy_name']; ?> - <?php echo $pay['sem_name']; ?></strong></p>
        <hr>
        <p style="margin:10px 0;"><strong>Student:</strong> <?php echo htmlspecialchars($pay['first_name'].' '.$pay['last_name']); ?></p>
        <p style="margin-bottom:20px;"><strong>Date:</strong> <?php echo date("F j, Y g:i a", strtotime($pay['date_paid'])); ?></p>
        <div style="border-top:1px solid #000; margin-top:10px; display:flex; justify-content:space-between;"><span>Description</span><span>Amount</span></div>
        <div style="display:flex; justify-content:space-between;"><span>Tuition & Fees Payment</span><span>₱ <?php echo number_format($pay['amount'],2); ?></span></div>
        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:18px; margin-top:10px;"><span>TOTAL PAID</span><span>₱ <?php echo number_format($pay['amount'],2); ?></span></div>
        <p style="margin-top:20px; font-size:12px;">OR Number: <?php echo $pay['or_number']; ?></p>
        <p style="font-size:10px;">This is a system generated receipt. Signature not required for digital validation.</p>
    </div>
    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()" class="btn btn-success">Print Receipt</button>
        <a href="?action=cashier" class="btn">Close</a>
    </div>
</body>
</html>
    <?php
            exit();
        }
    }
    if (isset($_POST['c_report_btn'])) {
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $c_report = $conn->query("SELECT p.*, u.first_name, u.last_name, sy.name as sy_name, sem.name as sem_name 
            FROM payments p 
            JOIN users u ON p.student_id = u.id 
            JOIN school_years sy ON p.sy_id = sy.id 
            JOIN semesters sem ON p.sem_id = sem.id 
            WHERE DATE(p.date_paid) BETWEEN '$from_date' AND '$to_date' 
            ORDER BY p.date_paid DESC");
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
        <?php echo $cashier_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Cashier</span></h2>

        <?php if(!isset($_GET['view_stud'])): ?>
        <div class="card">
            <h3>Search Student</h3>
            <form method="POST" class="form-row" style="max-width:500px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group" style="flex:2;"><input type="text" name="search_term" placeholder="Search by name or username..." required></div>
                <div class="form-group"><button type="submit" name="c_search_btn" class="btn">Search</button></div>
            </form>
        </div>

        <div class="card">
            <h3>Tuition Settings by Course</h3>
            <table>
                <tr><th>Course</th><th>Fee/Unit (₱)</th><th>Misc Fee (₱)</th><th>Action</th></tr>
                <?php $crs=$conn->query("SELECT * FROM courses ORDER BY code"); while($c=$crs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $c['code'] . ' - ' . $c['name']; ?></td>
                    <td>₱<?php echo number_format($c['fee_per_unit'] ?? 500.00, 2); ?></td>
                    <td>₱<?php echo number_format($c['misc_fee'] ?? 50.00, 2); ?></td>
                    <td><small>Managed by Dean</small></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div style="margin-top:10px; padding:10px; background:#fff3cd; border-left:4px solid #ffc107;">
                <small><strong>Note:</strong> Tuition settings are managed by the Dean in the Dean Dashboard. Contact the Dean to change tuition rates for specific courses.</small>
            </div>
        </div>

        <div class="card">
            <h3>Cash Collection Report</h3>
            <form method="POST" class="form-row" style="max-width:500px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group"><label>From Date</label><input type="date" name="from_date" required></div>
                <div class="form-group"><label>To Date</label><input type="date" name="to_date" required></div>
                <div class="form-group"><button type="submit" name="c_report_btn" class="btn">Generate Report</button></div>
            </form>
        </div>

        <?php if($c_report && $c_report->num_rows > 0): ?>
        <div class="card">
            <h3>Cash Collection Report</h3>
            <table>
                <tr><th>OR #</th><th>Date</th><th>Student</th><th>Amount</th><th>SY</th><th>Sem</th></tr>
                <?php $total=0; while($r=$c_report->fetch_assoc()): $total+=$r['amount']; ?>
                <tr>
                    <td><?php echo $r['or_number']; ?></td>
                    <td><?php echo date("M d, Y", strtotime($r['date_paid'])); ?></td>
                    <td><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
                    <td>₱<?php echo number_format($r['amount'],2); ?></td>
                    <td><?php echo $r['sy_name']; ?></td>
                    <td><?php echo $r['sem_name']; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="background:#f0f0f0; font-weight:bold;"><td colspan="3" style="text-align:right;">Total:</td><td colspan="3">₱<?php echo number_format($total,2); ?></td></tr>
            </table>
        </div>
        <?php endif; ?>

        <?php if($c_results && $c_results->num_rows > 0): ?>
        <div class="card">
            <h3>Search Results</h3>
            <table>
                <tr><th>Name</th><th>Username</th><th>Course</th><th>Action</th></tr>
                <?php while($r=$c_results->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                    <td><?php echo htmlspecialchars($r['course']); ?></td>
                    <td><a href="?action=cashier&view_stud=<?php echo $r['id']; ?>&sy_id=1&sem_id=1" class="btn btn-sm btn-success">View Billing</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <button onclick="window.location.href='?action=cashier'" class="btn btn-sm" style="margin-bottom:15px;">&larr; Back to Search</button>

        <?php if($c_view_stud): ?>
        <div class="card">
            <div class="form-row" style="justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="border:none; margin:0;">Student Billing</h3>
                    <p style="color:#666;">
                        <strong>Name:</strong> <?php echo htmlspecialchars($c_view_stud['first_name'].' '.$c_view_stud['last_name']); ?><br>
                        <strong>Course:</strong> <?php echo $c_view_stud['course']; ?> | <strong>Year:</strong> <?php echo $c_view_stud['year_level']; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if($c_stud_acc): ?>
        <div class="grid-2">
            <div>
                <div class="card">
                    <h4>Payment History</h4>
                    <?php if($c_hist && $c_hist->num_rows > 0): ?>
                    <table style="font-size:12px;">
                        <tr><th>OR #</th><th>Date</th><th>Amount</th><th>Action</th></tr>
                        <?php while($h=$c_hist->fetch_assoc()): ?>
                        <tr><td><?php echo $h['or_number']; ?></td><td><?php echo date("M d, Y", strtotime($h['date_paid'])); ?></td><td>₱<?php echo number_format($h['amount'],2); ?></td><td><a href="?action=cashier&print_receipt=<?php echo $h['or_number']; ?>" target="_blank" class="btn btn-sm btn-success">Print</a></td></tr>
                        <?php endwhile; ?>
                    </table>
                    <?php else: echo "<small>No payments yet.</small>"; endif; ?>
                </div>
            </div>

            <div>
                <div class="card permit-box <?php echo strtolower($c_stud_acc['permit_status']); ?>">
                    <h2 style="border:none;"><?php echo $c_stud_acc['permit_status']; ?></h2>
                    <p>Status as of today</p>
                </div>

                <div class="card">
                    <h4>Current Balance</h4>
                    <div class="big-total">₱<?php echo number_format($c_stud_acc['balance'],2); ?></div>
                    <div class="finance-summary" style="margin-bottom:20px;">
                        <table>
                            <?php 
                            $base_total = floatval($c_stud_acc['total_fees']);
                            $discount_amt = 0;
                            if($c_stud_acc['discount_type'] == 'fixed') {
                                $discount_amt = floatval($c_stud_acc['discount_value']);
                            } elseif($c_stud_acc['discount_type'] == 'percent') {
                                $discount_amt = $base_total * (floatval($c_stud_acc['discount_value']) / 100);
                            }
                            $original_total = $base_total + $discount_amt;
                            ?>
                            <?php if($c_stud_acc['discount_type']!='none'): ?>
                            <tr><td>Original Fees</td><td style="text-align:right;">₱<?php echo number_format($original_total,2); ?></td></tr>
                            <tr style="color:blue;"><td>Discount (<?php echo ucfirst($c_stud_acc['discount_type']).' '.$c_stud_acc['discount_value']; ?>)</td><td style="text-align:right;">-₱<?php echo number_format($discount_amt,2); ?></td></tr>
                            <?php endif; ?>
                            <tr><td>Total Fees</td><td style="text-align:right;">₱<?php echo number_format($c_stud_acc['total_fees'],2); ?></td></tr>
                            <tr><td>Amount Paid</td><td style="text-align:right;color:green;">(₱<?php echo number_format($c_stud_acc['amount_paid'],2); ?>)</td></tr>
                        </table>
                    </div>

                    <div class="card" style="margin-bottom:20px;">
                        <h4>Adjust Balance</h4>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="adj_acc_id" value="<?php echo $c_stud_acc['id']; ?>">
                            <div class="form-group">
                                <label>New Balance (₱)</label>
                                <input type="number" step="0.01" name="new_balance" value="<?php echo $c_stud_acc['balance']; ?>" required>
                            </div>
                            <button type="submit" name="adjust_balance_btn" class="btn btn-warning" style="width:100%;">Adjust Balance</button>
                        </form>
                    </div>

                    <?php if($c_stud_acc['balance'] > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="pay_sid" value="<?php echo $c_view_stud['id']; ?>">
                        <input type="hidden" name="pay_sy" value="<?php echo $_GET['sy_id']; ?>">
                        <input type="hidden" name="pay_sem" value="<?php echo $_GET['sem_id']; ?>">
                        <div class="form-group">
                            <label>Payment Amount (₱)</label>
                            <input type="number" step="0.01" name="pay_amt" required>
                        </div>
                        <button type="submit" name="c_pay_btn" class="btn btn-success" style="width:100%;">Process Payment & Print Receipt</button>
                    </form>
                    <?php else: ?>
                    <button class="btn" style="width:100%; background:#ccc;" disabled>Fully Paid</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
