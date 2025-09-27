<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Allow both authenticated and non-authenticated users for Web3 enrollments
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$course_id = intval($input['course_id'] ?? 0);
$wallet_address = $input['wallet_address'] ?? '';
$chain_id = $input['chain_id'] ?? '';
$signature = $input['signature'] ?? '';
$enrollment_type = $input['enrollment_type'] ?? 'web3';

// Basic validation
if (!$course_id || !$wallet_address || !$signature) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

// Validate wallet address format
if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $wallet_address)) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallet address format']);
    exit();
}

try {
    // Check if course exists and is published
    $course_check = "SELECT id, course_name, creator_id, students_enrolled, price FROM courses WHERE id = ? AND status = 'published'";
    $stmt = mysqli_prepare($conn, $course_check);
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $course_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($course_result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or not available']);
        exit();
    }
    
    $course = mysqli_fetch_assoc($course_result);
    
    // Check if wallet is already enrolled
    $enrollment_check = "SELECT id FROM web3_enrollments WHERE course_id = ? AND wallet_address = ?";
    $stmt = mysqli_prepare($conn, $enrollment_check);
    mysqli_stmt_bind_param($stmt, 'is', $course_id, $wallet_address);
    mysqli_stmt_execute($stmt);
    $enrollment_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($enrollment_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'This wallet is already enrolled in this course']);
        exit();
    }
    
    // Verify signature (basic verification)
    // In a production environment, you'd want more robust signature verification
    $message = "Enroll in course {$course_id} at " . date('c', $input['timestamp']);
    
    // For now, we'll trust the signature since we're in development
    // In production, implement proper signature verification using Web3 libraries
    
    // Create Web3 enrollment record
    $insert_enrollment = "INSERT INTO web3_enrollments (course_id, wallet_address, chain_id, signature, enrollment_type, enrollment_date) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $insert_enrollment);
    mysqli_stmt_bind_param($stmt, 'issss', $course_id, $wallet_address, $chain_id, $signature, $enrollment_type);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update course enrollment count
        $update_course = "UPDATE courses SET students_enrolled = students_enrolled + 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_course);
        mysqli_stmt_bind_param($stmt, 'i', $course_id);
        mysqli_stmt_execute($stmt);
        
        // If user is also logged in with traditional account, link the enrollment
        if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'learner') {
            $link_enrollment = "INSERT INTO enrollments (learner_id, course_id, enrollment_date) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE enrollment_date = enrollment_date";
            $stmt = mysqli_prepare($conn, $link_enrollment);
            mysqli_stmt_bind_param($stmt, 'ii', $_SESSION['user_id'], $course_id);
            mysqli_stmt_execute($stmt);
        }
        
        // Log the enrollment
        error_log("Web3 Enrollment successful - Course ID: $course_id, Wallet: $wallet_address");
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully enrolled in '{$course['course_name']}' with Web3 wallet!",
            'enrollment_id' => mysqli_insert_id($conn),
            'course_name' => $course['course_name']
        ]);
    } else {
        throw new Exception('Failed to create enrollment record');
    }
    
} catch (Exception $e) {
    error_log("Web3 Enrollment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Enrollment failed. Please try again.']);
}

mysqli_close($conn);
?>