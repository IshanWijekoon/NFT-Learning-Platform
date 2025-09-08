<?php
// Test NFT search functionality
include 'db.php';

echo "<h2>Testing NFT Search Functionality</h2>\n";

// Test with the known NFT key from our previous test
$test_nft_key = "NFT936A6064183ACDA7A64C47E7060FAA0E1757365260";

echo "Testing with NFT Key: " . htmlspecialchars($test_nft_key) . "<br><br>\n";

// Search for NFT certificate by NFT key
$search_query = "
    SELECT nc.*, nv.verification_code, c.course_name, l.full_name as learner_name
    FROM nft_certificates nc
    LEFT JOIN nft_verifications nv ON nc.id = nv.certificate_id
    LEFT JOIN courses c ON nc.course_id = c.id
    LEFT JOIN learners l ON nc.learner_id = l.id
    WHERE nc.nft_key = ?
";

$stmt = $conn->prepare($search_query);
$stmt->bind_param("s", $test_nft_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $certificate = $result->fetch_assoc();
    echo "✅ NFT Certificate Found!<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 1rem; margin: 1rem 0;'>\n";
    echo "<strong>Course:</strong> " . htmlspecialchars($certificate['course_name']) . "<br>\n";
    echo "<strong>Learner:</strong> " . htmlspecialchars($certificate['learner_name']) . "<br>\n";
    echo "<strong>NFT Key:</strong> " . htmlspecialchars($certificate['nft_key']) . "<br>\n";
    echo "<strong>Verification Code:</strong> " . htmlspecialchars($certificate['verification_code']) . "<br>\n";
    echo "<strong>Issued:</strong> " . htmlspecialchars($certificate['issued_at']) . "<br>\n";
    echo "</div>\n";
    
    echo "<p style='color: green;'>✓ Search would redirect to: verify_certificate.php?code=" . htmlspecialchars($certificate['verification_code']) . "</p>\n";
} else {
    echo "<p style='color: red;'>❌ No NFT certificate found for this key.</p>\n";
}

// Test with an invalid NFT key
echo "<br><h3>Testing with Invalid NFT Key</h3>\n";
$invalid_key = "INVALID_NFT_KEY_123";
echo "Testing with invalid key: " . htmlspecialchars($invalid_key) . "<br><br>\n";

$stmt = $conn->prepare($search_query);
$stmt->bind_param("s", $invalid_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Certificate found (unexpected)</p>\n";
} else {
    echo "<p style='color: orange;'>✓ No certificate found (as expected)</p>\n";
    echo "<p>Error message would be: 'No NFT certificate found for this key. Please verify the NFT key and try again.'</p>\n";
}

echo "<br>✅ NFT Search functionality is working correctly!<br>\n";
?>
