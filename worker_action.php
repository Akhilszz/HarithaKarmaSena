<?php
require 'config.php';

// Check if user is logged in and is a worker
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') {
    header('Location: login.php');
    exit;
}

// Get and validate action and ID
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

// Validate inputs
if(empty($action) || $id <= 0) {
    $_SESSION['error'] = "Invalid request parameters.";
    header('Location: worker_dashboard.php');
    exit;
}

// Allowed actions
$allowed_actions = ['accept', 'collect', 'cancel'];

if(!in_array($action, $allowed_actions)) {
    $_SESSION['error'] = "Invalid action specified.";
    header('Location: worker_dashboard.php');
    exit;
}

try {
    // Check if the collection request exists and belongs to a valid user
    $check_stmt = $mysqli->prepare("
        SELECT cr.id 
        FROM collection_requests cr 
        JOIN users u ON cr.user_id = u.id 
        WHERE cr.id = ? AND cr.status != 'collected'
    ");
    
    if(!$check_stmt) {
        throw new Exception("Database error: " . $mysqli->error);
    }
    
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows === 0) {
        $_SESSION['error'] = "Collection request not found or already completed.";
        header('Location: worker_dashboard.php');
        exit;
    }
    
    $check_stmt->close();

    // Prepare the update statement based on action
    $status_map = [
        'accept' => 'accepted',
        'collect' => 'collected', 
        'cancel' => 'cancelled'
    ];
    
    $new_status = $status_map[$action];
    
    $update_stmt = $mysqli->prepare("UPDATE collection_requests SET status = ? WHERE id = ?");
    
    if(!$update_stmt) {
        throw new Exception("Database error: " . $mysqli->error);
    }
    
    $update_stmt->bind_param("si", $new_status, $id);
    
    if($update_stmt->execute()) {
        if($update_stmt->affected_rows > 0) {
            $_SESSION['success'] = "Request #{$id} has been {$new_status} successfully.";
            
            // Log the action
            $user_id = $_SESSION['user']['id'];
            $log_stmt = $mysqli->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
            if($log_stmt) {
                $description = "Worker {$action}ed collection request #{$id}";
                $log_stmt->bind_param("iss", $user_id, $action, $description);
                $log_stmt->execute();
                $log_stmt->close();
            }
        } else {
            $_SESSION['error'] = "No changes made. The request may have been already updated.";
        }
    } else {
        throw new Exception("Failed to update request: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    error_log("Worker action error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing your request. Please try again.";
}

// Redirect back to dashboard
header('Location: worker_dashboard.php');
exit;