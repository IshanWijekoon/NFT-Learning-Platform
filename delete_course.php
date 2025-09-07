<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$course_id = intval($input['courseId']);
$creator_id = $_SESSION['user_id'];

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

// Get course details and verify ownership using prepared statement
$stmt = $conn->prepare("SELECT course_id, video_path FROM courses WHERE course_id = ? AND creator_id = ?");
$stmt->bind_param("ii", $course_id, $creator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit();
}

$course = $result->fetch_assoc();
$video_path = $course['video_path'];

// Delete the course from database
$delete_stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ? AND creator_id = ?");
$delete_stmt->bind_param("ii", $course_id, $creator_id);

if ($delete_stmt->execute()) {
    // Delete video file if it exists
    if ($video_path && file_exists($video_path)) {
        unlink($video_path);
    }
    
    // Update creator's total courses count
    $update_stmt = $conn->prepare("UPDATE creators SET total_courses = (SELECT COUNT(*) FROM courses WHERE creator_id = ?) WHERE id = ?");
    $update_stmt->bind_param("ii", $creator_id, $creator_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting course: ' . $conn->error]);
}

$stmt->close();
$delete_stmt->close();
?>