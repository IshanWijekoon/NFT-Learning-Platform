<?php
include 'db.php';

// Create web3_enrollments table if it doesn't exist
$create_table_sql = "
CREATE TABLE IF NOT EXISTS web3_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    wallet_address VARCHAR(42) NOT NULL,
    chain_id VARCHAR(10) DEFAULT NULL,
    signature TEXT NOT NULL,
    enrollment_type VARCHAR(20) DEFAULT 'web3',
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    learner_id INT DEFAULT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    INDEX idx_course_wallet (course_id, wallet_address),
    INDEX idx_wallet (wallet_address),
    INDEX idx_learner (learner_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES learners(id) ON DELETE SET NULL,
    UNIQUE KEY unique_course_wallet (course_id, wallet_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_table_sql)) {
    echo "Web3 enrollments table created successfully or already exists.<br>";
} else {
    echo "Error creating web3_enrollments table: " . mysqli_error($conn) . "<br>";
}

// Create web3_certificates table for NFT certificates
$create_certificates_table_sql = "
CREATE TABLE IF NOT EXISTS web3_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    course_id INT NOT NULL,
    wallet_address VARCHAR(42) NOT NULL,
    token_id VARCHAR(100) DEFAULT NULL,
    contract_address VARCHAR(42) DEFAULT NULL,
    ipfs_hash VARCHAR(100) DEFAULT NULL,
    metadata_json TEXT DEFAULT NULL,
    certificate_issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blockchain_tx_hash VARCHAR(66) DEFAULT NULL,
    certificate_status ENUM('pending', 'minted', 'failed') DEFAULT 'pending',
    INDEX idx_enrollment (enrollment_id),
    INDEX idx_wallet_course (wallet_address, course_id),
    INDEX idx_token (token_id),
    FOREIGN KEY (enrollment_id) REFERENCES web3_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_certificates_table_sql)) {
    echo "Web3 certificates table created successfully or already exists.<br>";
} else {
    echo "Error creating web3_certificates table: " . mysqli_error($conn) . "<br>";
}

// Create wallet_profiles table for wallet-based user profiles
$create_profiles_table_sql = "
CREATE TABLE IF NOT EXISTS wallet_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_address VARCHAR(42) NOT NULL UNIQUE,
    display_name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    profile_image_url TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    social_links JSON DEFAULT NULL,
    preferred_chain_id VARCHAR(10) DEFAULT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    linked_learner_id INT DEFAULT NULL,
    verification_status ENUM('unverified', 'verified') DEFAULT 'unverified',
    INDEX idx_wallet (wallet_address),
    INDEX idx_learner (linked_learner_id),
    FOREIGN KEY (linked_learner_id) REFERENCES learners(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_profiles_table_sql)) {
    echo "Wallet profiles table created successfully or already exists.<br>";
} else {
    echo "Error creating wallet_profiles table: " . mysqli_error($conn) . "<br>";
}

// Add web3_wallet_address column to existing learners table if it doesn't exist
$add_wallet_column_sql = "
ALTER TABLE learners 
ADD COLUMN IF NOT EXISTS web3_wallet_address VARCHAR(42) DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_wallet_address (web3_wallet_address)";

if (mysqli_query($conn, $add_wallet_column_sql)) {
    echo "Added web3_wallet_address column to learners table (if not exists).<br>";
} else {
    echo "Note: web3_wallet_address column may already exist or there was an error: " . mysqli_error($conn) . "<br>";
}

echo "<h2>Web3 Integration Setup Complete!</h2>";
echo "<p>The following tables have been created/updated:</p>";
echo "<ul>";
echo "<li><strong>web3_enrollments</strong> - Stores wallet-based course enrollments</li>";
echo "<li><strong>web3_certificates</strong> - Stores NFT certificate information</li>";
echo "<li><strong>wallet_profiles</strong> - Stores wallet-based user profiles</li>";
echo "<li><strong>learners</strong> - Updated with web3_wallet_address column</li>";
echo "</ul>";

echo "<p><a href='course-browser.php'>Go to Course Browser</a> to test the Web3 wallet integration.</p>";

mysqli_close($conn);
?>