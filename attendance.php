<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

// Check in
if(isset($_POST['checkin'])){
  $check = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id=$_SESSION[user_id] AND date=CURDATE()");
  
  if(mysqli_num_rows($check) > 0){
    $success = "Already checked in today!";
  } else {
    mysqli_query($conn, "INSERT INTO attendance (employee_id, date, check_in, status) VALUES ($_SESSION[user_id], CURDATE(), CURTIME(), 'Present')");
    $success = "✓ Check-in successful!";
  }
}

$records = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id=$_SESSION[user_id] ORDER BY date DESC");
?>

<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

// Check in
if(isset($_POST['checkin'])){
  $check = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id=$_SESSION[user_id] AND date=CURDATE()");
  
  if(mysqli_num_rows($check) > 0){
    $success = "Already checked in today!";
  } else {
    mysqli_query($conn, "INSERT INTO attendance (employee_id, date, check_in, status) VALUES ($_SESSION[user_id], CURDATE(), CURTIME(), 'Present')");
    $success = "✓ Check-in successful!";
  }
}

$records = mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id=$_SESSION[user_id] ORDER BY date DESC");
$today_record = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendance WHERE employee_id=$_SESSION[user_id] AND date=CURDATE()"));
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Attendance</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f5f5f5; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
    .container { display: flex; min-height: 100vh; }
    
    /* Sidebar */
    .sidebar { width: 220px; background: #5b3fa0; padding: 30px 20px; color: white; position: fixed; height: 100vh; overflow-y: auto; }
    .sidebar-header { display: flex; align-items: center; gap: 10px; margin-bottom: 40px; font-size: 18px; font-weight: 700; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 10px; }
    .nav-item { padding: 12px 15px; border-radius: 8px; color: rgba(255,255,255,0.7); text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; }
    .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
    .nav-icon { font-size: 18px; }
    .logout-nav { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    
    /* Main Content */
    .main { margin-left: 220px; flex: 1; padding: 40px; }
    .header { margin-bottom: 30px; }
    .page-title { font-size: 24px; font-weight: 700; color: #333; margin-bottom: 5px; }
    .page-subtitle { color: #999; font-size: 14px; }
    
    /* Status Card */
    .status-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 30px; display: flex; gap: 30px; align-items: center; justify-content: space-between; }
    .status-content { display: flex; gap: 20px; align-items: center; flex: 1; }
    .status-icon { font-size: 48px; }
    .status-info h2 { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 5px; }
    .status-info p { color: #999; font-size: 14px; }
    .status-badge { display: inline-block; padding: 8px 16px; background: #e8f5e9; color: #2e7d32; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .clock-btn { background: #f44336; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; white-space: nowrap; }
    .clock-btn:hover { background: #d32f2f; transform: translateY(-2px); }
    .clock-btn.checkin { background: #4caf50; }
    .clock-btn.checkin:hover { background: #388e3c; }
    
    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .stat-icon { font-size: 28px; margin-bottom: 10px; }
    .stat-label { font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 8px; font-weight: 600; letter-spacing: 0.5px; }
    .stat-value { font-size: 24px; font-weight: 700; color: #333; }
    .stat-change { font-size: 12px; color: #4caf50; margin-top: 5px; }
    
    /* Table */
    .table-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 15px; }
    .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f5f5f5; padding: 15px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #999; letter-spacing: 0.5px; border-bottom: 1px solid #e0e0e0; }
    td { padding: 15px; border-bottom: 1px solid #e0e0e0; }
    tr:hover { background: #fafafa; }
    tr:last-child td { border-bottom: none; }
    .date-label { color: #999; font-size: 12px; }
    .time { color: #333; font-weight: 500; }
    .hours { color: #333; font-weight: 600; }
    .status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-present { background: #e8f5e9; color: #2e7d32; }
    .status-leave { background: #f3e5f5; color: #6a1b9a; }
    .status-absent { background: #ffebee; color: #c62828; }
    
    @media (max-width: 1024px) {
      .stats-grid { grid-template-columns: 1fr; }
      .status-card { flex-direction: column; text-align: center; }
    }
    
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 20px; }
      .stats-grid { grid-template-columns: 1fr; }
      table { font-size: 12px; }
      th, td { padding: 10px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <span>🏢</span>
        <span>StaffSphere</span>
      </div>
      
      <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="profile.php" class="nav-item">
          <span class="nav-icon">👤</span>
          <span>Profile</span>
        </a>
        <a href="attendance.php" class="nav-item active">
          <span class="nav-icon">📋</span>
          <span>Attendance</span>
        </a>
        <a href="leave.php" class="nav-item">
          <span class="nav-icon">📝</span>
          <span>Leave Requests</span>
        </a>
        <a href="payroll.php" class="nav-item">
          <span class="nav-icon">💰</span>
          <span>Payroll</span>
        </a>
        
        <div class="logout-nav">
          <a href="../auth/logout.php" class="nav-item">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="main">
      <div class="header">
        <div class="page-title">Attendance</div>
        <div class="page-subtitle">Track your work hours</div>
      </div>
      
      <?php if(isset($success)): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <!-- Current Status -->
      <div class="status-card">
        <div class="status-content">
          <div class="status-icon">⏱️</div>
          <div>
            <h2><?php echo $today_record ? 'Working' : 'Not Checked In'; ?></h2>
            <?php if($today_record): ?>
              <p>Clocked in at <?php echo date('h:i A', strtotime($today_record['check_in'])); ?></p>
            <?php else: ?>
              <p>Click the button to check in</p>
            <?php endif; ?>
          </div>
        </div>
        <form method="POST" style="display: inline;">
          <?php if($today_record): ?>
            <button type="button" class="clock-btn">🚪 Clock Out</button>
          <?php else: ?>
            <button type="submit" name="checkin" class="clock-btn checkin">✔️ Check In</button>
          <?php endif; ?>
        </form>
      </div>
      
      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">⏰</div>
          <div class="stat-label">Hours Today</div>
          <div class="stat-value">4h 30m</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-label">This Week</div>
          <div class="stat-value">32.5h</div>
          <div class="stat-change">+2.5h from target</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-label">This Month</div>
          <div class="stat-value">156h</div>
          <div class="stat-change">On track</div>
        </div>
      </div>
      
      <!-- Recent History -->
      <h2 class="table-title">Recent History</h2>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Hours</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $counter = 0;
            while($r = mysqli_fetch_assoc($records)){
              $counter++;
              $date = new DateTime($r['date']);
              $now = new DateTime();
              
              if($date->format('Y-m-d') === $now->format('Y-m-d')){
                $date_label = 'Today';
              } elseif($date->format('Y-m-d') === $now->modify('-1 day')->format('Y-m-d')){
                $date_label = 'Yesterday';
              } else {
                $date_label = $date->format('M d');
              }
              
              echo "
              <tr>
                <td><span class='date-label'>$date_label</span></td>
                <td><span class='time'>{$r['check_in']}</span></td>
                <td><span class='time'>-</span></td>
                <td><span class='hours'>4h 30m</span></td>
                <td><span class='status status-present'>✓ Present</span></td>
              </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
