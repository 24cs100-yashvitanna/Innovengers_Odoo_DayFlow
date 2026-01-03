<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){
  header("Location: ../auth/login.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Employee Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f5f5f5; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
    .container { display: flex; min-height: 100vh; }
    
    /* Sidebar */
    .sidebar { width: 220px; background: #5b3fa0; padding: 30px 20px; color: white; position: fixed; height: 100vh; overflow-y: auto; }
    .sidebar-header { display: flex; align-items: center; gap: 10px; margin-bottom: 40px; font-size: 18px; font-weight: 700; }
    .sidebar-icon { font-size: 24px; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 10px; }
    .nav-item { padding: 12px 15px; border-radius: 8px; color: rgba(255,255,255,0.7); text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; }
    .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
    .nav-icon { font-size: 18px; }
    .logout-nav { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    
    /* Main Content */
    .main { margin-left: 220px; flex: 1; padding: 40px; }
    .header { margin-bottom: 40px; }
    .greeting { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 5px; }
    .greeting-name { color: #5b3fa0; }
    .greeting-subtitle { color: #999; font-size: 14px; }
    
    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: flex-start; gap: 15px; }
    .stat-icon { font-size: 32px; }
    .stat-content h3 { font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 8px; font-weight: 600; letter-spacing: 0.5px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 5px; }
    .stat-change { font-size: 12px; color: #4caf50; }
    
    /* Content Sections */
    .content-wrapper { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    
    /* Quick Actions */
    .section-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; }
    .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
    .action-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; text-decoration: none; color: #333; transition: all 0.3s; }
    .action-card:hover { transform: translateY(-4px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
    .action-icon { font-size: 32px; margin-bottom: 10px; }
    .action-label { font-size: 14px; font-weight: 600; }
    .action-desc { font-size: 12px; color: #999; margin-top: 5px; }
    
    /* Clock In Card - Featured */
    .clock-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; grid-column: span 2; box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3); text-align: center; }
    .clock-icon { font-size: 48px; margin-bottom: 15px; }
    .clock-title { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
    .clock-desc { font-size: 14px; opacity: 0.9; }
    
    /* Recent Activity */
    .activity-list { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 20px; }
    .activity-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .view-all { color: #667eea; text-decoration: none; font-size: 12px; font-weight: 600; }
    .activity-item { padding: 15px 0; border-bottom: 1px solid #f0f0f0; display: flex; gap: 15px; align-items: flex-start; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .dot-success { background: #4caf50; }
    .dot-info { background: #2196f3; }
    .dot-warning { background: #ff9800; }
    .activity-text h4 { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 3px; }
    .activity-text p { font-size: 12px; color: #999; }
    
    /* Upcoming */
    .upcoming { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 20px; margin-top: 20px; }
    .upcoming-item { display: flex; gap: 15px; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0; }
    .upcoming-item:last-child { border-bottom: none; }
    .date-box { background: #f5f5f5; padding: 10px; border-radius: 8px; text-align: center; min-width: 50px; }
    .date-day { font-size: 20px; font-weight: 700; color: #667eea; }
    .date-month { font-size: 11px; color: #999; text-transform: uppercase; }
    .event-info h4 { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 3px; }
    .event-info p { font-size: 12px; color: #999; }
    .event-time { margin-left: auto; font-size: 12px; color: #667eea; font-weight: 600; }
    
    @media (max-width: 1024px) {
      .content-wrapper { grid-template-columns: 1fr; }
      .clock-card { grid-column: span 1; }
    }
    
    @media (max-width: 768px) {
      .sidebar { width: 100%; height: auto; position: relative; padding: 20px; display: none; }
      .main { margin-left: 0; padding: 20px; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .actions-grid { grid-template-columns: 1fr; }
      .clock-card { grid-column: span 1; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <span class="sidebar-icon">🏢</span>
        <span>StaffSphere</span>
      </div>
      
      <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="profile.php" class="nav-item">
          <span class="nav-icon">👤</span>
          <span>Profile</span>
        </a>
        <a href="attendance.php" class="nav-item">
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
        <div class="greeting">Good afternoon, <span class="greeting-name"><?php echo $_SESSION['username']; ?></span></div>
        <div class="greeting-subtitle">Here's what's happening with your work today.</div>
      </div>
      
      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">⏰</div>
          <div class="stat-content">
            <h3>Hours This Week</h3>
            <div class="stat-value">32.5</div>
            <div class="stat-change">+2.5 from last week</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>Leave Balance</h3>
            <div class="stat-value">12</div>
            <div class="stat-change">Days remaining</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3>Attendance Rate</h3>
            <div class="stat-value">98%</div>
            <div class="stat-change">This month</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📬</div>
          <div class="stat-content">
            <h3>Pending Requests</h3>
            <div class="stat-value">1</div>
            <div class="stat-change">Awaiting approval</div>
          </div>
        </div>
      </div>
      
      <!-- Content Wrapper -->
      <div class="content-wrapper">
        <!-- Left Column -->
        <div>
          <!-- Quick Actions -->
          <h2 class="section-title">Quick Actions</h2>
          <div class="actions-grid">
            <a href="profile.php" class="action-card">
              <div class="action-icon">👤</div>
              <div class="action-label">View Profile</div>
              <div class="action-desc">Update your information</div>
            </a>
            
            <a href="leave.php" class="action-card">
              <div class="action-icon">📝</div>
              <div class="action-label">Request Leave</div>
              <div class="action-desc">Submit a new leave request</div>
            </a>
            
            <a href="payroll.php" class="action-card">
              <div class="action-icon">💰</div>
              <div class="action-label">View Payslips</div>
              <div class="action-desc">Access your salary documents</div>
            </a>
            
            <a href="attendance.php" class="action-card">
              <div class="action-icon">✅</div>
              <div class="action-label">Check Status</div>
              <div class="action-desc">View attendance records</div>
            </a>
          </div>
          
          <!-- Clock In Card -->
          <div class="clock-card" style="margin-top: 20px;">
            <div class="clock-icon">⏱️</div>
            <div class="clock-title">Clock In/Out</div>
            <div class="clock-desc">Record your daily attendance</div>
            <a href="attendance.php" style="color: white; text-decoration: none; display: inline-block; margin-top: 15px; padding: 10px 20px; background: rgba(255,255,255,0.2); border-radius: 6px; font-weight: 600; font-size: 14px;">Go to Attendance →</a>
          </div>
        </div>
        
        <!-- Right Column -->
        <div>
          <!-- Recent Activity -->
          <div class="activity-header">
            <h2 class="section-title" style="margin-bottom: 0;">Recent Activity</h2>
            <a href="#" class="view-all">View all →</a>
          </div>
          
          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-dot dot-success"></div>
              <div class="activity-text">
                <h4>✓ Leave Approved</h4>
                <p>Your leave request for Dec 25-26 was approved</p>
                <p style="color: #bbb; font-size: 11px; margin-top: 3px;">2 hours ago</p>
              </div>
            </div>
            
            <div class="activity-item">
              <div class="activity-dot dot-info"></div>
              <div class="activity-text">
                <h4>Check-in Recorded</h4>
                <p>Clock-in recorded at 9:00 AM</p>
                <p style="color: #bbb; font-size: 11px; margin-top: 3px;">5 hours ago</p>
              </div>
            </div>
            
            <div class="activity-item">
              <div class="activity-dot dot-warning"></div>
              <div class="activity-text">
                <h4>Profile Reminder</h4>
                <p>Complete your profile to unlock all features</p>
                <p style="color: #bbb; font-size: 11px; margin-top: 3px;">1 day ago</p>
              </div>
            </div>
          </div>
          
          <!-- Upcoming -->
          <div class="upcoming">
            <h2 class="section-title">Upcoming</h2>
            <div class="upcoming-item">
              <div class="date-box">
                <div class="date-day">25</div>
                <div class="date-month">DEC</div>
              </div>
              <div class="event-info">
                <h4>Holiday - Christmas</h4>
                <p>Company-wide holiday</p>
              </div>
              <div class="event-time">In 7 days</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
