<?php
include 'db.php';

echo "Fixing video paths for existing courses...\n";

// Get all courses that don't have video_path set but have video files
$courses = mysqli_query($conn, "SELECT id, creator_id FROM courses WHERE video_path IS NULL OR video_path = ''");

$video_dir = "uploads/course_videos/";
$updated_count = 0;

while ($course = mysqli_fetch_assoc($courses)) {
    $course_id = $course['id'];
    $creator_id = $course['creator_id'];
    
    // Look for video files that match this course pattern
    if (is_dir($video_dir)) {
        $files = scandir($video_dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && strpos($file, "course_{$course_id}_") === 0) {
                $video_path = $video_dir . $file;
                
                // Update the database with this video path
                $update_sql = "UPDATE courses SET video_path = '$video_path' WHERE id = $course_id";
                if (mysqli_query($conn, $update_sql)) {
                    echo "Updated course $course_id with video: $file\n";
                    $updated_count++;
                } else {
                    echo "Failed to update course $course_id: " . mysqli_error($conn) . "\n";
                }
                break; // Only take the first matching video file
            }
        }
    }
}

echo "Updated $updated_count courses with video paths.\n";

// Show updated courses
echo "\nCourses with video paths:\n";
$courses_with_videos = mysqli_query($conn, "SELECT id, course_name, video_path FROM courses WHERE video_path IS NOT NULL AND video_path != ''");
while ($course = mysqli_fetch_assoc($courses_with_videos)) {
    echo "ID: {$course['id']}, Name: {$course['course_name']}, Video: {$course['video_path']}\n";
}
?>
