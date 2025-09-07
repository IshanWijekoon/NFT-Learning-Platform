<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get course ID from POST data
$course_id = intval($_POST['course_id']);
$learner_id = $_SESSION['user_id'];

// Validate input
if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

// Check if enrollment exists
$check_enrollment = "SELECT * FROM enrollments WHERE learner_id = '$learner_id' AND course_id = '$course_id'";
$enrollment_result = mysqli_query($conn, $check_enrollment);

if (!$enrollment_result || mysqli_num_rows($enrollment_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
    exit();
}

$enrollment = mysqli_fetch_assoc($enrollment_result);

// Check if already completed
if ($enrollment['completed']) {
    echo json_encode(['success' => true, 'message' => 'Course already completed']);
    exit();
}

// Mark course as completed
$update_query = "UPDATE enrollments 
                SET completed = 1, 
                    completed_at = NOW(), 
                    progress = 100.00 
                WHERE learner_id = '$learner_id' AND course_id = '$course_id'";

if (mysqli_query($conn, $update_query)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Course marked as completed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}
?>
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$course_id = intval($input['course_id']);
$learner_id = intval($input['learner_id']);

// Validate input
if (!$course_id || !$learner_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course or learner ID']);
    exit();
}

// Verify the learner matches the session
if ($learner_id !== $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

