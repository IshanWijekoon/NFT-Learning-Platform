<?php
include 'db.php';

echo "<h2>Setting up NFT Certificate Tables</h2>";

// Check if nft_certificate_image column exists in courses table
$check_column = "SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding nft_certificate_image column to courses table...</p>";
    $alter_courses = "ALTER TABLE courses ADD COLUMN nft_certificate_image VARCHAR(255) NULL AFTER video_path";
    if (mysqli_query($conn, $alter_courses)) {
        echo "<p style='color: green;'>✓ Added nft_certificate_image column to courses table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>✓ nft_certificate_image column already exists in courses table</p>";
}

// Create nft_certificates table
$nft_certificates_table = "CREATE TABLE IF NOT EXISTS nft_certificates (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    learner_id INT(11) NOT NULL,
    creator_id INT(11) NOT NULL,
    nft_key VARCHAR(64) NOT NULL UNIQUE,
    certificate_hash VARCHAR(128) NOT NULL UNIQUE,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    learner_name VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    creator_name VARCHAR(255) NOT NULL,
    certificate_image_path VARCHAR(255) NOT NULL,
    blockchain_tx_hash VARCHAR(255) NULL,
    verification_url VARCHAR(500) NULL,
    status ENUM('pending', 'issued', 'verified', 'revoked') DEFAULT 'pending',
    metadata JSON NULL,
    INDEX idx_course_learner (course_id, learner_id),
    INDEX idx_nft_key (nft_key),
    INDEX idx_certificate_hash (certificate_hash)
)";

if (mysqli_query($conn, $nft_certificates_table)) {
    echo "<p style='color: green;'>✓ Created/verified nft_certificates table</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating nft_certificates table: " . mysqli_error($conn) . "</p>";
}

// Create nft_verifications table
$nft_verifications_table = "CREATE TABLE IF NOT EXISTS nft_verifications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT(11) NOT NULL,
    verification_code VARCHAR(32) NOT NULL UNIQUE,
    verified_at TIMESTAMP NULL,
    verifier_ip VARCHAR(45) NULL,
    verification_count INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_verification_code (verification_code)
)";

if (mysqli_query($conn, $nft_verifications_table)) {
    echo "<p style='color: green;'>✓ Created/verified nft_verifications table</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating nft_verifications table: " . mysqli_error($conn) . "</p>";
}

// Add certificate_issued column to enrollments table if it doesn't exist
$check_enrollment_column = "SHOW COLUMNS FROM enrollments LIKE 'certificate_issued'";
$enrollment_result = mysqli_query($conn, $check_enrollment_column);

if (mysqli_num_rows($enrollment_result) == 0) {
    echo "<p>Adding certificate_issued column to enrollments table...</p>";
    $alter_enrollments = "ALTER TABLE enrollments ADD COLUMN certificate_issued TINYINT(1) DEFAULT 0 AFTER completed";
    if (mysqli_query($conn, $alter_enrollments)) {
        echo "<p style='color: green;'>✓ Added certificate_issued column to enrollments table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding certificate_issued column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>✓ certificate_issued column already exists in enrollments table</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p>The NFT certificate system should now be functional.</p>";
echo "<p><a href='course-management.php' style='color: blue;'>Go back to Course Management</a></p>";
?>
