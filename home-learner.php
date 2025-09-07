<?php
session_start();
include 'db.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Learner';

// Get learner info
$learner_query = "SELECT full_name FROM learners WHERE id = '$user_id'";
$learner_result = mysqli_query($conn, $learner_query);
$learner = mysqli_fetch_assoc($learner_result);
if ($learner) {
    $user_name = $learner['full_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduChain - Learner Dashboard</title>
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
            background: #f8f9fa;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            color: #2c5aa0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #1e3f73;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
            padding: 5px 0;
        }

        .nav-link:hover {
            color: #2c5aa0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #2c5aa0;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .logout-btn::after {
            display: none;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        /* Main Content */
        main {
            margin-top: 70px;
            min-height: calc(100vh - 140px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(255, 107, 53, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        /* Categories Section */
        .categories {
            padding: 80px 20px;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #1e3f73;
            margin-bottom: 50px;
        }

        .categories-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #2c5aa0;
        }

        .category-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #2c5aa0;
        }

        .category-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e3f73;
        }

        .category-card p {
            color: #666;
            margin-bottom: 20px;
        }

        .explore-btn {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3f73 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .explore-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
        }

        /* Popular Courses Section */
        .popular-courses {
            padding: 80px 20px;
            background: #f8f9fa;
        }

        .courses-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            height: 200px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #2c5aa0;
        }

        .course-content {
            padding: 25px;
        }

        .course-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1e3f73;
        }

        .course-instructor {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .course-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stars {
            color: #ffc107;
        }

        .rating-number {
            color: #666;
            font-size: 0.9rem;
        }

        .course-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c5aa0;
        }

        /* Footer */
        footer {
            background: #1e3f73;
            color: white;
            text-align: center;
            padding: 40px 20px;
        }

        footer p {
            margin-bottom: 20px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                gap: 30px;
                padding-top: 50px;
                transition: left 0.3s ease;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-menu.active {
                left: 0;
            }

            .hamburger {
                display: flex;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .categories-grid,
            .courses-grid {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 60px 15px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .categories,
            .popular-courses {
                padding: 60px 15px;
            }

            .category-card,
            .course-content {
                padding: 20px;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">EduChain</a>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="home-learner.php" class="nav-link">Home</a></li>
                <li><a href="course-browser.php" class="nav-link">Courses</a></li>
                <li><a href="nft-search.html" class="nav-link">Search NFT</a></li>
                <li><a href="learner-profile.php" class="nav-link">Profile</a></li>
                <li><a href="login.html" class="nav-link logout-btn">Logout</a></li>
            </ul>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Continue your learning journey with thousands of courses, earn blockchain certificates, and advance your career with EduChain's innovative learning platform.</p>
                <a href="course-browser.php" class="cta-button">Browse Courses</a>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories" id="categories">
            <h2 class="section-title">Explore Categories</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <div class="category-icon">💻</div>
                    <h3>Web Development</h3>
                    <p>Learn HTML, CSS, JavaScript, React, and more to build modern websites and applications.</p>
                    <button class="explore-btn" onclick="alert('Exploring Web Development courses...')">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📱</div>
                    <h3>Mobile Development</h3>
                    <p>Create iOS and Android apps using React Native, Flutter, Swift, and Kotlin.</p>
                    <button class="explore-btn" onclick="alert('Exploring Mobile Development courses...')">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">🔗</div>
                    <h3>Blockchain</h3>
                    <p>Master cryptocurrency, smart contracts, DeFi, and blockchain development.</p>
                    <button class="explore-btn" onclick="alert('Exploring Blockchain courses...')">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📊</div>
                    <h3>Data Science</h3>
                    <p>Analyze data with Python, R, machine learning, and artificial intelligence.</p>
                    <button class="explore-btn" onclick="alert('Exploring Data Science courses...')">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">🎨</div>
                    <h3>Design</h3>
                    <p>Learn UI/UX design, graphic design, and digital marketing strategies.</p>
                    <button class="explore-btn" onclick="alert('Exploring Design courses...')">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">💼</div>
                    <h3>Business</h3>
                    <p>Develop entrepreneurship, management, and leadership skills for success.</p>
                    <button class="explore-btn" onclick="alert('Exploring Business courses...')">Explore</button>
                </div>
            </div>
        </section>

        <!-- Popular Courses Section -->
        <section class="popular-courses" id="courses">
            <h2 class="section-title">Popular Courses</h2>
            <div class="courses-grid">
                <div class="course-card">
                    <div class="course-image">🚀</div>
                    <div class="course-content">
                        <h3 class="course-title">Complete Web Development Bootcamp</h3>
                        <p class="course-instructor">By Dr. Angela Yu</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-number">4.8 (125,430)</span>
                        </div>
                        <div class="course-price">$89.99</div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">⚛️</div>
                    <div class="course-content">
                        <h3 class="course-title">React - The Complete Guide</h3>
                        <p class="course-instructor">By Maximilian Schwarzmüller</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-number">4.7 (89,340)</span>
                        </div>
                        <div class="course-price">$94.99</div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">🔗</div>
                    <div class="course-content">
                        <h3 class="course-title">Blockchain A-Z: Build a Blockchain</h3>
                        <p class="course-instructor">By Kirill Eremenko</p>
                        <div class="course-rating">
                            <span class="stars">★★★★☆</span>
                            <span class="rating-number">4.5 (23,567)</span>
                        </div>
                        <div class="course-price">$129.99</div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">🐍</div>
                    <div class="course-content">
                        <h3 class="course-title">Python for Data Science</h3>
                        <p class="course-instructor">By Jose Portilla</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-number">4.6 (67,890)</span>
                        </div>
                        <div class="course-price">$79.99</div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">🎨</div>
                    <div class="course-content">
                        <h3 class="course-title">UI/UX Design Masterclass</h3>
                        <p class="course-instructor">By Daniel Walter Scott</p>
                        <div class="course-rating">
                            <span class="stars">★★★★☆</span>
                            <span class="rating-number">4.4 (45,123)</span>
                        </div>
                        <div class="course-price">$99.99</div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">📱</div>
                    <div class="course-content">
                        <h3 class="course-title">Flutter & Dart Complete Guide</h3>
                        <p class="course-instructor">By Maximilian Schwarzmüller</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-number">4.7 (34,567)</span>
                        </div>
                        <div class="course-price">$109.99</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <p>&copy; <span id="currentYear"></span> EduChain. All rights reserved.</p>
    </footer>

    <script>
        // Set current year
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 70;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>