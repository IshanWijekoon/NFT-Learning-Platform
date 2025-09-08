<?php
include 'db.php';

echo "<h2>Database Tables List</h2>\n";

$query = "SHOW TABLES";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<ul>\n";
    while ($row = mysqli_fetch_array($result)) {
        echo "<li>" . $row[0] . "</li>\n";
    }
    echo "</ul>\n";
    
    // Show learners table structure
    echo "<h3>Learners Table Structure:</h3>\n";
    $learners_query = "DESCRIBE learners";
    $learners_result = mysqli_query($conn, $learners_query);
    
    if ($learners_result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        while ($row = mysqli_fetch_assoc($learners_result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // Show sample learners
        echo "<h3>Sample Learners:</h3>\n";
        $sample_query = "SELECT id, full_name, email FROM learners LIMIT 5";
        $sample_result = mysqli_query($conn, $sample_query);
        
        if ($sample_result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr><th>ID</th><th>Full Name</th><th>Email</th></tr>\n";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
    }
    
    // Show enrollments table
    echo "<h3>Enrollments Table Structure:</h3>\n";
    $enrollments_query = "DESCRIBE enrollments";
    $enrollments_result = mysqli_query($conn, $enrollments_query);
    
    if ($enrollments_result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        while ($row = mysqli_fetch_assoc($enrollments_result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // Show sample enrollments
        echo "<h3>Sample Enrollments:</h3>\n";
        $sample_query = "SELECT id, learner_id, course_id, completed, enrolled_at FROM enrollments LIMIT 5";
        $sample_result = mysqli_query($conn, $sample_query);
        
        if ($sample_result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr><th>ID</th><th>Learner ID</th><th>Course ID</th><th>Completed</th><th>Enrolled At</th></tr>\n";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['learner_id'] . "</td>";
                echo "<td>" . $row['course_id'] . "</td>";
                echo "<td>" . ($row['completed'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $row['enrolled_at'] . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
    }
    
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
