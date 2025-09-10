<?php
include 'db.php';

echo "<h2>Certificate Path Test</h2>";

// Check if nft_certificate_image column exists
$check_column = "SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✓ nft_certificate_image column exists in courses table</p>";
    
    // Check courses with certificate paths
    $cert_query = "SELECT id, course_name, nft_certificate_image FROM courses WHERE nft_certificate_image IS NOT NULL AND nft_certificate_image != ''";
    $cert_result = mysqli_query($conn, $cert_query);
    
    if (mysqli_num_rows($cert_result) > 0) {
        echo "<h3>Courses with Certificate Templates:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Course ID</th><th>Course Name</th><th>Certificate Path</th><th>File Exists</th></tr>";
        
        while ($course = mysqli_fetch_assoc($cert_result)) {
            $fileExists = file_exists($course['nft_certificate_image']) ? "✓ Yes" : "✗ No";
            $fileColor = file_exists($course['nft_certificate_image']) ? "green" : "red";
            
            echo "<tr>";
            echo "<td>{$course['id']}</td>";
            echo "<td>" . htmlspecialchars($course['course_name']) . "</td>";
            echo "<td>" . htmlspecialchars($course['nft_certificate_image']) . "</td>";
            echo "<td style='color: $fileColor;'>$fileExists</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No courses have certificate templates uploaded yet</p>";
    }
    
    // Show recent courses without certificates
    echo "<h3>Recent Courses Without Certificates:</h3>";
    $no_cert_query = "SELECT id, course_name, created_at FROM courses WHERE nft_certificate_image IS NULL OR nft_certificate_image = '' ORDER BY id DESC LIMIT 5";
    $no_cert_result = mysqli_query($conn, $no_cert_query);
    
    if (mysqli_num_rows($no_cert_result) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Course ID</th><th>Course Name</th><th>Created</th></tr>";
        
        while ($course = mysqli_fetch_assoc($no_cert_result)) {
            echo "<tr>";
            echo "<td>{$course['id']}</td>";
            echo "<td>" . htmlspecialchars($course['course_name']) . "</td>";
            echo "<td>{$course['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "<p style='color: red;'>✗ nft_certificate_image column does NOT exist in courses table</p>";
    echo "<p>Run <a href='setup_nft_tables.php' style='color: blue;'>setup_nft_tables.php</a> first!</p>";
}

echo "<br><p><a href='setup_nft_tables.php' style='color: blue;'>Setup NFT Tables</a> | 
         <a href='course-management.php' style='color: blue;'>Course Management</a> | 
         <a href='award_certificate_manual.php' style='color: blue;'>Manual Certificate Award</a></p>";
?>
