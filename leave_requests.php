<?php
include('../db.php');
if($_SESSION['role'] !== 'admin'){
  header("Location: ../auth/login.php");
  exit;
}

// Handle AJAX approve/reject
if(isset($_POST['action']) && isset($_POST['id'])){
  $action = $_POST['action'];
  $id = $_POST['id'];
  
  if($action == 'approve'){
    mysqli_query($conn, "UPDATE leaves SET status='approved' WHERE id=$id");
    echo "approved";
  } elseif($action == 'reject'){
    mysqli_query($conn, "UPDATE leaves SET status='rejected' WHERE id=$id");
    echo "rejected";
  }
  exit;
}

// Get stats
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM leaves WHERE status='pending'"));
$approved_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM leaves WHERE status='approved' AND MONTH(start_date)=MONTH(CURDATE())"));
$rejected_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM leaves WHERE status='rejected' AND MONTH(start_date)=MONTH(CURDATE())"));

$records = mysqli_query($conn, "
  SELECT l.*, u.username, u.employee_id, u.email
  FROM leaves l
  JOIN users u ON l.employee_id = u.id 
  ORDER BY 
    CASE 
      WHEN l.status = 'pending' THEN 1
      WHEN l.status = 'approved' THEN 2
      WHEN l.status = 'rejected' THEN 3
    END,
    l.start_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Leave Approvals</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f5f5f5; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
    .container { display: flex; min-height: 100vh; }
    
    /* Sidebar */
    .sidebar { width: 250px; background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%); padding: 30px 20px; color: white; position: fixed; height: 100vh; overflow-y: auto; display: flex; flex-direction: column; }
    .sidebar-header { display: flex; align-items: center; gap: 12px; margin-bottom: 50px; }
    .logo-icon { width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .logo-text { font-size: 20px; font-weight: 700; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .nav-item { padding: 14px 16px; border-radius: 10px; color: rgba(255,255,255,0.8); text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 12px; font-size: 15px; }
    .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
    .logout-nav { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    
    /* User Section */
    .user-section { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); }
    .user-info { display: flex; align-items: center; gap: 12px; padding: 12px; margin-bottom: 10px; }
    .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; }
    .user-details { flex: 1; }
    .user-name { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
    .user-role { font-size: 12px; opacity: 0.8; }
    .logout-btn-link { padding: 12px 16px; border-radius: 10px; color: rgba(255,255,255,0.8); text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.3s; font-size: 15px; }
    .logout-btn-link:hover { background: rgba(255,255,255,0.15); color: white; }
    
    /* Main Content */
    .main { margin-left: 250px; flex: 1; padding: 40px; }
    .header-section { margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 5px; }
    .page-subtitle { color: #999; font-size: 14px; }
    
    /* Stats Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 15px; }
    .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .stat-icon.pending { background: #fff3e0; }
    .stat-icon.approved { background: #e8f5e9; }
    .stat-icon.rejected { background: #ffebee; }
    .stat-content { flex: 1; }
    .stat-number { font-size: 32px; font-weight: 700; color: #333; margin-bottom: 2px; }
    .stat-label { font-size: 13px; color: #999; font-weight: 600; margin-bottom: 2px; }
    .stat-sublabel { font-size: 11px; color: #ff9800; font-weight: 600; }
    .stat-sublabel.green { color: #4caf50; }
    .stat-sublabel.red { color: #f44336; }
    
    /* Filter Badges */
    .filter-badges { display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; }
    .badge-filter { padding: 10px 20px; background: #5b3fa0; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; font-size: 14px; }
    .badge-filter.secondary { background: white; color: #333; border: 1px solid #ddd; }
    .badge-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .badge-count { display: inline-block; background: #ff9800; color: white; border-radius: 12px; padding: 2px 8px; margin-left: 5px; font-size: 12px; }
    
    /* Leave Request Cards */
    .requests-container { display: flex; flex-direction: column; gap: 15px; }
    .request-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; gap: 20px; align-items: center; transition: all 0.3s; }
    .request-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
    .request-card.hidden { display: none; }
    
    .request-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px; flex-shrink: 0; }
    
    .request-info { flex: 1; }
    .request-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .request-name { font-size: 16px; font-weight: 700; color: #333; }
    .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .status-badge.pending { background: #fff3e0; color: #ff9800; }
    .status-badge.approved { background: #e8f5e9; color: #4caf50; }
    .status-badge.rejected { background: #ffebee; color: #f44336; }
    
    .request-dept { font-size: 13px; color: #999; margin-bottom: 10px; }
    
    .request-details { display: flex; gap: 20px; margin-bottom: 8px; font-size: 13px; color: #666; }
    .detail-item { display: flex; align-items: center; gap: 5px; }
    
    .request-reason { font-size: 13px; color: #666; margin-bottom: 5px; }
    .request-time { font-size: 12px; color: #999; }
    
    .request-actions { display: flex; gap: 10px; flex-shrink: 0; }
    .btn-action { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.3s; display: flex; align-items: center; gap: 5px; }
    .btn-approve { background: #4caf50; color: white; }
    .btn-approve:hover { background: #45a049; transform: translateY(-2px); }
    .btn-reject { background: #f44336; color: white; }
    .btn-reject:hover { background: #da190b; transform: translateY(-2px); }
    .btn-action:disabled { opacity: 0.5; cursor: not-allowed; }
    
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 20px; }
      .stats-grid { grid-template-columns: 1fr; }
      .request-card { flex-direction: column; align-items: flex-start; }
      .request-actions { width: 100%; }
      .btn-action { flex: 1; justify-content: center; }
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
          <span>📊</span>
          <span>Dashboard</span>
        </a>
        <a href="employees.php" class="nav-item">
          <span>👥</span>
          <span>Employees</span>
        </a>
        <a href="attendance.php" class="nav-item">
          <span>📋</span>
          <span>Attendance</span>
        </a>
        <a href="leave_requests.php" class="nav-item active">
          <span>📝</span>
          <span>Leave Approvals</span>
        </a>
        <a href="payroll.php" class="nav-item">
          <span>💰</span>
          <span>Payroll</span>
        </a>
        
        <div class="logout-nav">
          <a href="../auth/logout.php" class="nav-item">
            <span>🚪</span>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="main">
      <div class="header-section">
        <h1 class="page-title">Leave Approvals</h1>
        <p class="page-subtitle">Review and manage leave requests</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon pending">⏳</div>
          <div class="stat-content">
            <div class="stat-label">Pending</div>
            <div class="stat-number"><?php echo $pending_count; ?></div>
            <div class="stat-sublabel">Needs attention</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon approved">✓</div>
          <div class="stat-content">
            <div class="stat-label">Approved</div>
            <div class="stat-number"><?php echo $approved_count; ?></div>
            <div class="stat-sublabel green">This month</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon rejected">✗</div>
          <div class="stat-content">
            <div class="stat-label">Rejected</div>
            <div class="stat-number"><?php echo $rejected_count; ?></div>
            <div class="stat-sublabel red">This month</div>
          </div>
        </div>
      </div>
      
      <!-- Filter Badges -->
      <div class="filter-badges">
        <button class="badge-filter" data-filter="all">All Requests</button>
        <button class="badge-filter secondary" data-filter="pending">Pending<span class="badge-count"><?php echo $pending_count; ?></span></button>
        <button class="badge-filter secondary" data-filter="approved">Approved</button>
        <button class="badge-filter secondary" data-filter="rejected">Rejected</button>
      </div>
      
      <!-- Leave Requests -->
      <div class="requests-container">
        <?php
        while($r = mysqli_fetch_assoc($records)){
          $initials = strtoupper(substr($r['username'], 0, 1)) . strtoupper(substr($r['username'], -1, 1));
          
          // Calculate days
          $start = new DateTime($r['start_date']);
          $end = new DateTime($r['end_date']);
          $days = $start->diff($end)->days + 1;
          
          // Format dates
          $date_range = $start->format('M d') . '-' . $end->format('d, Y');
          
          $status_class = $r['status'];
          $status_text = ucfirst($r['status']);
          
          $disabled = $r['status'] != 'pending' ? 'disabled' : '';
          
          echo "
          <div class='request-card' data-status='{$r['status']}'>
            <div class='request-avatar'>$initials</div>
            
            <div class='request-info'>
              <div class='request-header'>
                <span class='request-name'>{$r['username']}</span>
                <span class='status-badge $status_class'>$status_text</span>
              </div>
              
              <div class='request-dept'>Marketing</div>
              
              <div class='request-details'>
                <div class='detail-item'>
                  <span>📅</span>
                  <span>{$r['leave_type']}</span>
                </div>
                <div class='detail-item'>
                  <span>🕐</span>
                  <span>$date_range ($days day" . ($days > 1 ? 's' : '') . ")</span>
                </div>
              </div>
              
              <div class='request-reason'>Reason: {$r['reason']}</div>
            </div>
            
            <div class='request-actions'>
              <button class='btn-action btn-approve' onclick='handleLeave({$r['id']}, \"approve\", this)' $disabled>
                <span>✓</span> Approve
              </button>
              <button class='btn-action btn-reject' onclick='handleLeave({$r['id']}, \"reject\", this)' $disabled>
                <span>✗</span> Reject
              </button>
            </div>
          </div>";
        }
        ?>
      </div>
    </div>
  </div>
  
  <script>
    // Handle approve/reject without page reload
    function handleLeave(id, action, button) {
      const formData = new FormData();
      formData.append('id', id);
      formData.append('action', action);
      
      fetch('leave_requests.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        const card = button.closest('.request-card');
        const statusBadge = card.querySelector('.status-badge');
        const buttons = card.querySelectorAll('.btn-action');
        
        // Update status badge
        statusBadge.className = 'status-badge ' + data;
        statusBadge.textContent = data.charAt(0).toUpperCase() + data.slice(1);
        
        // Update card data attribute
        card.setAttribute('data-status', data);
        
        // Disable buttons
        buttons.forEach(btn => btn.disabled = true);
        
        // Update stats
        updateStats();
      })
      .catch(error => console.error('Error:', error));
    }
    
    // Update stats after action
    function updateStats() {
      const pending = document.querySelectorAll('[data-status="pending"]').length;
      const approved = document.querySelectorAll('[data-status="approved"]').length;
      const rejected = document.querySelectorAll('[data-status="rejected"]').length;
      
      document.querySelectorAll('.stat-number')[0].textContent = pending;
      document.querySelectorAll('.stat-number')[1].textContent = approved;
      document.querySelectorAll('.stat-number')[2].textContent = rejected;
      
      document.querySelector('[data-filter="pending"] .badge-count').textContent = pending;
    }
    
    // Filter functionality
    document.querySelectorAll('.badge-filter').forEach(badge => {
      badge.addEventListener('click', function(){
        document.querySelectorAll('.badge-filter').forEach(b => {
          b.classList.remove('badge-filter');
          b.classList.add('badge-filter', 'secondary');
        });
        this.classList.remove('secondary');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.request-card').forEach(card => {
          if(filter === 'all') {
            card.classList.remove('hidden');
          } else {
            if(card.dataset.status === filter) {
              card.classList.remove('hidden');
            } else {
              card.classList.add('hidden');
            }
          }
        });
      });
    });
  </script>
</body>
</html>
