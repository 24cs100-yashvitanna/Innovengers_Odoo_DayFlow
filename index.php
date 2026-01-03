<?php
// StaffSphere - Employee Management System
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login
    header('Location: auth/login.php');
    exit();
} else {
    // Check if user is admin or employee
    if($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit();
}
?>
