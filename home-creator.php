<?php
session_start();
include 'db.php';

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.html');
    exit();
}

// Get creator data
$user_id = $_SESSION['user_id'];
$creator_query = "SELECT * FROM creators WHERE id = '$user_id'";
$creator_result = mysqli_query($conn, $creator_query);

if ($creator_result && mysqli_num_rows($creator_result) > 0) {
    $creator = mysqli_fetch_assoc($creator_result);
} else {
    echo "<script>alert('Creator not found'); window.location.href='login.html';</script>";
    exit();
}

// Get creator's courses (if courses table exists)
$courses_query = "SELECT * FROM courses WHERE creator_id = '$user_id' ORDER BY created_at DESC";
$courses_result = mysqli_query($conn, $courses_query);

// Calculate total students from all courses
$total_students = 0;
if ($courses_result) {
    $total_students_query = "SELECT SUM(students_enrolled) as total_students FROM courses WHERE creator_id = '$user_id'";
    $total_students_result = mysqli_query($conn, $total_students_query);
    if ($total_students_result) {
        $total_students_row = mysqli_fetch_assoc($total_students_result);
        $total_students = $total_students_row['total_students'] ?? 0;
    }
}

// Count total courses
$course_count_query = "SELECT COUNT(*) as course_count FROM courses WHERE creator_id = '$user_id'";
$course_count_result = mysqli_query($conn, $course_count_query);
$total_courses = 0;
if ($course_count_result) {
    $course_count_row = mysqli_fetch_assoc($course_count_result);
    $total_courses = $course_count_row['course_count'] ?? 0;
}

// Check if columns exist before updating
$check_columns = "SHOW COLUMNS FROM creators LIKE 'total_courses'";
$column_exists = mysqli_query($conn, $check_columns);

if (mysqli_num_rows($column_exists) > 0) {
    // Update creator stats only if columns exist
    $update_stats = "UPDATE creators SET 
                     total_courses = '$total_courses',
                     total_students = '$total_students'
                     WHERE id = '$user_id'";
    mysqli_query($conn, $update_stats);
    
    // Refresh creator data
    $creator_result = mysqli_query($conn, $creator_query);
    $creator = mysqli_fetch_assoc($creator_result);
} else {
    // Use calculated values if columns don't exist
    $creator['total_courses'] = $total_courses;
    $creator['total_students'] = $total_students;
    $creator['total_revenue'] = 0.00;
    $creator['rating'] = 0.00;
    $creator['total_reviews'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Dashboard - NFT Learning Platform</title>
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
            color: #333;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
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
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .welcome-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .creator-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            overflow: hidden;
        }

        .creator-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .welcome-text h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
        }

        .quick-actions {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .quick-actions h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .recent-courses {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .recent-courses h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        .course-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: background 0.3s ease;
        }

        .course-item:hover {
            background: #f8f9fa;
        }

        .course-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .course-info {
            flex: 1;
        }

        .course-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .course-stats {
            color: #666;
            font-size: 0.9rem;
        }

        .no-courses {
            text-align: center;
            color: #666;
            padding: 2rem;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🎓 NFT Learning - Creator</div>
            <ul class="nav-links">
                <li><a href="home-creator.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="course-management.html"><i class="fas fa-plus"></i> Create Course</a></li>
                <li><a href="course-browser-creator.html"><i class="fas fa-book"></i> My Courses</a></li>
                <li><a href="creator-profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="login.html"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="creator-avatar">
                <?php if (!empty($creator['profile_picture']) && file_exists($creator['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($creator['profile_picture']); ?>" alt="Profile Picture">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($creator['full_name']); ?>!</h1>
                <p>Ready to inspire and educate? Let's check your teaching impact.</p>
                <?php if (!empty($creator['expertise'])): ?>
                    <p style="margin-top: 0.5rem;"><strong>Expertise:</strong> <?php echo htmlspecialchars($creator['expertise']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $creator['total_courses']; ?></div>
                <div class="stat-label">Total Courses</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $creator['total_students']; ?></div>
                <div class="stat-label">Total Students</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number">$<?php echo number_format($creator['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?php echo number_format($creator['rating'], 1); ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="course-management.html" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Create New Course
                </a>
                <a href="course-browser-creator.html" class="action-btn">
                    <i class="fas fa-edit"></i>
                    Manage Courses
                </a>
                <a href="creator-profile.php" class="action-btn">
                    <i class="fas fa-user-edit"></i>
                    Edit Profile
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-chart-line"></i>
                    View Analytics
                </a>
            </div>
        </div>

        <!-- Recent Courses -->
        <div class="recent-courses">
            <h2>Your Recent Courses</h2>
            <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                    <div class="course-item">
                        <div class="course-thumbnail">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="course-info">
                            <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                            <div class="course-stats">
                                <?php echo $course['students_enrolled']; ?> students • 
                                Rating: <?php echo number_format($course['rating'], 1); ?>/5 • 
                                Created: <?php echo date('M j, Y', strtotime($course['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-book" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <p>You haven't created any courses yet. Start your teaching journey!</p>
                    <a href="course-management.html" class="action-btn" style="margin-top: 1rem; display: inline-flex;">
                        <i class="fas fa-plus"></i>
                        Create Your First Course
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>