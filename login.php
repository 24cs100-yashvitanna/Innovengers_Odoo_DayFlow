<?php 
include('../db.php');

// Handle login before any HTML output
if(isset($_POST['login'])){
  $role = mysqli_real_escape_string($conn, $_POST['role']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $pass = $_POST['password'];
  
  if(empty($role) || empty($username) || empty($pass)) {
    $error = "All fields are required!";
  } else {
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND role='$role'");
    
    if(!$q){
      $error = "Database error: " . mysqli_error($conn);
    } else {
      $u = mysqli_fetch_assoc($q);
      
      if($u){
        // Verify password
        if(password_verify($pass, $u['password'])){
          $_SESSION['uid'] = $u['id'];
          $_SESSION['user_id'] = $u['id'];
          $_SESSION['role'] = $u['role'];
          $_SESSION['username'] = $u['username'];
          
          // Redirect based on role
          if($u['role'] === 'admin'){
            header("Location: ../admin/dashboard.php");
            exit();
          } else if($u['role'] === 'employee'){
            header("Location: ../employee/dashboard.php");
            exit();
          }
        } else {
          $error = "Invalid password!";
        }
      } else {
        $error = "User not found with selected role!";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .login-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
      padding: 40px;
    }
    
    .logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo h1 {
      color: #667eea;
      font-size: 28px;
      margin-bottom: 5px;
    }
    
    .logo p {
      color: #999;
      font-size: 14px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 600;
      font-size: 14px;
    }
    
    select, input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 5px;
      font-size: 14px;
      transition: border-color 0.3s;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    select:focus, input:focus {
      outline: none;
      border-color: #667eea;
    }
    
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 25px;
    }
    
    button {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-login {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-register {
      background: #f0f0f0;
      color: #667eea;
    }
    
    .btn-register:hover {
      background: #e0e0e0;
    }
    
    .error {
      background: #fee;
      color: #c00;
      padding: 12px;
      border-radius: 5px;
      margin-bottom: 20px;
      border-left: 4px solid #c00;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo">
      <h1>🏢 StaffSphere</h1>
      <p>Employee Management System</p>
    </div>
    
    <?php if(isset($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="role">Login As</label>
        <select name="role" id="role" required>
          <option value="">-- Select Role --</option>
          <option value="admin">Admin</option>
          <option value="employee">Employee</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Enter your username" required>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>
      </div>
      
      <div class="button-group">
        <button type="submit" name="login" class="btn-login">Login</button>
        <button type="button" class="btn-register" onclick="location.href='register.php'">Register</button>
      </div>
    </form>
  </div>
</body>
</html>
