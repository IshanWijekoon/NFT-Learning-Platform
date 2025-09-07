<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$creator_id = $_SESSION['user_id'];

// First check if courses table exists and what columns it has
$check_table = "SHOW TABLES LIKE 'courses'";
$table_result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Courses table does not exist. Please run the database schema first.']);
    exit();
}

// Check what columns exist in the courses table
$check_columns = "SHOW COLUMNS FROM courses";
$columns_result = mysqli_query($conn, $check_columns);
$columns = [];
while ($col = mysqli_fetch_assoc($columns_result)) {
    $columns[] = $col['Field'];
}

// Build query based on available columns
$select_fields = [];
$field_mappings = [
    'id' => 'id',
    'course_id' => 'id', // fallback if using course_id instead of id
    'course_name' => 'title', // your database uses course_name
    'title' => 'title', // fallback if using title instead of course_name
    'description' => 'description',
    'category' => 'category',
    'price' => 'price',
    'duration' => 'duration',
    'duration_hours' => 'duration', // fallback if using duration_hours
    'students_enrolled' => 'students_enrolled',
    'rating' => 'rating',
    'total_reviews' => 'total_reviews',
    'status' => 'status',
    'created_at' => 'created_at',
    'updated_at' => 'updated_at',
    'video_path' => 'video_path',
    'thumbnail' => 'thumbnail',
    'instructor' => 'instructor'
];

// Add all available columns from the mappings
foreach ($field_mappings as $db_field => $result_field) {
    if (in_array($db_field, $columns)) {
        $select_fields[] = "$db_field";
    }
}

if (empty($select_fields)) {
    echo json_encode(['success' => false, 'message' => 'No compatible columns found in courses table. Available columns: ' . implode(', ', $columns)]);
    exit();
}

// Get courses for this creator
$sql = "SELECT " . implode(', ', $select_fields) . "
        FROM courses 
        WHERE creator_id = '$creator_id' 
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

if ($result) {
    $courses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Debug: log the raw row data
        error_log("Raw course data: " . print_r($row, true));
        
        // Ensure all expected fields exist with default values
        $course = [
            'id' => $row['id'] ?? 0,
            'title' => $row['course_name'] ?? $row['title'] ?? 'Untitled Course', // course_name first
            'description' => $row['description'] ?? 'No description available',
            'category' => $row['category'] ?? 'General',
            'price' => $row['price'] ?? 0,
            'duration' => $row['duration'] ?? $row['duration_hours'] ?? 0,
            'students_enrolled' => $row['students_enrolled'] ?? 0,
            'rating' => $row['rating'] ?? 0,
            'total_reviews' => $row['total_reviews'] ?? 0,
            'status' => $row['status'] ?? 'published',
            'video_path' => $row['video_path'] ?? '',
            'thumbnail' => $row['thumbnail'] ?? '',
            'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
            'instructor' => $row['instructor'] ?? 'Unknown Instructor'
        ];
        
        // Debug: log the processed course data
        error_log("Processed course data: " . print_r($course, true));
        
        $courses[] = $course;
    }
    
    echo json_encode(['success' => true, 'courses' => $courses, 'debug' => [
        'total_courses' => count($courses), 
        'creator_id' => $creator_id, 
        'available_columns' => $columns,
        'selected_fields' => $select_fields,
        'sql_query' => $sql,
        'raw_data' => $courses
    ]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'query' => $sql]);
}
?>