<?php
include 'db.php';

echo "Checking courses table structure:\n";
$result = mysqli_query($conn, "DESCRIBE courses");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nSample course data:\n";
$courses = mysqli_query($conn, "SELECT id, course_name, video_path, thumbnail FROM courses LIMIT 3");
while($course = mysqli_fetch_assoc($courses)) {
    echo "ID: " . $course['id'] . ", Name: " . $course['course_name'] . ", Video: " . ($course['video_path'] ?? 'NULL') . ", Thumbnail: " . ($course['thumbnail'] ?? 'NULL') . "\n";
}

echo "\nChecking uploads directory:\n";
$video_dir = "uploads/course_videos/";
if (is_dir($video_dir)) {
    $files = scandir($video_dir);
    foreach($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "Video file: " . $file . "\n";
        }
    }
} else {
    echo "Video directory does not exist\n";
}
?>
