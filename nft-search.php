<?php
session_start();
include 'db.php';

// Handle search functionality
$search_message = '';
$search_error = '';
$nft_key = '';

if ($_POST && isset($_POST['nft_key'])) {
    $nft_key = trim($_POST['nft_key']);
    
    if (!empty($nft_key)) {
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
        $stmt->bind_param("s", $nft_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $certificate = $result->fetch_assoc();
            // Redirect to verify_certificate.php with the verification code
            header("Location: verify_certificate.php?code=" . $certificate['verification_code']);
            exit();
        } else {
            $search_error = "No NFT certificate found for this key. Please verify the NFT key and try again.";
        }
    } else {
        $search_error = "Please enter an NFT key to search.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFT Certificate Search - Learnity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 0 20px;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #764ba2;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .nav-link:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .logout-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Main Content */
        .main-container {
            margin-top: 70px;
            padding: 2rem 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
        }

        /* Search Section */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .search-group label {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        .search-input {
            padding: 1rem 1.5rem;
            font-size: 1rem;
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
            font-family: 'Courier New', monospace;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: #999;
        }

        .search-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-btn,
        .reset-btn {
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px;
        }

        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .reset-btn {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e0e6ed;
        }

        .reset-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .alert-success {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #c6f6d5;
        }

        /* Instructions */
        .instructions {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .instructions h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .instructions ul {
            list-style: none;
            padding: 0;
        }

        .instructions li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .instructions li::before {
            content: "✓";
            color: #667eea;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .search-section {
                padding: 1.5rem;
            }

            .search-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo"><i class="fas fa-graduation-cap"></i> Learnity</a>
            
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'learner'): ?>
                    <li><a href="home-learner.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="course-browser.php" class="nav-link"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="my_certificates.php" class="nav-link"><i class="fas fa-certificate"></i> My Certificates</a></li>
                    <li><a href="learner-profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="login.html" class="nav-link logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.html" class="nav-link"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <header class="page-header">
            <h1>
                <i class="fas fa-search"></i> NFT Certificate Search
            </h1>
            <p>
                Verify and search for blockchain-verified educational certificates. 
                Enter an NFT Key to view detailed certificate information and verification status.
            </p>
        </header>

        <!-- Alert Messages -->
        <?php if (!empty($search_error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php endif; ?>

        <!-- Search Section -->
        <section class="search-section">
            <form class="search-form" method="POST">
                <div class="search-group">
                    <label for="nft_key"><i class="fas fa-key"></i> Enter NFT Key</label>
                    <input 
                        type="text" 
                        id="nft_key" 
                        name="nft_key"
                        class="search-input" 
                        placeholder="e.g., NFT936A6064183ACDA7A64C47E7060FAA0E1757365260"
                        value="<?php echo htmlspecialchars($nft_key); ?>"
                        required
                        autocomplete="off">
                </div>

                <div class="search-buttons">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Certificate
                    </button>
                    <button type="button" class="reset-btn" onclick="document.getElementById('nft_key').value=''; window.location.href='nft-search.php';">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </section>

        <!-- Instructions -->
        <section class="instructions">
            <h3><i class="fas fa-info-circle"></i> How to Search</h3>
            <ul>
                <li>Enter the complete NFT Key in the search box above</li>
                <li>NFT Keys are long alphanumeric strings (e.g., NFT936A6064183ACDA7A64C47E7060FAA0E1757365260)</li>
                <li>If the certificate exists, you'll be redirected to the verification page</li>
                <li>The verification page will show who earned the certificate and when</li>
                <li>All searches are logged for security purposes</li>
            </ul>
        </section>
    </main>

    <script>
        // Auto-focus on the search input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nft_key').focus();
        });

        // Format NFT key input (optional - remove spaces and convert to uppercase)
        document.getElementById('nft_key').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').toUpperCase();
            e.target.value = value;
        });
    </script>
</body>
</html>
