<?php
// Sidebar component - requires $active_page and $user variables to be set
// $active_page: 'dashboard', 'employees', 'attendance', 'leave', 'payroll', 'profile', 'leave_requests'
// $user: array with 'username', 'role', etc.

$is_admin = $_SESSION['role'] === 'admin';
$base_path = $is_admin ? '../' : '../';
?>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo-icon">⭐</div>
    <div class="logo-text">StaffSphere</div>
  </div>
  
  <div class="sidebar-nav">
    <?php if($is_admin): ?>
      <!-- Admin Navigation -->
      <a href="dashboard.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
        <span class="nav-icon">📊</span>
        <span>Dashboard</span>
      </a>
      <a href="employees.php" class="nav-item <?php echo $active_page === 'employees' ? 'active' : ''; ?>">
        <span class="nav-icon">👥</span>
        <span>Employees</span>
      </a>
      <a href="attendance.php" class="nav-item <?php echo $active_page === 'attendance' ? 'active' : ''; ?>">
        <span class="nav-icon">📋</span>
        <span>Attendance</span>
      </a>
      <a href="payroll.php" class="nav-item <?php echo $active_page === 'payroll' ? 'active' : ''; ?>">
        <span class="nav-icon">💰</span>
        <span>Payroll</span>
      </a>
      <a href="leave_requests.php" class="nav-item <?php echo $active_page === 'leave_requests' ? 'active' : ''; ?>">
        <span class="nav-icon">📝</span>
        <span>Leave Requests</span>
      </a>
    <?php else: ?>
      <!-- Employee Navigation -->
      <a href="dashboard.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
        <span class="nav-icon">📊</span>
        <span>Dashboard</span>
      </a>
      <a href="attendance.php" class="nav-item <?php echo $active_page === 'attendance' ? 'active' : ''; ?>">
        <span class="nav-icon">📋</span>
        <span>Attendance</span>
      </a>
      <a href="leave.php" class="nav-item <?php echo $active_page === 'leave' ? 'active' : ''; ?>">
        <span class="nav-icon">📝</span>
        <span>Leave</span>
      </a>
      <a href="payroll.php" class="nav-item <?php echo $active_page === 'payroll' ? 'active' : ''; ?>">
        <span class="nav-icon">💰</span>
        <span>Payroll</span>
      </a>
      <a href="profile.php" class="nav-item <?php echo $active_page === 'profile' ? 'active' : ''; ?>">
        <span class="nav-icon">👤</span>
        <span>Profile</span>
      </a>
    <?php endif; ?>
  </div>
  
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
      <div class="user-details">
        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
        <div class="user-role"><?php echo $is_admin ? 'Admin' : 'Employee'; ?></div>
      </div>
    </div>
    <a href="<?php echo $base_path; ?>auth/logout.php" class="logout-btn">
      <span class="nav-icon">🚪</span>
      <span>Sign Out</span>
    </a>
  </div>
</div>

<!-- Common Sidebar Styles -->
<style>
  /* Sidebar */
  .sidebar { width: 250px; background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%); padding: 30px 20px; color: white; position: fixed; height: 100vh; overflow-y: auto; display: flex; flex-direction: column; }
  .sidebar-header { display: flex; align-items: center; gap: 12px; margin-bottom: 50px; }
  .logo-icon { width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
  .logo-text { font-size: 20px; font-weight: 700; }
  .sidebar-nav { display: flex; flex-direction: column; gap: 8px; flex: 1; }
  .nav-item { padding: 14px 16px; border-radius: 10px; color: rgba(255,255,255,0.8); text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 12px; font-size: 15px; }
  .nav-item:hover { background: rgba(255,255,255,0.15); color: white; }
  .nav-item.active { background: rgba(255,255,255,0.2); color: white; font-weight: 600; }
  .nav-icon { font-size: 18px; }
  
  /* User Section */
  .user-section { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); }
  .user-info { display: flex; align-items: center; gap: 12px; padding: 12px; margin-bottom: 10px; }
  .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; }
  .user-details { flex: 1; }
  .user-name { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
  .user-role { font-size: 12px; opacity: 0.8; }
  .logout-btn { padding: 12px 16px; border-radius: 10px; color: rgba(255,255,255,0.8); text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.3s; font-size: 15px; }
  .logout-btn:hover { background: rgba(255,255,255,0.15); color: white; }
  
  @media (max-width: 768px) {
    .sidebar { display: none; }
  }
</style>
