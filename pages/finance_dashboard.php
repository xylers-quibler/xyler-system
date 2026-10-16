<?php
require_once __DIR__ . '/../includes/auth.php';

$finance_msg = "";
$f_view_stud = null; $f_stud_acc = null; $f_stud_subs = null; $f_hist = null; $f_results = null;

// Ensure sys_settings is available
if (!isset($sys_settings) || $sys_settings === null) {
    $sys_settings = ['fee_per_unit' => 500.00, 'misc_fee' => 50.00];
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 'finance') {
    if (isset($_POST['f_search_btn'])) {
        $term = $conn->real_escape_string($_POST['search_term']);
        $f_results = $conn->query("SELECT * FROM users WHERE role_id=2 AND is_approved=1 AND (first_name LIKE '%$term%' OR last_name LIKE '%$term%' OR username LIKE '%$term%')");
    }
    if (isset($_GET['view_stud'])) {
        $sid = intval($_GET['view_stud']); $syid = intval($_GET['sy_id']); $semid = intval($_GET['sem_id']);
        $f_view_stud = $conn->query("SELECT * FROM users WHERE id=$sid")->fetch_assoc();
        $acc_check = $conn->query("SELECT * FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid");
        if($acc_check->num_rows > 0) $f_stud_acc = $acc_check->fetch_assoc();
        else {
            $subs=$conn->query("SELECT s.units FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=$sid AND ss.sy_id=$syid AND ss.sem_id=$semid AND ss.status='Enrolled'"); $tu=0;
            while($s=$subs->fetch_assoc()) $tu+=$s['units'];
            $tot=($tu*$sys_settings['fee_per_unit'])+$sys_settings['misc_fee'];
            $conn->query("INSERT INTO student_accounts (student_id, sy_id, sem_id, total_fees, balance) VALUES ($sid, $syid, $semid, $tot, $tot)");
            $f_stud_acc = $conn->query("SELECT * FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid")->fetch_assoc();
        }
        $f_stud_subs = $conn->query("SELECT s.* FROM student_subjects ss JOIN subjects s ON ss.subject_id=s.id WHERE ss.student_id=$sid AND ss.sy_id=$syid AND ss.sem_id=$semid AND ss.status='Enrolled'");
        $f_hist = $conn->query("SELECT * FROM payments WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid ORDER BY date_paid DESC");
    }
    if (isset($_POST['add_discount_btn'])) {
        $aid=intval($_POST['disc_acc_id']); $dt=$_POST['disc_type']; $dv=floatval($_POST['disc_val']);
        $acc=$conn->query("SELECT * FROM student_accounts WHERE id=$aid")->fetch_assoc();
        $base_total=floatval($acc['total_fees']);
        $discount=0;
        if($dt=='fixed') $discount=$dv;
        elseif($dt=='percent') $discount=$base_total*($dv/100);
        $new_total=$base_total-$discount;
        $new_balance=max(0,$new_total-floatval($acc['amount_paid']));
        $permit=$new_balance<=0?'Approved':'Pending';
        $conn->query("UPDATE student_accounts SET discount_type='$dt', discount_value=$dv, total_fees=$new_total, balance=$new_balance, permit_status='$permit' WHERE id=$aid");
        $finance_msg="<div class='success'>Discount applied. Balance recalculated.</div>";
    }
    if (isset($_POST['f_pay_btn'])) {
        $sid=intval($_POST['pay_sid']); $syid=intval($_POST['pay_sy']); $semid=intval($_POST['pay_sem']); $amt=floatval($_POST['pay_amt']);
        $or="OR-".date('YmdHis')."-".$sid;
        $conn->query("INSERT INTO payments (student_id, sy_id, sem_id, amount, or_number) VALUES ($sid, $syid, $semid, $amt, '$or')");
        $acc=$conn->query("SELECT id, amount_paid, total_fees FROM student_accounts WHERE student_id=$sid AND sy_id=$syid AND sem_id=$semid")->fetch_assoc();
        $new_paid=floatval($acc['amount_paid'])+$amt;
        $raw_due=floatval($acc['total_fees'])-$new_paid;
        $new_bal=max(0,$raw_due);
        $permit=$raw_due<=0?'Approved':'Pending';
        $conn->query("UPDATE student_accounts SET amount_paid=$new_paid, balance=$new_bal, permit_status='$permit' WHERE id=".$acc['id']);
        $finance_msg="<div class='success'>Payment recorded. OR: $or</div>";
    }
}

$name = $_SESSION['name']; $role = $_SESSION['role'];
$tab = $_GET['tab'] ?? 'billing';
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
        <?php echo $finance_msg; ?>
        <h2>Welcome, <?php echo htmlspecialchars($name); ?>! <span class="role-badge">Finance Office</span></h2>
        
        <?php if(isset($_GET['action']) && $_GET['action'] == 'print_receipt'): 
            $or = $conn->real_escape_string($_GET['or']);
            $q = $conn->query("SELECT p.*, u.first_name, u.last_name, sy.name as sy_name, sem.name as sem_name FROM payments p JOIN users u ON p.student_id=u.id JOIN school_years sy ON p.sy_id=sy.id JOIN semesters sem ON p.sem_id=sem.id WHERE p.or_number='$or'");
            if($q && $q->num_rows > 0) {
                $pay = $q->fetch_assoc();
        ?>
        <div id="receipt-area">
            <h3>OFFICIAL RECEIPT</h3>
            <p><strong><?php echo $pay['sy_name']; ?> - <?php echo $pay['sem_name']; ?></strong></p>
            <hr>
            <p style="margin:10px 0;"><strong>Student:</strong> <?php echo htmlspecialchars($pay['first_name'].' '.$pay['last_name']); ?></p>
            <p style="margin-bottom:20px;"><strong>Date:</strong> <?php echo date("F j, Y g:i a", strtotime($pay['date_paid'])); ?></p>
            <div class="receipt-line" style="border-top:1px solid #000; margin-top:10px;"><span>Description</span><span>Amount</span></div>
            <div class="receipt-line"><span>Tuition & Fees Payment</span><span>₱ <?php echo number_format($pay['amount'],2); ?></span></div>
            <div class="receipt-line" style="font-weight:bold; font-size:18px;"><span>TOTAL PAID</span><span>₱ <?php echo number_format($pay['amount'],2); ?></span></div>
            <p style="margin-top:20px; font-size:12px;">OR Number: <?php echo $pay['or_number']; ?></p>
            <p style="font-size:10px;">This is a system generated receipt. Signature not required for digital validation.</p>
        </div>
        <div class="no-print" style="text-align:center; margin-top:20px;">
            <button onclick="window.print()" class="btn btn-success">Print Receipt</button>
            <a href="?action=finance" class="btn">Close</a>
        </div>
        <?php exit(); } else { echo "<p style='color:red; text-align:center;'>Receipt not found.</p><a href='?action=finance'>Back</a>"; exit(); } endif; ?>

        <div class="finance-tabs">
            <a href="?action=finance&tab=billing" class="btn btn-sm <?php echo $tab=='billing'?'active':''; ?>">💳 Billing</a>
            <a href="?action=finance&tab=reports" class="btn btn-sm <?php echo $tab=='reports'?'active':''; ?>">📊 Reports</a>
        </div>

        <?php if($tab == 'billing'): ?>
            <?php if(!$f_view_stud): ?>
            <div class="card">
                <h3>Student Billing & Payments</h3>
                <div class="alert-box">Search for a student to view ledger, calculate fees, and apply discounts.</div>
                <form method="POST" class="form-row" style="max-width: 600px; align-items:flex-end;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group" style="flex:2;">
                        <label>Search Student (Name or Username)</label>
                        <input type="text" name="search_term" placeholder="e.g. Dela Cruz or juan123" required>
                    </div>
                    <div class="form-group" style="flex:0 0 auto;">
                        <button type="submit" name="f_search_btn" class="btn btn-success">Search</button>
                    </div>
                </form>

                <?php if(isset($f_results) && $f_results->num_rows > 0): ?>
                <h4 style="margin-top:20px; color:var(--primary-blue);">Search Results</h4>
                <table>
                    <tr><th>Name</th><th>Course/Year</th><th>Action</th></tr>
                    <?php while($s=$f_results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['first_name'].' '.$s['last_name']); ?> <br><small><?php echo $s['username']; ?></small></td>
                        <td><?php echo $s['course'].' - '.$s['year_level']; ?></td>
                        <td>
                            <form method="GET" class="form-row" style="margin:0; gap:5px;">
                                <input type="hidden" name="action" value="finance">
                                <input type="hidden" name="view_stud" value="<?php echo $s['id']; ?>">
                                <div style="flex:0 0 80px;"><select name="sy_id" style="padding:5px;"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($y=$sys->fetch_assoc()) echo "<option value='".$y['id']."'>".$y['name']."</option>"; ?></select></div>
                                <div style="flex:0 0 90px;"><select name="sem_id" style="padding:5px;"><?php $sms=$conn->query("SELECT * FROM semesters"); while($m=$sms->fetch_assoc()) echo "<option value='".$m['id']."'>".$m['name']."</option>"; ?></select></div>
                                <button type="submit" class="btn btn-sm">View</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <?php elseif(isset($f_results)): ?>
                    <div class="error" style="margin-top:15px;">No students found.</div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="dashboard-container" style="max-width:1000px;">
                <button onclick="window.location.href='?action=finance&tab=billing'" class="btn btn-sm" style="margin-bottom:15px;">&larr; Back to Search</button>
                
                <?php if($f_view_stud): ?>
                <div class="card">
                    <div class="form-row" style="justify-content:space-between; align-items:center;">
                        <div>
                            <h3 style="border:none; margin:0;">Statement of Account</h3>
                            <p style="color:#666;">
                                <strong>Name:</strong> <?php echo htmlspecialchars($f_view_stud['first_name'].' '.$f_view_stud['last_name']); ?><br>
                                <strong>Course:</strong> <?php echo $f_view_stud['course']; ?> | <strong>Year:</strong> <?php echo $f_view_stud['year_level']; ?>
                            </p>
                        </div>
                        <div style="text-align:right;">
                            <h2 style="border:none; margin:0; font-size:18px;"><?php echo $conn->query("SELECT name FROM school_years WHERE id=".intval($_GET['sy_id']))->fetch_assoc()['name']; ?></h2>
                            <h2 style="border:none; margin:0; font-size:16px;"><?php echo $conn->query("SELECT name FROM semesters WHERE id=".intval($_GET['sem_id']))->fetch_assoc()['name']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($f_stud_acc): ?>
                <div class="grid-2">
                    <div>
                        <div class="card">
                            <h4>Enrolled Subjects</h4>
                            <?php if($f_stud_subs): ?>
                            <table style="font-size:13px;">
                                <tr><th>Code</th><th>Desc</th><th>Units</th></tr>
                                <?php $total_units = 0; while($sub=$f_stud_subs->fetch_assoc()): $total_units += $sub['units']; ?>
                                <tr><td><?php echo $sub['code']; ?></td><td><?php echo $sub['name']; ?></td><td style="text-align:center;"><?php echo $sub['units']; ?></td></tr>
                                <?php endwhile; ?>
                                <tr style="background:#f0f0f0;"><td colspan="2" style="text-align:right; font-weight:bold;">Total Units:</td><td style="text-align:center; font-weight:bold;"><?php echo $total_units; ?></td></tr>
                            </table>
                            <?php else: echo "<small>No subjects found.</small>"; endif; ?>
                        </div>

                        <div class="card">
                            <h4>Apply Discount</h4>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="disc_acc_id" value="<?php echo $f_stud_acc['id']; ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="disc_type" onchange="this.form.disc_val.disabled = this.value=='none'">
                                            <option value="none" <?php echo $f_stud_acc['discount_type']=='none'?'selected':''; ?>>No Discount</option>
                                            <option value="fixed" <?php echo $f_stud_acc['discount_type']=='fixed'?'selected':''; ?>>Fixed Amount (₱)</option>
                                            <option value="percent" <?php echo $f_stud_acc['discount_type']=='percent'?'selected':''; ?>>Percentage (%)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Value</label>
                                        <input type="number" step="0.01" name="disc_val" value="<?php echo $f_stud_acc['discount_value']; ?>" <?php echo $f_stud_acc['discount_type']=='none'?'disabled':''; ?>>
                                    </div>
                                </div>
                                <button type="submit" name="add_discount_btn" class="btn btn-sm btn-warning" style="width:100%;">Apply Discount</button>
                            </form>
                        </div>

                        <div class="card">
                            <h4>Payment History</h4>
                            <?php if($f_hist && $f_hist->num_rows > 0): ?>
                            <table style="font-size:12px;">
                                <tr><th>OR #</th><th>Date</th><th>Amount</th></tr>
                                <?php while($h=$f_hist->fetch_assoc()): ?>
                                <tr><td><?php echo $h['or_number']; ?></td><td><?php echo date("M d, Y", strtotime($h['date_paid'])); ?></td><td>₱<?php echo number_format($h['amount'],2); ?></td></tr>
                                <?php endwhile; ?>
                            </table>
                            <?php else: echo "<small>No payments yet.</small>"; endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="card permit-box <?php echo strtolower($f_stud_acc['permit_status']); ?>">
                            <h2 style="border:none;"><?php echo $f_stud_acc['permit_status']; ?></h2>
                            <p>Status as of today</p>
                        </div>

                        <div class="card">
                            <h4>Current Balance</h4>
                            <div class="big-total">₱<?php echo number_format($f_stud_acc['balance'],2); ?></div>
                            <div class="finance-summary" style="margin-bottom:20px;">
                                <table>
                                    <?php 
                                    $base_total = floatval($f_stud_acc['total_fees']);
                                    $discount_amt = 0;
                                    if($f_stud_acc['discount_type'] == 'fixed') {
                                        $discount_amt = floatval($f_stud_acc['discount_value']);
                                    } elseif($f_stud_acc['discount_type'] == 'percent') {
                                        $discount_amt = $base_total * (floatval($f_stud_acc['discount_value']) / 100);
                                    }
                                    $original_total = $base_total + $discount_amt;
                                    ?>
                                    <?php if($f_stud_acc['discount_type']!='none'): ?>
                                    <tr><td>Original Fees</td><td style="text-align:right;">₱<?php echo number_format($original_total,2); ?></td></tr>
                                    <tr style="color:blue;"><td>Discount (<?php echo ucfirst($f_stud_acc['discount_type']).' '.$f_stud_acc['discount_value']; ?>)</td><td style="text-align:right;">-₱<?php echo number_format($discount_amt,2); ?></td></tr>
                                    <?php endif; ?>
                                    <tr><td>Total Fees</td><td style="text-align:right;">₱<?php echo number_format($f_stud_acc['total_fees'],2); ?></td></tr>
                                    <tr><td>Amount Paid</td><td style="text-align:right; color:green;">(₱<?php echo number_format($f_stud_acc['amount_paid'],2); ?>)</td></tr>
                                </table>
                            </div>

                            <?php if($f_stud_acc['balance'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="pay_sid" value="<?php echo $f_view_stud['id']; ?>">
                                <input type="hidden" name="pay_sy" value="<?php echo $_GET['sy_id']; ?>">
                                <input type="hidden" name="pay_sem" value="<?php echo $_GET['sem_id']; ?>">
                                <div class="form-group">
                                    <label>Payment Amount (₱)</label>
                                    <input type="number" step="0.01" name="pay_amt" required>
                                </div>
                                <button type="submit" name="f_pay_btn" class="btn btn-success" style="width:100%;">Process Payment & Print</button>
                            </form>
                            <?php else: ?>
                            <button class="btn" style="width:100%; background:#ccc;" disabled>Fully Paid</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php elseif($tab == 'reports'): ?>
            <div class="card">
                <h3>Financial Reports</h3>
                <p>Select filters below to generate data.</p>
                <form method="GET" class="form-row" style="max-width:500px; margin-bottom:20px;">
                    <input type="hidden" name="action" value="finance"><input type="hidden" name="tab" value="reports">
                    <div class="form-group"><label>School Year</label><select name="r_sy" onchange="this.form.submit()"><?php $sys=$conn->query("SELECT * FROM school_years ORDER BY name DESC"); while($s=$sys->fetch_assoc()) echo "<option value='".$s['id']."' ".(($_GET['r_sy']??1)==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div>
                    <div class="form-group"><label>Semester</label><select name="r_sem" onchange="this.form.submit()"><?php $sms=$conn->query("SELECT * FROM semesters"); while($s=$sms->fetch_assoc()) echo "<option value='".$s['id']."' ".(($_GET['r_sem']??1)==$s['id']?'selected':'').">".$s['name']."</option>"; ?></select></div>
                </form>
            </div>

            <?php 
            $r_sy = intval($_GET['r_sy'] ?? 1);
            $r_sem = intval($_GET['r_sem'] ?? 1);
            
            $inc_q = $conn->query("SELECT SUM(amount) as total FROM payments WHERE sy_id=$r_sy AND sem_id=$r_sem");
            $inc = $inc_q ? $inc_q->fetch_assoc()['total'] : 0;

            $daily_q = $conn->query("SELECT SUM(amount) as total FROM payments WHERE sy_id=$r_sy AND sem_id=$r_sem AND DATE(date_paid)=CURDATE()");
            $daily = $daily_q ? $daily_q->fetch_assoc()['total'] : 0;
            
            $del_q = $conn->query("SELECT u.first_name, u.last_name, sa.balance FROM student_accounts sa JOIN users u ON sa.student_id=u.id WHERE sa.sy_id=$r_sy AND sa.sem_id=$r_sem AND sa.balance > 0");
            $del = $del_q ? $del_q->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            
            <div class="grid-2">
                <div class="card" style="text-align:center; background:#d4edda; border-color:#c3e6cb;">
                    <h1 style="color:#155724; font-size:36px;">₱ <?php echo number_format($inc,2); ?></h1>
                    <p>Total Collected Income</p>
                    <small>For this Semester</small>
                </div>
                <div class="card" style="text-align:center; background:#e2e3e5; border-color:#d6d8db;">
                    <h1 style="color:#383d41; font-size:36px;">₱ <?php echo number_format($daily,2); ?></h1>
                    <p>Today's Collection</p>
                    <small>Payments made today</small>
                </div>
            </div>

            <div class="card">
                <h4>Delinquent Students (Unpaid Balance)</h4>
                <table>
                    <tr><th>Name</th><th>Balance</th><th>Status</th></tr>
                    <?php if(empty($del)): ?>
                        <tr><td colspan="3" style="text-align:center;">No delinquents found! Everyone is paid up.</td></tr>
                    <?php else: ?>
                        <?php foreach($del as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['first_name'].' '.$d['last_name']); ?></td>
                            <td style="color:red; font-weight:bold;">₱ <?php echo number_format($d['balance'],2); ?></td>
                            <td><span class="role-badge" style="background:red;">Unpaid</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
