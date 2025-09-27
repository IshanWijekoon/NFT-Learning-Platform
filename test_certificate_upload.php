<?php
session_start();
include 'db.php';

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    die("Please log in as a creator to test certificate upload");
}

$creator_id = $_SESSION['user_id'];

if ($_POST) {
    echo "<h2>Testing Certificate Upload</h2>";
    
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $certificate = $_FILES['certificate'];
        $certUploadDir = 'uploads/nft_certificates/';
        
        // Create unique filename for certificate
        $certFileExtension = pathinfo($certificate['name'], PATHINFO_EXTENSION);
        $uniqueCertFilename = 'cert_test_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $certFileExtension;
        $certificatePath = $certUploadDir . $uniqueCertFilename;
        
        // Create directory if it doesn't exist
        if (!file_exists($certUploadDir)) {
            mkdir($certUploadDir, 0777, true);
            echo "<p>Created directory: $certUploadDir</p>";
        }
        
        // Move uploaded certificate file
        if (move_uploaded_file($certificate['tmp_name'], $certificatePath)) {
            echo "<p style='color: green;'>✓ Certificate uploaded successfully to: $certificatePath</p>";
            
            // Check if column exists and update a test record
            $check_column = "SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'";
            $result = mysqli_query($conn, $check_column);
            
            if (mysqli_num_rows($result) > 0) {
                echo "<p style='color: green;'>✓ nft_certificate_image column exists</p>";
                
                // Find the latest course by this creator
                $latest_course_query = "SELECT id, course_name FROM courses WHERE creator_id = '$creator_id' ORDER BY id DESC LIMIT 1";
                $course_result = mysqli_query($conn, $latest_course_query);
                
                if (mysqli_num_rows($course_result) > 0) {
                    $course = mysqli_fetch_assoc($course_result);
                    
                    // Update the course with certificate path
                    $update_query = "UPDATE courses SET nft_certificate_image = '$certificatePath' WHERE id = {$course['id']}";
                    
                    if (mysqli_query($conn, $update_query)) {
                        echo "<p style='color: green;'>✓ Updated course '{$course['course_name']}' (ID: {$course['id']}) with certificate path</p>";
                        echo "<p><strong>File Path:</strong> $certificatePath</p>";
                        echo "<p><strong>File Exists:</strong> " . (file_exists($certificatePath) ? "Yes" : "No") . "</p>";
                        echo "<p><strong>File Size:</strong> " . filesize($certificatePath) . " bytes</p>";
                        
                        // Show preview
                        echo "<div style='margin: 1rem 0;'>";
                        echo "<strong>Certificate Preview:</strong><br>";
                        echo "<img src='$certificatePath' style='max-width: 300px; max-height: 200px; border: 1px solid #ccc; border-radius: 5px;'>";
                        echo "</div>";
                        
                    } else {
                        echo "<p style='color: red;'>✗ Failed to update course: " . mysqli_error($conn) . "</p>";
                    }
                } else {
                    echo "<p style='color: orange;'>⚠ No courses found for this creator. Create a course first.</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ nft_certificate_image column does not exist. Run setup first.</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to move uploaded certificate file</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ No certificate file uploaded or upload error</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Certificate Upload</title>
</head>
<body>
    <h2>Test Certificate Upload</h2>
    <form method="POST" enctype="multipart/form-data" style="background: #f0f0f0; padding: 1rem; border-radius: 5px;">
        <div style="margin: 1rem 0;">
            <label for="certificate"><strong>Upload Certificate Template:</strong></label><br>
            <input type="file" name="certificate" id="certificate" accept="image/*" required>
        </div>
        <button type="submit" style="background: #007cba; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px;">
            Upload & Test
        </button>
    </form>
    
    <p><a href="test_certificate_paths.php">View Certificate Paths</a> | 
       <a href="course-management.php">Course Management</a></p>
</body>
</html>
