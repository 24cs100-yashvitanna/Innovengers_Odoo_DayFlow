<?php
// Temporarily enable errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

header('Content-Type: application/json');
include('../db.php');

// Check if user is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in. Please refresh and try again.']);
    exit;
}

// Check admin authorization
if($_SESSION['role'] !== 'admin') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Admin only.']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

// ========================
// ACTION: Search Employee
// ========================
if($action === 'search_employee') {
    $spoken_name = trim($_POST['name'] ?? '');
    
    if(empty($spoken_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Employee name is required']);
        exit;
    }
    
    // Fetch all employees for fuzzy matching
    $query = "SELECT id, username, employee_id, email FROM users WHERE role='employee' ORDER BY username";
    $result = mysqli_query($conn, $query);
    
    if(!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    $employees = [];
    while($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    // Fuzzy matching function - calculate similarity
    $best_match = null;
    $best_score = 0;
    $threshold = 0.6; // 60% match threshold
    
    $spoken_lower = strtolower($spoken_name);
    
    foreach($employees as $emp) {
        $emp_name_lower = strtolower($emp['username']);
        
        // Calculate similarity using levenshtein distance
        $distance = levenshtein($spoken_lower, $emp_name_lower);
        $max_len = max(strlen($spoken_lower), strlen($emp_name_lower));
        $similarity = 1 - ($distance / $max_len);
        
        // Also check if name contains the spoken text (higher priority)
        if(strpos($emp_name_lower, $spoken_lower) !== false) {
            $similarity = 1.0; // Perfect match if substring found
        }
        
        if($similarity > $best_score) {
            $best_score = $similarity;
            $best_match = $emp;
        }
    }
    
    if($best_match && $best_score >= $threshold) {
        $employee_row_id = staffsphere_get_or_create_employee_id($conn, (int)$best_match['id']);
        if($employee_row_id <= 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to resolve employee record']);
            exit;
        }

        // Fetch current attendance status
        $today = date('Y-m-d');
        $att_query = "SELECT * FROM attendance WHERE employee_id=$employee_row_id AND date='$today' ORDER BY check_in DESC LIMIT 1";
        $att_result = mysqli_query($conn, $att_query);
        $attendance = $att_result ? mysqli_fetch_assoc($att_result) : null;
        
        $emp_details = null;
        $emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE id=$employee_row_id LIMIT 1");
        $emp_details = $emp_result ? mysqli_fetch_assoc($emp_result) : null;
        
        $response = [
            'success' => true,
            'employee' => [
                'id' => $employee_row_id,
                'user_id' => (int)$best_match['id'],
                'name' => $best_match['username'],
                'employee_id' => $best_match['employee_id'],
                'email' => $best_match['email'],
                'department' => $emp_details['department'] ?? 'General'
            ],
            'current_status' => $attendance ? $attendance['status'] : 'Not Checked In',
            'last_checkin' => $attendance['check_in'] ?? null,
            'similarity_score' => round($best_score * 100, 1)
        ];
        
        http_response_code(200);
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }
    exit;
}

// ========================
// ACTION: Log Attendance
// ========================
if($action === 'log_attendance') {
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $status = $_POST['status'] ?? 'Present';
    
    if($employee_id <= 0) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID: ' . $employee_id]);
        exit;
    }
    
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Check if employee exists (employee_id is employees.id)
    $emp_check = mysqli_query($conn, "SELECT e.id, u.username FROM employees e JOIN users u ON e.user_id=u.id WHERE e.id=$employee_id AND u.role='employee' LIMIT 1");
    
    if(!$emp_check) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . mysqli_error($conn)]);
        exit;
    }
    
    if(mysqli_num_rows($emp_check) === 0) {
        ob_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employee ID ' . $employee_id . ' not found']);
        exit;
    }
    
    $employee = mysqli_fetch_assoc($emp_check);
    
    // Check if attendance already exists for today
    $existing = mysqli_query($conn, "SELECT id FROM attendance WHERE employee_id=$employee_id AND date='$today'");
    
    if(!$existing) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        exit;
    }
    
    if(mysqli_num_rows($existing) > 0) {
        // Update existing record
        $update_query = "UPDATE attendance SET status='$status', check_in='$current_time' WHERE employee_id=$employee_id AND date='$today'";
        if(mysqli_query($conn, $update_query)) {
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => $employee['username'] . ' attendance updated to ' . $status,
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => $status
            ]);
        } else {
            $error = mysqli_error($conn);
            ob_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $error]);
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO attendance (employee_id, date, check_in, status) VALUES ($employee_id, '$today', '$current_time', '$status')";
        if(mysqli_query($conn, $insert_query)) {
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => $employee['username'] . ' marked as ' . $status,
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => $status
            ]);
        } else {
            $error = mysqli_error($conn);
            ob_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $error]);
        }
    }
    exit;
}

// Default response
ob_clean();
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
