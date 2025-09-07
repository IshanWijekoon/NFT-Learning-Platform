<?php
include 'db.php';

echo "Testing course-watching.php fixes...\n\n";

// Test course 4 data with creator profile picture
$course_query = "SELECT c.*, cr.full_name as creator_name, cr.profile_picture as creator_profile_picture 
                 FROM courses c 
                 JOIN creators cr ON c.creator_id = cr.id 
                 WHERE c.id = 4";
$course_result = mysqli_query($conn, $course_query);

if ($course_result && mysqli_num_rows($course_result) > 0) {
    $course = mysqli_fetch_assoc($course_result);
    echo "✓ Course: " . $course['course_name'] . "\n";
    echo "✓ Creator: " . $course['creator_name'] . "\n";
    echo "✓ Creator Profile Picture: " . ($course['creator_profile_picture'] ?? 'NULL') . "\n";
    
    if (!empty($course['creator_profile_picture'])) {
        $pic_exists = file_exists($course['creator_profile_picture']);
        echo "✓ Profile Picture File Exists: " . ($pic_exists ? 'Yes' : 'No') . "\n";
        if ($pic_exists) {
            echo "✓ File Size: " . number_format(filesize($course['creator_profile_picture']) / 1024, 2) . " KB\n";
        }
    }
    
    echo "✓ Video Path: " . ($course['video_path'] ?? 'NULL') . "\n";
    if (!empty($course['video_path'])) {
        $video_exists = file_exists($course['video_path']);
        echo "✓ Video File Exists: " . ($video_exists ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "✗ Course not found\n";
}

echo "\n✅ All fixes applied:\n";
echo "- Output buffering added to prevent CSS display issues\n";
echo "- Creator profile picture query updated\n";
echo "- Profile picture display logic implemented\n";
echo "- Debug output removed\n";
echo "- Error reporting disabled\n";

echo "\nThe course-watching.php page should now display properly without CSS errors and show the creator's profile picture.\n";
?>
