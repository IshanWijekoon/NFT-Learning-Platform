<?php
include 'db.php';

echo "<h2>Enrollments Table Test</h2>";

// Check if enrollments table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'enrollments'");
if (mysqli_num_rows($table_check) > 0) {
    echo "<p style='color: green;'>✓ Enrollments table exists</p>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure_query = mysqli_query($conn, "DESCRIBE enrollments");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_query)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count total enrollments
    $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments");
    $count_result = mysqli_fetch_assoc($count_query);
    echo "<p><strong>Total enrollments:</strong> " . $count_result['total'] . "</p>";
    
    // Show recent enrollments
    $enrollments_query = mysqli_query($conn, "
        SELECT 
            e.id,
            e.learner_id,
            e.course_id,
            e.enrolled_at,
            e.progress,
            e.completed,
            e.completed_at,
            l.full_name as learner_name,
            c.course_name
        FROM enrollments e 
        LEFT JOIN learners l ON e.learner_id = l.id 
        LEFT JOIN courses c ON e.course_id = c.id 
        ORDER BY e.enrolled_at DESC 
        LIMIT 10
    ");
    
    if (mysqli_num_rows($enrollments_query) > 0) {
        echo "<h3>Recent Enrollments:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Learner</th><th>Course</th><th>Enrolled At</th><th>Progress</th><th>Completed</th></tr>";
        
        while ($row = mysqli_fetch_assoc($enrollments_query)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['learner_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['enrolled_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['progress']) . "%</td>";
            echo "<td>" . ($row['completed'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No enrollments found</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Enrollments table does not exist</p>";
    echo "<p>The table will be created automatically when the first enrollment happens.</p>";
}
?>
