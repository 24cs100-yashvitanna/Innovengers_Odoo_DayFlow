<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=".$_SESSION['user_id']));

// Update profile
if(isset($_POST['update'])){
  $username = $_POST['username'];
  $email = $_POST['email'];
  
  mysqli_query($conn, "UPDATE users SET username='$username', email='$email' WHERE id=".$_SESSION['user_id']);
  $_SESSION['username'] = $username;
  $success = "✓ Profile updated!";
}
?>

<?php
include('../db.php');
if($_SESSION['role'] !== 'employee'){ 
  header("Location: ../auth/login.php");
  exit; 
}

$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=".$_SESSION['user_id']));

// Update profile
if(isset($_POST['update'])){
  $username = $_POST['username'];
  $email = $_POST['email'];
  
  mysqli_query($conn, "UPDATE users SET username='$username', email='$email' WHERE id=".$_SESSION['user_id']);
  $_SESSION['username'] = $username;
  $success = "✓ Profile updated!";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - My Profile</title>
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
    .form-card { background: #252d48; border: 1px solid #404966; border-radius: 12px; padding: 25px; margin-bottom: 30px; max-width: 500px; }
    .form-card h3 { font-size: 18px; color: #ffffff; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #404966; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 8px; color: #aaa; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    input { width: 100%; padding: 12px; background: #1a1f35; border: 1px solid #404966; border-radius: 5px; color: #e0e0e0; font-size: 14px; transition: all 0.3s; }
    input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 10px rgba(102, 126, 234, 0.2); }
    input:read-only { background: #1a1a2e; cursor: not-allowed; opacity: 0.7; border-color: #333; }
    .submit-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-weight: 600; margin-top: 10px; transition: all 0.3s; }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
    .bottom-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #252d48; border-top: 1px solid #404966; padding: 10px 0; z-index: 1000; }
    @media (max-width: 768px) { body { padding-bottom: 80px; } .bottom-nav { display: flex; justify-content: space-around; } }
  </style>
</head>
<body>
  <div class="header-top">
    <div>
      <h1>👤 My Profile</h1>
      <p>Edit your profile information</p>
    </div>
    <a href="../auth/logout.php" class="logout-btn">✕ Logout</a>
  </div>

  <?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>

  <div class="form-card">
    <h3>✏️ Edit Profile Information</h3>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo $u['username']; ?>" required>
      </div>
      <div class="form-group">
        <label>Employee ID</label>
        <input type="text" value="<?php echo $u['employee_id']; ?>" readonly>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo $u['email']; ?>" required>
      </div>
      <button type="submit" name="update" class="submit-btn">💾 Update Profile</button>
    </form>
  </div>

  <div class="bottom-nav">
    <a href="dashboard.php" class="nav-item">📊</a>
    <a href="attendance.php" class="nav-item">📋</a>
    <a href="leave.php" class="nav-item">📝</a>
    <a href="payroll.php" class="nav-item">💰</a>
    <a href="profile.php" class="nav-item active">👤</a>
  </div>
</body>
</html>
