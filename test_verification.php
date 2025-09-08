<?php
include 'db.php';
include 'nft_certificate_system.php';

echo "<h2>Testing Certificate Verification</h2>\n";

// Use the verification code from the previous test
$verification_code = "62793156";

echo "Testing verification code: $verification_code<br><br>\n";

$result = verifyCertificate($verification_code);

if ($result['success']) {
    echo "✅ Certificate Verification Successful!<br>\n";
    $cert = $result['certificate'];
    echo "Course: " . htmlspecialchars($cert['course_name']) . "<br>\n";
    echo "Learner: " . htmlspecialchars($cert['learner_name']) . "<br>\n";
    echo "NFT Key: " . htmlspecialchars($cert['nft_key']) . "<br>\n";
    echo "Issued: " . htmlspecialchars($cert['issued_at']) . "<br>\n";
    echo "Verification Count: " . $cert['verification_count'] . "<br>\n";
} else {
    echo "❌ Verification Failed: " . htmlspecialchars($result['message']) . "<br>\n";
}
?>
