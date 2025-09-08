<?php
include 'db.php';

echo "<h2>Courses with NFT Certificate Templates</h2>\n";

$query = "SELECT id, course_name, nft_certificate_image FROM courses WHERE nft_certificate_image IS NOT NULL AND nft_certificate_image != ''";
$result = mysqli_query($conn, $query);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Course ID</th><th>Course Name</th><th>NFT Certificate Image</th><th>File Exists</th></tr>\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $file_exists = file_exists($row['nft_certificate_image']) ? "✓ Yes" : "❌ No";
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nft_certificate_image']) . "</td>";
        echo "<td>" . $file_exists . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p>No courses have NFT certificate templates uploaded yet.</p>\n";
    
    // Show all courses
    echo "<h3>All Courses:</h3>\n";
    $all_query = "SELECT id, course_name, nft_certificate_image FROM courses";
    $all_result = mysqli_query($conn, $all_query);
    
    if ($all_result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Course ID</th><th>Course Name</th><th>NFT Certificate Image</th></tr>\n";
        
        while ($row = mysqli_fetch_assoc($all_result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
            echo "<td>" . ($row['nft_certificate_image'] ? htmlspecialchars($row['nft_certificate_image']) : "<em>None</em>") . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
}

// Create a dummy certificate image for testing
echo "<h3>Creating Test Certificate Image</h3>\n";

$uploads_dir = "uploads/nft_certificates/";
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
    echo "✓ Created uploads/nft_certificates/ directory<br>\n";
}

$test_image_path = $uploads_dir . "test_certificate.jpg";

// Create a simple test image (placeholder)
$image = imagecreate(800, 600);
$bg_color = imagecolorallocate($image, 67, 126, 234); // Blue background
$text_color = imagecolorallocate($image, 255, 255, 255); // White text

// Add text
imagestring($image, 5, 250, 100, "CERTIFICATE", $text_color);
imagestring($image, 4, 200, 150, "OF COMPLETION", $text_color);
imagestring($image, 3, 300, 250, "Course Name", $text_color);
imagestring($image, 2, 250, 300, "Student Name", $text_color);
imagestring($image, 2, 200, 350, "Learnity Learning Platform", $text_color);

// Save the image
if (imagejpeg($image, $test_image_path)) {
    echo "✓ Created test certificate image: $test_image_path<br>\n";
    
    // Update course 4 to have this certificate
    $update_query = "UPDATE courses SET nft_certificate_image = ? WHERE id = 4";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("s", $test_image_path);
    
    if ($stmt->execute()) {
        echo "✓ Updated course 4 with test certificate template<br>\n";
    } else {
        echo "❌ Failed to update course 4<br>\n";
    }
} else {
    echo "❌ Failed to create test certificate image<br>\n";
}

imagedestroy($image);
?>
