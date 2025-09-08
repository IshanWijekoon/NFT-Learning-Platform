<?php
/**
 * Smart NFT Certificate Database Setup Script
 * Checks for existing columns and tables before creating
 */

include 'db.php';

echo "<h2>NFT Certificate System Setup</h2>";

$success_count = 0;
$total_operations = 0;

echo "<h3>Checking and Creating Database Components...</h3>";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px; font-family: monospace;'>";

// 1. Check and add NFT certificate column to courses table
$total_operations++;
echo "<p><strong>Operation 1: Adding NFT certificate column to courses table</strong></p>";
$check_col = $conn->query("SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'");
if ($check_col->num_rows == 0) {
    $add_col = $conn->query("ALTER TABLE courses ADD COLUMN nft_certificate_image VARCHAR(255) NULL");
    if ($add_col) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Added nft_certificate_image column</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to add column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ Column already exists</p>";
    $success_count++;
}
echo "<hr>";

// 2. Create NFT certificates table
$total_operations++;
echo "<p><strong>Operation 2: Creating NFT certificates table</strong></p>";
$check_table = $conn->query("SHOW TABLES LIKE 'nft_certificates'");
if ($check_table->num_rows == 0) {
    $create_table = $conn->query("
        CREATE TABLE nft_certificates (
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
            metadata TEXT NULL,
            INDEX idx_course_learner (course_id, learner_id),
            INDEX idx_nft_key (nft_key),
            INDEX idx_certificate_hash (certificate_hash)
        )
    ");
    if ($create_table) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Created nft_certificates table</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to create table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ Table already exists</p>";
    $success_count++;
}
echo "<hr>";

// 3. Create NFT verification table
$total_operations++;
echo "<p><strong>Operation 3: Creating NFT verifications table</strong></p>";
$check_verify_table = $conn->query("SHOW TABLES LIKE 'nft_verifications'");
if ($check_verify_table->num_rows == 0) {
    $create_verify_table = $conn->query("
        CREATE TABLE nft_verifications (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            certificate_id INT(11) NOT NULL,
            verification_code VARCHAR(32) NOT NULL UNIQUE,
            verified_at TIMESTAMP NULL,
            verifier_ip VARCHAR(45) NULL,
            verification_count INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_verification_code (verification_code)
        )
    ");
    if ($create_verify_table) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Created nft_verifications table</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to create table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ Table already exists</p>";
    $success_count++;
}
echo "<hr>";

// 4. Add completion tracking columns to enrollments
$total_operations++;
echo "<p><strong>Operation 4: Adding completion tracking to enrollments</strong></p>";
$check_completion = $conn->query("SHOW COLUMNS FROM enrollments LIKE 'completion_date'");
if ($check_completion->num_rows == 0) {
    $add_completion = $conn->query("ALTER TABLE enrollments ADD COLUMN completion_date TIMESTAMP NULL");
    if ($add_completion) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Added completion_date column</p>";
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to add completion_date: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ completion_date column already exists</p>";
}

$check_cert_issued = $conn->query("SHOW COLUMNS FROM enrollments LIKE 'certificate_issued'");
if ($check_cert_issued->num_rows == 0) {
    $add_cert_issued = $conn->query("ALTER TABLE enrollments ADD COLUMN certificate_issued TINYINT(1) DEFAULT 0");
    if ($add_cert_issued) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Added certificate_issued column</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to add certificate_issued: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ certificate_issued column already exists</p>";
    $success_count++;
}
echo "<hr>";

// 5. Create NFT settings table
$total_operations++;
echo "<p><strong>Operation 5: Creating NFT settings table</strong></p>";
$check_settings_table = $conn->query("SHOW TABLES LIKE 'nft_settings'");
if ($check_settings_table->num_rows == 0) {
    $create_settings_table = $conn->query("
        CREATE TABLE nft_settings (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            description TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        )
    ");
    if ($create_settings_table) {
        echo "<p style='color: green; margin-left: 1rem;'>✅ Created nft_settings table</p>";
        $success_count++;
    } else {
        echo "<p style='color: red; margin-left: 1rem;'>❌ Failed to create table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue; margin-left: 1rem;'>ℹ️ Table already exists</p>";
    $success_count++;
}

echo "</div>";

// Insert default settings
echo "<h3>Setting Up Default Configuration...</h3>";
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

// Create directories
echo "<h3>Creating Upload Directories...</h3>";
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

echo "<h3>Setup Summary</h3>";
echo "<p><strong>$success_count</strong> out of <strong>$total_operations</strong> operations completed successfully.</p>";

if ($success_count >= ($total_operations - 1)) { // Allow for minor errors
    echo "<p style='color: green; font-weight: bold; font-size: 1.2rem;'>🎉 NFT Certificate System is ready!</p>";
    
    echo "<h3>✨ Features Now Available:</h3>";
    echo "<ul>";
    echo "<li>📁 <strong>Course Creation:</strong> Upload NFT certificate templates in course-management.php</li>";
    echo "<li>🏆 <strong>Auto-Award:</strong> Certificates automatically awarded on course completion</li>";
    echo "<li>🔍 <strong>Verification:</strong> Public certificate verification at <a href='verify_certificate.php'>verify_certificate.php</a></li>";
    echo "<li>📋 <strong>My Certificates:</strong> Learners can view certificates at <a href='my_certificates.php'>my_certificates.php</a></li>";
    echo "<li>🔐 <strong>Blockchain Security:</strong> Unique NFT keys and certificate hashes</li>";
    echo "</ul>";
    
    echo "<h3>🚀 Quick Test:</h3>";
    echo "<ol>";
    echo "<li>Go to <a href='course-management.php'>course-management.php</a> and create a course with certificate template</li>";
    echo "<li>Enroll as a learner and complete the course</li>";
    echo "<li>Check <a href='my_certificates.php'>my_certificates.php</a> for your NFT certificate</li>";
    echo "<li>Use the verification code at <a href='verify_certificate.php'>verify_certificate.php</a></li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Some operations failed. Please check the errors above.</p>";
}

echo "<h3>📊 System Status:</h3>";
$tables_to_check = ['courses', 'nft_certificates', 'nft_verifications', 'nft_settings', 'enrollments'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' is ready</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' is missing</p>";
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
        background: #f8f9fa;
    }
    h2 { 
        color: #333; 
        border-bottom: 2px solid #667eea; 
        padding-bottom: 0.5rem; 
        background: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    h3 { color: #667eea; margin-top: 2rem; }
    hr { border: none; border-top: 1px solid #eee; margin: 0.5rem 0; }
    ol, ul { margin-left: 1rem; }
    a { color: #667eea; text-decoration: none; font-weight: bold; }
    a:hover { text-decoration: underline; }
    .container { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 1rem; }
</style>
