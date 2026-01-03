<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

// Apply for leave
if(isset($_POST['apply'])){
  $leave_type = $_POST['leave_type'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $reason = $_POST['reason'];
  
  mysqli_query($conn, "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status) VALUES ($_SESSION[user_id], '$leave_type', '$start_date', '$end_date', '$reason', 'pending')");
  $success = "✓ Leave request submitted!";
}

$records = mysqli_query($conn, "SELECT * FROM leaves WHERE employee_id=$_SESSION[user_id] ORDER BY start_date DESC");
?>

<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

// Apply for leave
if(isset($_POST['apply'])){
  $leave_type = $_POST['leave_type'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $reason = $_POST['reason'];
  
  mysqli_query($conn, "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status) VALUES ($_SESSION[user_id], '$leave_type', '$start_date', '$end_date', '$reason', 'pending')");
  $success = "✓ Leave request submitted!";
}

$records = mysqli_query($conn, "SELECT * FROM leaves WHERE employee_id=$_SESSION[user_id] ORDER BY start_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Leave Request</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #1a1a2e; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; padding: 20px; }
    .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header-top h1 { font-size: 32px; color: #ffffff; margin-bottom: 5px; }
    .header-top p { color: #888; font-size: 14px; }
    .logout-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .logout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid; }
    .alert-success { background: #1a4d2e; color: #4ade80; border-left-color: #4ade80; }
    .form-card { background: #252d48; border: 1px solid #404966; border-radius: 12px; padding: 25px; margin-bottom: 30px; }
    .form-card h3 { font-size: 18px; color: #ffffff; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #404966; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 8px; color: #aaa; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    input, select, textarea { width: 100%; padding: 12px; background: #1a1f35; border: 1px solid #404966; border-radius: 5px; color: #e0e0e0; font-size: 14px; font-family: inherit; transition: all 0.3s; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 10px rgba(102, 126, 234, 0.2); }
    .submit-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-weight: 600; margin-top: 10px; transition: all 0.3s; }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .table-container { background: #252d48; border: 1px solid #404966; border-radius: 12px; overflow: hidden; margin-bottom: 30px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1a1f35; color: #aaa; padding: 15px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #404966; }
    td { padding: 15px; border-bottom: 1px solid #404966; color: #e0e0e0; }
    tr:hover { background: #2d3451; }
    .badge { padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .badge-pending { background: #4d3a1a; color: #fbbf24; }
    .badge-approved { background: #1a4d2e; color: #4ade80; }
    .badge-rejected { background: #4d1a1a; color: #ff6b6b; }
    .bottom-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #252d48; border-top: 1px solid #404966; padding: 10px 0; z-index: 1000; }
    @media (max-width: 768px) { body { padding-bottom: 80px; } .bottom-nav { display: flex; justify-content: space-around; } .grid-2 { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="header-top">
    <div>
      <h1>📝 Leave Request</h1>
      <p>Apply for and manage your leaves</p>
    </div>
    <a href="../auth/logout.php" class="logout-btn">✕ Logout</a>
  </div>

  <?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>

  <!-- Leave Application Form -->
  <div class="form-card">
    <h3>📋 Leave Application Form</h3>
    <form method="POST">
      <div class="form-group">
        <label>Leave Type</label>
        <select name="leave_type" required>
          <option value="">-- Select Leave Type --</option>
          <option value="Sick Leave">🤒 Sick Leave</option>
          <option value="Paid Leave">✓ Paid Leave</option>
          <option value="Casual Leave">😊 Casual Leave</option>
          <option value="Unpaid Leave">Unpaid Leave</option>
        </select>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>From Date</label>
          <input type="date" name="start_date" required>
        </div>
        <div class="form-group">
          <label>To Date</label>
          <input type="date" name="end_date" required>
        </div>
      </div>
      <div class="form-group">
        <label>Reason</label>
        <textarea name="reason" rows="4" placeholder="Please provide a reason for your leave..." required></textarea>
      </div>
      <button type="submit" name="apply" class="submit-btn">📤 Submit Leave Request</button>
    </form>
  </div>

  <!-- Leave History -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Leave Type</th>
          <th>From Date</th>
          <th>To Date</th>
          <th>Reason</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        while($r = mysqli_fetch_assoc($records)){
          if($r['status'] == 'pending') {
            $badge = '<span class="badge badge-pending">⏳ Pending</span>';
          } elseif($r['status'] == 'approved') {
            $badge = '<span class="badge badge-approved">✓ Approved</span>';
          } else {
            $badge = '<span class="badge badge-rejected">✗ Rejected</span>';
          }
          
          echo "
          <tr>
            <td>{$r['leave_type']}</td>
            <td>{$r['start_date']}</td>
            <td>{$r['end_date']}</td>
            <td>{$r['reason']}</td>
            <td>$badge</td>
          </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="bottom-nav">
    <a href="dashboard.php" class="nav-item">📊</a>
    <a href="attendance.php" class="nav-item">📋</a>
    <a href="leave.php" class="nav-item active">📝</a>
    <a href="payroll.php" class="nav-item">💰</a>
    <a href="profile.php" class="nav-item">👤</a>
  </div>
</body>
</html>
