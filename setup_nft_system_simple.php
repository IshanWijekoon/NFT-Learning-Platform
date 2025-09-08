<?php
/**
 * NFT Certificate Database Setup Script (Simplified)
 * Run this script once to set up the NFT certificate system tables
 */

include 'db.php';

echo "<h2>NFT Certificate System Setup</h2>";

// Array of SQL commands to execute
$sql_commands = [
    // 1. Add NFT certificate column to courses table
    "ALTER TABLE courses ADD COLUMN nft_certificate_image VARCHAR(255) NULL",
    
    // 2. Create NFT certificates table (without foreign key constraints initially)
    "CREATE TABLE IF NOT EXISTS nft_certificates (
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
    )",
    
    // 3. Create NFT verification table
    "CREATE TABLE IF NOT EXISTS nft_verifications (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        certificate_id INT(11) NOT NULL,
        verification_code VARCHAR(32) NOT NULL UNIQUE,
        verified_at TIMESTAMP NULL,
        verifier_ip VARCHAR(45) NULL,
        verification_count INT(11) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_verification_code (verification_code)
    )",
    
    // 4. Add completion tracking to enrollments
    "ALTER TABLE enrollments ADD COLUMN completion_date TIMESTAMP NULL",
    "ALTER TABLE enrollments ADD COLUMN certificate_issued TINYINT(1) DEFAULT 0",
    
    // 5. Create NFT settings table
    "CREATE TABLE IF NOT EXISTS nft_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        description TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    )"
];

$success_count = 0;
$total_commands = count($sql_commands);

echo "<h3>Executing SQL Commands...</h3>";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px; font-family: monospace;'>";

foreach ($sql_commands as $index => $sql) {
    echo "<p><strong>Command " . ($index + 1) . ":</strong></p>";
    echo "<p style='color: #666; margin-left: 1rem;'>" . htmlspecialchars(substr($sql, 0, 80)) . "...</p>";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Success</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Error: " . htmlspecialchars($conn->error) . "</p>";
    }
    echo "<hr>";
}

echo "</div>";

// Insert default settings separately to avoid conflicts
echo "<h3>Inserting Default Settings...</h3>";
$settings = [
    ['nft_enabled', '1', 'Enable/disable NFT certificate system'],
    ['certificate_template_width', '800', 'Certificate image width in pixels'],
    ['certificate_template_height', '600', 'Certificate image height in pixels'],
    ['certificate_format', 'png', 'Certificate image format (png, jpg)'],
    ['blockchain_network', 'ethereum', 'Blockchain network for NFT minting'],
    ['auto_mint_enabled', '0', 'Automatically mint NFT certificates'],
    ['verification_base_url', 'https://yoursite.com/verify/', 'Base URL for certificate verification']
];

foreach ($settings as $setting) {
    $check_stmt = $conn->prepare("SELECT id FROM nft_settings WHERE setting_key = ?");
    $check_stmt->bind_param("s", $setting[0]);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        $insert_stmt = $conn->prepare("INSERT INTO nft_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>✅ Added setting: " . $setting[0] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add setting: " . $setting[0] . "</p>";
        }
        $insert_stmt->close();
    } else {
        echo "<p style='color: blue;'>ℹ️ Setting already exists: " . $setting[0] . "</p>";
    }
    $check_stmt->close();
}

echo "<h3>Setup Summary</h3>";
echo "<p><strong>$success_count</strong> out of <strong>$total_commands</strong> commands executed successfully.</p>";

// Create the uploads directory for certificates
$cert_dir = 'uploads/nft_certificates/';
if (!file_exists($cert_dir)) {
    if (mkdir($cert_dir, 0777, true)) {
        echo "<p style='color: green;'>✅ Created certificate upload directory: $cert_dir</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Could not create certificate upload directory. Please create manually: $cert_dir</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Certificate upload directory already exists: $cert_dir</p>";
}

echo "<p style='color: green; font-weight: bold;'>🎉 NFT Certificate System setup completed!</p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Upload NFT certificate templates when creating courses in course-management.php</li>";
echo "<li>Learners will automatically receive NFT certificates when completing courses</li>";
echo "<li>Test the certificate verification system at <a href='verify_certificate.php'>verify_certificate.php</a></li>";
echo "<li>Learners can view their certificates at <a href='my_certificates.php'>my_certificates.php</a></li>";
echo "</ol>";

// Test database structure
echo "<h3>Database Structure Verification</h3>";
$tables_to_check = ['courses', 'nft_certificates', 'nft_verifications', 'nft_settings', 'enrollments'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
    }
}

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
        line-height: 1.6;
    }
    h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; }
    h3 { color: #667eea; margin-top: 2rem; }
    hr { border: none; border-top: 1px solid #eee; margin: 0.5rem 0; }
    ol, ul { margin-left: 1rem; }
    a { color: #667eea; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>
