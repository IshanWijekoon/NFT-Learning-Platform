<?php
session_start();
include 'db.php';

// Simple test to see what's in the courses table
echo "<h1>Course Database Test</h1>";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo "<p style='color: red;'>Please log in as a creator first.</p>";
    exit();
}

$creator_id = $_SESSION['user_id'];
echo "<p>Creator ID: $creator_id</p>";

// Check what columns exist
$columns_query = "SHOW COLUMNS FROM courses";
$columns_result = mysqli_query($conn, $columns_query);
echo "<h2>Courses Table Structure:</h2>";
echo "<table border='1'><tr><th>Column</th><th>Type</th></tr>";
while ($col = mysqli_fetch_assoc($columns_result)) {
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
}
echo "</table>";

// Get all courses for this creator
$query = "SELECT * FROM courses WHERE creator_id = '$creator_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

echo "<h2>Courses for Creator $creator_id:</h2>";
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    $first = true;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($first) {
            echo "<tr>";
            foreach (array_keys($row) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No courses found for this creator.</p>";
}

// Test the get_courses.php API
echo "<h2>Testing get_courses.php API:</h2>";
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/get_courses.php';
echo "<p>API URL: $api_url</p>";
echo "<iframe src='get_courses.php' width='100%' height='300px'></iframe>";
?>
