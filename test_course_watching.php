<?php
include 'db.php';

echo "Testing course-watching page functionality...\n\n";

// Test 1: Check if course 4 (with video) exists
$course_query = "SELECT c.*, cr.full_name as creator_name 
                 FROM courses c 
                 JOIN creators cr ON c.creator_id = cr.id 
                 WHERE c.id = 4";
$course_result = mysqli_query($conn, $course_query);

if ($course_result && mysqli_num_rows($course_result) > 0) {
    $course = mysqli_fetch_assoc($course_result);
    echo "✓ Course found: " . $course['course_name'] . "\n";
    echo "✓ Creator: " . $course['creator_name'] . "\n";
    echo "✓ Video path: " . ($course['video_path'] ?? 'NULL') . "\n";
    
    if (!empty($course['video_path'])) {
        $video_exists = file_exists($course['video_path']);
        echo "✓ Video file exists: " . ($video_exists ? 'Yes' : 'No') . "\n";
        if ($video_exists) {
            echo "✓ Video file size: " . number_format(filesize($course['video_path']) / 1024 / 1024, 2) . " MB\n";
        }
    }
} else {
    echo "✗ Course 4 not found\n";
}

echo "\n";

// Test 2: Check enrollment functionality
echo "Testing enrollment for learner 6 in course 4...\n";
$enrollment_query = "SELECT * FROM enrollments WHERE learner_id = 6 AND course_id = 4";
$enrollment_result = mysqli_query($conn, $enrollment_query);

if (mysqli_num_rows($enrollment_result) > 0) {
    $enrollment = mysqli_fetch_assoc($enrollment_result);
    echo "✓ Enrollment exists\n";
    echo "✓ Progress: " . $enrollment['progress'] . "%\n";
    echo "✓ Completed: " . ($enrollment['completed'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ No enrollment found\n";
    echo "Creating test enrollment...\n";
    $enroll_query = "INSERT INTO enrollments (learner_id, course_id, enrolled_at) VALUES (6, 4, NOW())";
    if (mysqli_query($conn, $enroll_query)) {
        echo "✓ Test enrollment created\n";
    } else {
        echo "✗ Failed to create enrollment: " . mysqli_error($conn) . "\n";
    }
}

echo "\nTest complete. You can now test: http://localhost/NFT-Learning-Platform-/course-watching.php?course_id=4\n";
?>
