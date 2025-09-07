<?php
include 'db.php';

echo "<h2>Creator Profile Pictures Test</h2>";

// Check creators table
$creators_query = mysqli_query($conn, "SELECT id, full_name, profile_picture FROM creators");

if (mysqli_num_rows($creators_query) > 0) {
    echo "<h3>Creators and their profile pictures:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Profile Picture Path</th><th>File Exists</th><th>Preview</th></tr>";
    
    while ($creator = mysqli_fetch_assoc($creators_query)) {
        $file_exists = !empty($creator['profile_picture']) && file_exists($creator['profile_picture']);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($creator['id']) . "</td>";
        echo "<td>" . htmlspecialchars($creator['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($creator['profile_picture'] ?? 'NULL') . "</td>";
        echo "<td>" . ($file_exists ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>";
        if ($file_exists) {
            echo "<img src='" . htmlspecialchars($creator['profile_picture']) . "' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover;'>";
        } else {
            echo "<div style='width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;'>";
            echo strtoupper(substr($creator['full_name'], 0, 1));
            echo "</div>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No creators found</p>";
}

// Check courses with creator info
echo "<h3>Courses and their creators:</h3>";
$courses_query = mysqli_query($conn, "
    SELECT 
        c.id, 
        c.course_name, 
        cr.full_name as creator_name, 
        cr.profile_picture 
    FROM courses c 
    LEFT JOIN creators cr ON c.creator_id = cr.id 
    LIMIT 10
");

if (mysqli_num_rows($courses_query) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Course ID</th><th>Course Name</th><th>Creator</th><th>Profile Picture</th></tr>";
    
    while ($course = mysqli_fetch_assoc($courses_query)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($course['id']) . "</td>";
        echo "<td>" . htmlspecialchars($course['course_name']) . "</td>";
        echo "<td>" . htmlspecialchars($course['creator_name'] ?? 'Unknown') . "</td>";
        echo "<td>";
        if (!empty($course['profile_picture']) && file_exists($course['profile_picture'])) {
            echo "<img src='" . htmlspecialchars($course['profile_picture']) . "' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>";
        } else {
            echo "No picture";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
