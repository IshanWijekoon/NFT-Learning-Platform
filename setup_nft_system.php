<?php
/**
 * NFT Certificate Database Setup Script
 * Run this script once to set up the NFT certificate system tables
 */

include 'db.php';

echo "<h2>NFT Certificate System Setup</h2>";

// Array of SQL commands to execute
$sql_commands = [
    // 1. Add NFT certificate column to courses table
    "ALTER TABLE courses ADD COLUMN IF NOT EXISTS nft_certificate_image VARCHAR(255) NULL AFTER thumbnail",
    
    // 2. Create NFT certificates table
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
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (creator_id) REFERENCES creators(id) ON DELETE CASCADE,
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
        FOREIGN KEY (certificate_id) REFERENCES nft_certificates(id) ON DELETE CASCADE,
        INDEX idx_verification_code (verification_code)
    )",
    
    // 4. Add completion tracking to enrollments
    "ALTER TABLE enrollments ADD COLUMN IF NOT EXISTS completion_date TIMESTAMP NULL AFTER completed_at",
    "ALTER TABLE enrollments ADD COLUMN IF NOT EXISTS certificate_issued TINYINT(1) DEFAULT 0 AFTER completion_date",
    
    // 5. Create NFT settings table
    "CREATE TABLE IF NOT EXISTS nft_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        description TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    )",
    
    // 6. Insert default NFT settings
    "INSERT IGNORE INTO nft_settings (setting_key, setting_value, description) VALUES
        ('nft_enabled', '1', 'Enable/disable NFT certificate system'),
        ('certificate_template_width', '800', 'Certificate image width in pixels'),
        ('certificate_template_height', '600', 'Certificate image height in pixels'),
        ('certificate_format', 'png', 'Certificate image format (png, jpg)'),
        ('blockchain_network', 'ethereum', 'Blockchain network for NFT minting'),
        ('auto_mint_enabled', '0', 'Automatically mint NFT certificates'),
        ('verification_base_url', 'https://yoursite.com/verify/', 'Base URL for certificate verification')"
];

$success_count = 0;
$total_commands = count($sql_commands);

echo "<h3>Executing SQL Commands...</h3>";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px; font-family: monospace;'>";

foreach ($sql_commands as $index => $sql) {
    echo "<p><strong>Command " . ($index + 1) . ":</strong></p>";
    echo "<p style='color: #666; margin-left: 1rem;'>" . htmlspecialchars(substr($sql, 0, 100)) . "...</p>";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Success</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Error: " . htmlspecialchars($conn->error) . "</p>";
    }
    echo "<hr>";
}

echo "</div>";

echo "<h3>Setup Summary</h3>";
echo "<p><strong>$success_count</strong> out of <strong>$total_commands</strong> commands executed successfully.</p>";

if ($success_count === $total_commands) {
    echo "<p style='color: green; font-weight: bold;'>🎉 NFT Certificate System setup completed successfully!</p>";
    
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
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Upload NFT certificate templates when creating courses</li>";
    echo "<li>Learners will automatically receive NFT certificates when completing courses</li>";
    echo "<li>Test the certificate verification system at <a href='verify_certificate.php'>verify_certificate.php</a></li>";
    echo "<li>Learners can view their certificates at <a href='my_certificates.php'>my_certificates.php</a></li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Some commands failed. Please check the errors above and run this script again.</p>";
}

// Test database structure
echo "<h3>Database Structure Verification</h3>";
$tables_to_check = ['courses', 'nft_certificates', 'nft_verifications', 'nft_settings', 'enrollments'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Show some column info for important tables
        if (in_array($table, ['courses', 'nft_certificates', 'enrollments'])) {
            $cols = $conn->query("SHOW COLUMNS FROM $table");
            echo "<ul style='margin-left: 2rem; font-size: 0.9rem; color: #666;'>";
            while ($col = $cols->fetch_assoc()) {
                echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
            }
            echo "</ul>";
        }
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
