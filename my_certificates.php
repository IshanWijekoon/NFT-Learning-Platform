<?php
session_start();
include 'db.php';
include 'nft_certificate_system.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.html');
    exit();
}

$learner_id = $_SESSION['user_id'];

// Get learner info
$learner_query = "SELECT full_name FROM learners WHERE id = ?";
$stmt = $conn->prepare($learner_query);
$stmt->bind_param("i", $learner_id);
$stmt->execute();
$learner_result = $stmt->get_result();
$learner = $learner_result->fetch_assoc();

// Get all certificates for this learner
$certificates = getLearnerCertificates($learner_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NFT Certificates - Learnity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .certificates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .certificate-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .certificate-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .certificate-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .certificate-image .placeholder {
            color: #999;
            font-size: 3rem;
        }

        .certificate-content {
            padding: 1.5rem;
        }

        .certificate-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .certificate-course {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .certificate-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .certificate-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 1px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .nft-key {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.8rem;
            word-break: break-all;
            margin-top: 1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-issued {
            background: #d4edda;
            color: #155724;
        }

        .status-verified {
            background: #cce5ff;
            color: #004085;
        }

        .no-certificates {
            text-align: center;
            color: white;
            padding: 3rem;
        }

        .no-certificates i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .certificates-grid {
                grid-template-columns: 1fr;
            }
            
            .certificate-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🎓 Learnity</div>
            <div class="nav-links">
                <a href="home-learner.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="course-browser.php"><i class="fas fa-search"></i> Browse Courses</a>
                <a href="my_certificates.php"><i class="fas fa-certificate"></i> My Certificates</a>
                <a href="learner-profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-certificate"></i> My NFT Certificates</h1>
            <p>Your blockchain-secured learning achievements, <?php echo htmlspecialchars($learner['full_name']); ?>!</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($certificates); ?></div>
                <div class="stat-label">Total Certificates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($certificates, function($c) { return $c['status'] === 'verified'; })); ?></div>
                <div class="stat-label">Verified Certificates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum(array_column($certificates, 'verification_count')); ?></div>
                <div class="stat-label">Total Verifications</div>
            </div>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="no-certificates">
                <i class="fas fa-certificate"></i>
                <h2>No Certificates Yet</h2>
                <p>Complete courses to earn your first NFT certificate!</p>
                <a href="course-browser.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-search"></i> Browse Courses
                </a>
            </div>
        <?php else: ?>
            <div class="certificates-grid">
                <?php foreach ($certificates as $cert): ?>
                    <div class="certificate-card">
                        <div class="certificate-image">
                            <?php if ($cert['certificate_image_path'] && file_exists($cert['certificate_image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($cert['certificate_image_path']); ?>" alt="Certificate">
                            <?php else: ?>
                                <i class="fas fa-certificate placeholder"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="certificate-content">
                            <div class="certificate-title"><?php echo htmlspecialchars($cert['course_name']); ?></div>
                            <div class="certificate-course">by <?php echo htmlspecialchars($cert['creator_name']); ?></div>
                            
                            <div class="certificate-details">
                                <div class="detail-label">Issued:</div>
                                <div class="detail-value"><?php echo date('M j, Y', strtotime($cert['issued_at'])); ?></div>
                                
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">
                                    <span class="status-badge status-<?php echo $cert['status']; ?>">
                                        <?php echo ucfirst($cert['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="detail-label">Verifications:</div>
                                <div class="detail-value"><?php echo $cert['verification_count']; ?> times</div>
                            </div>
                            
                            <div class="certificate-actions">
                                <?php if ($cert['verification_code']): ?>
                                    <a href="verify_certificate.php?code=<?php echo $cert['verification_code']; ?>" 
                                       class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> View Public
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-secondary" onclick="copyToClipboard('<?php echo $cert['verification_code']; ?>')">
                                    <i class="fas fa-copy"></i> Copy Code
                                </button>
                                
                                <button class="btn btn-secondary" onclick="showNFTKey(this)" data-key="<?php echo htmlspecialchars($cert['nft_key']); ?>">
                                    <i class="fas fa-key"></i> NFT Key
                                </button>
                            </div>
                            
                            <div class="nft-key" style="display: none;">
                                <strong>NFT Key:</strong><br>
                                <span class="key-text"><?php echo htmlspecialchars($cert['nft_key']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Verification code copied to clipboard!');
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('Verification code copied to clipboard!');
                } catch (err) {
                    alert('Failed to copy verification code');
                }
                document.body.removeChild(textArea);
            });
        }

        function showNFTKey(button) {
            const card = button.closest('.certificate-card');
            const nftKeyDiv = card.querySelector('.nft-key');
            
            if (nftKeyDiv.style.display === 'none') {
                nftKeyDiv.style.display = 'block';
                button.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Key';
            } else {
                nftKeyDiv.style.display = 'none';
                button.innerHTML = '<i class="fas fa-key"></i> NFT Key';
            }
        }
    </script>
</body>
</html>
