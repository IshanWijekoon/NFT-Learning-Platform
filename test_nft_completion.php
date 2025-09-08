<?php
// Test NFT certificate awarding on course completion
include 'db.php';
include 'nft_certificate_system.php';

echo "<h2>Testing NFT Certificate Awarding System</h2>\n";

// Test parameters
$learner_id = 7; // Using learner ID 7 from enrollments table
$course_id = 18; // Using course ID 18 (Game Development) - has NFT certificate template

echo "<h3>Test Parameters:</h3>\n";
echo "Learner ID: $learner_id<br>\n";
echo "Course ID: $course_id<br><br>\n";

// Check if learner exists
$learner_query = "SELECT full_name FROM learners WHERE id = ?";
$stmt = $conn->prepare($learner_query);
$stmt->bind_param("i", $learner_id);
$stmt->execute();
$learner_result = $stmt->get_result();

if ($learner_result->num_rows === 0) {
    echo "❌ Error: Learner ID $learner_id not found or not a learner<br>\n";
    exit();
}

$learner = $learner_result->fetch_assoc();
echo "✓ Learner Found: " . htmlspecialchars($learner['full_name']) . "<br>\n";

// Check if course exists
$course_query = "SELECT course_name FROM courses WHERE id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo "❌ Error: Course ID $course_id not found<br>\n";
    exit();
}

$course = $course_result->fetch_assoc();
echo "✓ Course Found: " . htmlspecialchars($course['course_name']) . "<br>\n";

// Check enrollment
$enrollment_query = "SELECT completed FROM enrollments WHERE learner_id = ? AND course_id = ?";
$stmt = $conn->prepare($enrollment_query);
$stmt->bind_param("ii", $learner_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    echo "⚠️ Warning: Learner is not enrolled in this course<br>\n";
} else {
    $enrollment = $enrollment_result->fetch_assoc();
    echo "✓ Enrollment Found - Completed: " . ($enrollment['completed'] ? 'Yes' : 'No') . "<br>\n";
}

// Check if certificate already exists
$cert_check_query = "SELECT nc.nft_key, nv.verification_code FROM nft_certificates nc LEFT JOIN nft_verifications nv ON nc.id = nv.certificate_id WHERE nc.learner_id = ? AND nc.course_id = ?";
$stmt = $conn->prepare($cert_check_query);
$stmt->bind_param("ii", $learner_id, $course_id);
$stmt->execute();
$cert_result = $stmt->get_result();

if ($cert_result->num_rows > 0) {
    $existing_cert = $cert_result->fetch_assoc();
    echo "⚠️ Certificate already exists for this course:<br>\n";
    echo "NFT Key: " . htmlspecialchars($existing_cert['nft_key']) . "<br>\n";
    echo "Verification Code: " . htmlspecialchars($existing_cert['verification_code']) . "<br><br>\n";
} else {
    echo "✓ No existing certificate found<br><br>\n";
}

// Test NFT certificate awarding
echo "<h3>Testing NFT Certificate Award:</h3>\n";

try {
    $result = awardNFTCertificate($course_id, $learner_id);
    
    if ($result['success']) {
        echo "✅ NFT Certificate Awarded Successfully!<br>\n";
        echo "NFT Key: " . htmlspecialchars($result['nft_key']) . "<br>\n";
        echo "Verification Code: " . htmlspecialchars($result['verification_code']) . "<br>\n";
        echo "Certificate Hash: " . htmlspecialchars($result['certificate_hash']) . "<br>\n";
        echo "Message: " . htmlspecialchars($result['message']) . "<br><br>\n";
        
        // Test verification
        echo "<h3>Testing Verification:</h3>\n";
        $verification = verifyCertificate($result['verification_code']);
        if ($verification) {
            echo "✅ Certificate Verification Successful!<br>\n";
            echo "Course: " . htmlspecialchars($verification['course_name']) . "<br>\n";
            echo "Learner: " . htmlspecialchars($verification['learner_name']) . "<br>\n";
            echo "Issued: " . htmlspecialchars($verification['issued_at']) . "<br>\n";
        } else {
            echo "❌ Certificate Verification Failed<br>\n";
        }
        
    } else {
        echo "❌ NFT Certificate Award Failed: " . htmlspecialchars($result['message']) . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
}

echo "<br><h3>All NFT Certificates for this Learner:</h3>\n";
$all_certificates = getLearnerCertificates($learner_id);
if (empty($all_certificates)) {
    echo "No certificates found.<br>\n";
} else {
    foreach ($all_certificates as $cert) {
        echo "Course: " . htmlspecialchars($cert['course_name']) . "<br>\n";
        echo "NFT Key: " . htmlspecialchars($cert['nft_key']) . "<br>\n";
        echo "Verification Code: " . htmlspecialchars($cert['verification_code']) . "<br>\n";
        echo "Issued: " . htmlspecialchars($cert['issued_at']) . "<br><hr>\n";
    }
}
?>
