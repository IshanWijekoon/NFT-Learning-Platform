<?php
include 'db.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    // Check if courses table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'courses'");
    if (mysqli_num_rows($table_check) > 0) {
        echo "<p style='color: green;'>✓ Courses table exists</p>";
        
        // Count total courses
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses");
        $count_result = mysqli_fetch_assoc($count_query);
        echo "<p><strong>Total courses in database:</strong> " . $count_result['total'] . "</p>";
        
        // Show all courses with their data
        $courses_query = mysqli_query($conn, "SELECT * FROM courses LIMIT 5");
        if (mysqli_num_rows($courses_query) > 0) {
            echo "<h3>Sample Courses Data:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Course Name</th><th>Category</th><th>Price</th><th>Status</th><th>Creator ID</th></tr>";
            
            while ($row = mysqli_fetch_assoc($courses_query)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['creator_id']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠ No courses found in database</p>";
        }
        
        // Show table structure
        echo "<h3>Courses Table Structure:</h3>";
        $structure_query = mysqli_query($conn, "DESCRIBE courses");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        while ($row = mysqli_fetch_assoc($structure_query)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>✗ Courses table does not exist</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}
?>
