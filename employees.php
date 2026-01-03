<?php
include('../db.php');
if($_SESSION['role'] !== 'admin'){
  header("Location: ../auth/login.php");
  exit;
}

// Add new employee
if(isset($_POST['add_employee'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  
  $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
  
  if(mysqli_num_rows($check) > 0){
    $error = "Username already exists!";
  } else {
    mysqli_query($conn, "INSERT INTO users (username, employee_id, email, password, role) VALUES ('$username', '$employee_id', '$email', '$password', 'employee')");
    $success = "Employee added successfully!";
  }
}

// Delete employee
if(isset($_GET['delete'])){
  mysqli_query($conn, "DELETE FROM users WHERE id=".$_GET['delete']);
  header("Location: employees.php");
}

$query = mysqli_query($conn, "SELECT * FROM users WHERE role='employee'");
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Employees</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f8f9fa; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
    .container { display: flex; min-height: 100vh; }
    
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
    
    /* Main Content */
    .main { margin-left: 250px; flex: 1; padding: 30px 40px; }
    
    /* Header */
    .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header-content h1 { font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 5px; }
    .header-content p { color: #6b7280; font-size: 15px; }
    .header-actions { display: flex; gap: 10px; }
    .export-btn { background: white; color: #333; border: 1px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
    .export-btn:hover { background: #f9fafb; }
    .add-btn { background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
    .add-btn:hover { background: #4f46e5; transform: translateY(-2px); }
    
    /* Search & Filter */
    .search-filter { display: flex; gap: 15px; margin-bottom: 30px; align-items: center; flex-wrap: wrap; }
    .search-box { flex: 1; min-width: 300px; background: white; padding: 12px 18px; border: 1px solid #e5e7eb; border-radius: 10px; display: flex; align-items: center; gap: 10px; }
    .search-box input { flex: 1; border: none; outline: none; font-size: 14px; color: #333; }
    .search-box input::placeholder { color: #9ca3af; }
    .filter-badges { display: flex; gap: 10px; flex-wrap: wrap; }
    .badge { padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; font-size: 14px; font-weight: 600; }
    .badge.secondary { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .badge:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    
    /* Employee Cards Grid */
    .employees-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
    .employee-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); transition: all 0.3s; position: relative; }
    .employee-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); transform: translateY(-2px); }
    
    .employee-header { display: flex; gap: 15px; align-items: flex-start; margin-bottom: 16px; }
    .avatar { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px; flex-shrink: 0; }
    .employee-info { flex: 1; }
    .employee-name { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
    .employee-title { font-size: 13px; color: #6b7280; }
    .menu-btn { background: none; border: none; color: #9ca3af; font-size: 20px; cursor: pointer; padding: 5px; position: absolute; top: 20px; right: 20px; }
    .menu-btn:hover { color: #1f2937; }
    
    .employee-contact { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
    .contact-item { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 8px; }
    
    .employee-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #f3f4f6; }
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-leave { background: #fed7aa; color: #9a3412; }
    
    .view-profile { color: #6366f1; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .view-profile:hover { color: #4f46e5; }
    
    /* Modal */
    .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal.show { display: flex; }
    .modal-content { background: white; border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .modal-title { font-size: 22px; font-weight: 700; color: #1f2937; }
    .close-btn { background: none; border: none; font-size: 28px; color: #9ca3af; cursor: pointer; padding: 0; }
    .close-btn:hover { color: #1f2937; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 14px; }
    .form-group input { width: 100%; padding: 12px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; color: #1f2937; font-size: 14px; transition: all 0.3s; }
    .form-group input:focus { outline: none; border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    .submit-btn { background: #6366f1; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; width: 100%; }
    .submit-btn:hover { background: #4f46e5; transform: translateY(-2px); }
    
    .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; border-left-color: #10b981; }
    .alert-danger { background: #fee2e2; color: #991b1b; border-left-color: #ef4444; }
    
    @media (max-width: 1200px) {
      .employees-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 20px; }
      .employees-grid { grid-template-columns: 1fr; }
      .header-section { flex-direction: column; align-items: flex-start; gap: 15px; }
      .search-filter { flex-direction: column; align-items: stretch; }
      .search-box { min-width: 100%; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <div class="logo-icon">⭐</div>
        <div class="logo-text">StaffSphere</div>
      </div>
      
      <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="employees.php" class="nav-item active">
          <span class="nav-icon">👥</span>
          <span>Employees</span>
        </a>
        <a href="attendance.php" class="nav-item">
          <span class="nav-icon">📋</span>
          <span>Attendance</span>
        </a>
        <a href="leave_requests.php" class="nav-item">
          <span class="nav-icon">📝</span>
          <span>Leave Approvals</span>
        </a>
      </div>
      
      <div class="user-section">
        <div class="user-info">
          <div class="user-avatar">JB</div>
          <div class="user-details">
            <div class="user-name">Admin</div>
            <div class="user-role">HR</div>
          </div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">
          <span class="nav-icon">🚪</span>
          <span>Sign Out</span>
        </a>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="main">
      <!-- Header -->
      <div class="header-section">
        <div class="header-content">
          <h1>Employees</h1>
          <p>Manage your workforce</p>
        </div>
        <div class="header-actions">
          <button class="add-btn" onclick="openModal()">
            <span>➕</span>
            <span>Add Employee</span>
          </button>
        </div>
      </div>
      
      <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <!-- Search & Filter -->
      <div class="search-filter">
        <div class="search-box">
          <span>🔍</span>
          <input type="text" id="searchInput" placeholder="Search employees...">
        </div>
        <div class="filter-badges">
          <button class="badge" data-filter="all">All</button>
          <button class="badge secondary" data-filter="Engineering">Engineering</button>
          <button class="badge secondary" data-filter="Design">Design</button>
          <button class="badge secondary" data-filter="Marketing">Marketing</button>
          <button class="badge secondary" data-filter="HR">HR</button>
          <button class="badge secondary" data-filter="Sales">Sales</button>
        </div>
      </div>
      
      <!-- Employees Grid -->
      <div class="employees-grid">
        <?php
        mysqli_data_seek($query, 0); // Reset query pointer
        while($emp = mysqli_fetch_assoc($query)){
          $initials = strtoupper(substr($emp['username'], 0, 1)) . strtoupper(substr($emp['username'], -1, 1));
          
          // Assign random departments for demo
          $departments = ['Engineering', 'Design', 'Marketing', 'HR', 'Sales'];
          $dept = $departments[array_rand($departments)];
          
          $titles = ['Senior Developer', 'UI/UX Designer', 'Marketing Manager', 'HR Specialist', 'Sales Executive', 'Backend Developer', 'Junior Developer', 'DevOps Engineer'];
          $title = $titles[array_rand($titles)];
          
          $statuses = ['Active', 'Active', 'Active', 'On Leave']; // 75% active
          $status = $statuses[array_rand($statuses)];
          $statusClass = $status == 'Active' ? 'status-active' : 'status-leave';
          
          echo "
          <div class='employee-card' data-department='$dept'>
            <button class='menu-btn' onclick='deleteEmployee({$emp['id']})'>⋮</button>
            <div class='employee-header'>
              <div class='avatar'>$initials</div>
              <div class='employee-info'>
                <div class='employee-name'>{$emp['username']}</div>
                <div class='employee-title'>$title</div>
              </div>
            </div>
            
            <div class='employee-contact'>
              <div class='contact-item'>
                <span>✉️</span>
                <span>{$emp['email']}</span>
              </div>
              <div class='contact-item'>
                <span>🏢</span>
                <span>$dept</span>
              </div>
            </div>
            
            <div class='employee-footer'>
              <span class='status-badge $statusClass'>$status</span>
              <a href='#' class='view-profile'>View Profile</a>
            </div>
          </div>";
        }
        ?>
      </div>
    </div>
  </div>
  
  <!-- Add Employee Modal -->
  <div class="modal" id="addEmployeeModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Add New Employee</h2>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      
      <form method="POST" autocomplete="off" id="addEmployeeForm">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" autocomplete="new-password" value="" required>
        </div>
        
        <div class="form-group">
          <label>Employee ID</label>
          <input type="text" name="employee_id" autocomplete="new-password" value="" required>
        </div>
        
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" autocomplete="new-password" value="" required>
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" autocomplete="new-password" value="" required>
        </div>
        
        <button type="submit" name="add_employee" class="submit-btn">Add Employee</button>
      </form>
    </div>
  </div>
  
  <script>
    function openModal() {
      const modal = document.getElementById('addEmployeeModal');
      modal.classList.add('show');
      // Clear all form fields
      document.getElementById('addEmployeeForm').reset();
      setTimeout(() => {
        document.querySelectorAll('#addEmployeeForm input').forEach(input => {
          input.value = '';
        });
      }, 10);
    }
    
    function closeModal() {
      document.getElementById('addEmployeeModal').classList.remove('show');
    }
    
    function deleteEmployee(id) {
      if(confirm('Are you sure you want to delete this employee?')) {
        window.location.href = '?delete=' + id;
      }
    }
    
    // Close modal on outside click
    document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
      if(e.target === this) {
        closeModal();
      }
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e){
      const search = e.target.value.toLowerCase();
      document.querySelectorAll('.employee-card').forEach(card => {
        const name = card.querySelector('.employee-name').textContent.toLowerCase();
        card.style.display = name.includes(search) ? '' : 'none';
      });
    });
    
    // Filter functionality
    document.querySelectorAll('.filter-badges .badge').forEach(badge => {
      badge.addEventListener('click', function(){
        document.querySelectorAll('.filter-badges .badge').forEach(b => {
          b.classList.remove('badge');
          b.classList.add('badge', 'secondary');
        });
        this.classList.remove('secondary');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.employee-card').forEach(card => {
          if(filter === 'all') {
            card.style.display = '';
          } else {
            const dept = card.dataset.department;
            card.style.display = dept === filter ? '' : 'none';
          }
        });
      });
    });
  </script>
</body>
</html>

