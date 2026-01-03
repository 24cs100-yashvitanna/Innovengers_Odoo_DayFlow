<?php include('../db.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>StaffSphere - Register</title>
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
      padding: 20px;
    }
    
    .register-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 450px;
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
    
    .btn-register {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-back {
      background: #f0f0f0;
      color: #667eea;
    }
    
    .btn-back:hover {
      background: #e0e0e0;
    }
    
    .success {
      background: #efe;
      color: #060;
      padding: 12px;
      border-radius: 5px;
      margin-bottom: 20px;
      border-left: 4px solid #060;
      font-size: 14px;
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
  <div class="register-container">
    <div class="logo">
      <h1>🏢 StaffSphere</h1>
      <p>Create Your Account</p>
    </div>
    
    <?php if(isset($success)): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="role">Register As</label>
        <select name="role" id="role" required>
          <option value="">-- Select Role --</option>
          <option value="employee">Employee</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Choose a username" required>
      </div>
      
      <div class="form-group">
        <label for="employee_id">Employee ID</label>
        <input type="text" name="employee_id" id="employee_id" placeholder="Enter your employee ID" required>
      </div>
      
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" placeholder="Enter your full name" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Create a password" required>
      </div>
      
      <div class="button-group">
        <button type="submit" name="reg" class="btn-register">Register</button>
        <button type="button" class="btn-back" onclick="location.href='login.php'">Back to Login</button>
      </div>
    </form>
  </div>
</body>
</html>

<?php
if(isset($_POST['reg'])){
  $role = $_POST['role'];
  $username = $_POST['username'];
  $employee_id = $_POST['employee_id'];
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  if(empty($role) || empty($username) || empty($password)) {
    $error = "All fields are required!";
  } else {
    $p = password_hash($password, PASSWORD_DEFAULT);
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    
    if(mysqli_num_rows($check) > 0) {
      $error = "Username already exists!";
    } else {
      $insert = mysqli_query($conn, "INSERT INTO users (username, employee_id, password, role) VALUES ('$username', '$employee_id', '$p', '$role')");
      
      if($insert) {
        $success = "Registration successful! Redirecting to login...";
        echo "<script>setTimeout(function(){ window.location='login.php'; }, 2000);</script>";
      } else {
        $error = "Registration failed. Please try again.";
      }
    }
  }
}
?>
