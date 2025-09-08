<?php
// Test my_certificates.php functionality
session_start();
$_SESSION['user_id'] = 7; // Test with learner ID 7
$_SESSION['role'] = 'learner';

include 'db.php';
include 'nft_certificate_system.php';

echo "<h2>Testing My Certificates Page</h2>\n";

$learner_id = $_SESSION['user_id'];

echo "Testing with Learner ID: $learner_id<br><br>\n";

// Get learner info
$learner_query = "SELECT full_name FROM learners WHERE id = ?";
$stmt = $conn->prepare($learner_query);
$stmt->bind_param("i", $learner_id);
$stmt->execute();
$learner_result = $stmt->get_result();

if ($learner_result->num_rows > 0) {
    $learner = $learner_result->fetch_assoc();
    echo "✓ Learner Found: " . htmlspecialchars($learner['full_name']) . "<br>\n";
} else {
    echo "❌ Learner not found<br>\n";
    exit();
}

// Get all certificates for this learner
echo "<br>Getting certificates...<br>\n";
$certificates = getLearnerCertificates($learner_id);

echo "✓ Found " . count($certificates) . " certificate(s)<br><br>\n";

if (!empty($certificates)) {
    echo "<h3>Certificate Details:</h3>\n";
    foreach ($certificates as $cert) {
        echo "<div style='border: 1px solid #ccc; padding: 1rem; margin: 1rem 0;'>\n";
        echo "<strong>Course:</strong> " . htmlspecialchars($cert['course_name'] ?? 'Unknown') . "<br>\n";
        echo "<strong>Category:</strong> " . htmlspecialchars($cert['category'] ?? 'Unknown') . "<br>\n";
        echo "<strong>NFT Key:</strong> " . htmlspecialchars($cert['nft_key']) . "<br>\n";
        echo "<strong>Verification Code:</strong> " . htmlspecialchars($cert['verification_code']) . "<br>\n";
        echo "<strong>Issued:</strong> " . htmlspecialchars($cert['issued_at']) . "<br>\n";
        echo "</div>\n";
    }
} else {
    echo "<p>No certificates found for this learner.</p>\n";
}

echo "<br>✅ My Certificates page should now work properly!<br>\n";
?>
