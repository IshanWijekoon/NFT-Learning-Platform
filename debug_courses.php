<?php
session_start();
include 'db.php';

echo "<h1>Course Management Debug Tool</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo "<p style='color: red;'>❌ You are not logged in as a creator. Please log in first.</p>";
    echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
    exit();
}

echo "<p style='color: green;'>✅ Logged in as creator. User ID: " . $_SESSION['user_id'] . "</p>";

// Check database connection
if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Check if courses table exists
$check_table = "SHOW TABLES LIKE 'courses'";
$table_result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_result) == 0) {
    echo "<p style='color: red;'>❌ Courses table does not exist</p>";
    echo "<h3>Available tables:</h3>";
    $tables_query = "SHOW TABLES";
    $tables_result = mysqli_query($conn, $tables_query);
    while ($table = mysqli_fetch_array($tables_result)) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "<p style='color: orange;'>⚠️ Please run the SQL schema from course_management_schema.sql</p>";
} else {
    echo "<p style='color: green;'>✅ Courses table exists</p>";
    
    // Show table structure
    echo "<h3>Courses table structure:</h3>";
    $columns_query = "SHOW COLUMNS FROM courses";
    $columns_result = mysqli_query($conn, $columns_query);
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($col = mysqli_fetch_assoc($columns_result)) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check if there are any courses for this creator
    $creator_id = $_SESSION['user_id'];
    $count_query = "SELECT COUNT(*) as total FROM courses WHERE creator_id = '$creator_id'";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    
    echo "<h3>Your courses:</h3>";
    echo "<p>Total courses for creator ID $creator_id: " . $count_row['total'] . "</p>";
    
    if ($count_row['total'] > 0) {
        $courses_query = "SELECT * FROM courses WHERE creator_id = '$creator_id'";
        $courses_result = mysqli_query($conn, $courses_query);
        echo "<table border='1'><tr>";
        
        // Get column names
        $fields = mysqli_fetch_fields($courses_result);
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        // Show data
        while ($course = mysqli_fetch_assoc($courses_result)) {
            echo "<tr>";
            foreach ($course as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Check creators table
echo "<h3>Creators table check:</h3>";
$check_creators = "SHOW TABLES LIKE 'creators'";
$creators_result = mysqli_query($conn, $check_creators);

if (mysqli_num_rows($creators_result) == 0) {
    echo "<p style='color: red;'>❌ Creators table does not exist</p>";
} else {
    echo "<p style='color: green;'>✅ Creators table exists</p>";
    
    // Check if current user exists in creators table
    $creator_check = "SELECT * FROM creators WHERE id = '{$_SESSION['user_id']}'";
    $creator_result = mysqli_query($conn, $creator_check);
    
    if (mysqli_num_rows($creator_result) > 0) {
        $creator_data = mysqli_fetch_assoc($creator_result);
        echo "<p style='color: green;'>✅ Creator profile found: " . htmlspecialchars($creator_data['full_name']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Creator profile not found for user ID: " . $_SESSION['user_id'] . "</p>";
    }
}

echo "<h3>Quick Actions:</h3>";
echo "<a href='course-management.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Go to Course Management</a>";
echo "<br><br>";
echo "<a href='get_courses.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;' target='_blank'>Test get_courses.php API</a>";
?>
