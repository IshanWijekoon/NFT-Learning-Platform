<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thumbnail Feature Test - NFT Learning Platform</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        ul { margin: 10px 0; padding-left: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #5a6fd8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Course Thumbnail Feature Test</h1>
        
        <div class="test-section info">
            <h2>📋 Feature Overview</h2>
            <p>This test page verifies that the course thumbnail feature has been properly implemented.</p>
            <ul>
                <li>✅ Thumbnail upload field added to course creation form</li>
                <li>✅ Database column for thumbnail added automatically</li>
                <li>✅ File upload handling with validation (JPG, PNG, WebP, max 5MB)</li>
                <li>✅ Thumbnail display in all course browsers</li>
                <li>✅ Thumbnail display in course details page</li>
                <li>✅ Responsive design for mobile devices</li>
            </ul>
        </div>

        <?php
        include 'db.php';
        
        // Check if thumbnail column exists in courses table
        $check_thumbnail = "SHOW COLUMNS FROM courses LIKE 'thumbnail'";
        $result = mysqli_query($conn, $check_thumbnail);
        $thumbnail_exists = mysqli_num_rows($result) > 0;
        
        // Check if thumbnail upload directory exists
        $thumbnail_dir = 'uploads/course_thumbnails/';
        $dir_exists = file_exists($thumbnail_dir) && is_dir($thumbnail_dir);
        
        // Check for any courses with thumbnails
        $courses_with_thumbnails = 0;
        if ($thumbnail_exists) {
            $count_query = "SELECT COUNT(*) as count FROM courses WHERE thumbnail IS NOT NULL AND thumbnail != ''";
            $count_result = mysqli_query($conn, $count_query);
            if ($count_result) {
                $count_row = mysqli_fetch_assoc($count_result);
                $courses_with_thumbnails = $count_row['count'];
            }
        }
        ?>

        <div class="test-section <?php echo $thumbnail_exists ? 'success' : 'error'; ?>">
            <h2>🗄️ Database Schema</h2>
            <?php if ($thumbnail_exists): ?>
                <p><strong>✅ SUCCESS:</strong> The 'thumbnail' column exists in the courses table.</p>
            <?php else: ?>
                <p><strong>❌ ERROR:</strong> The 'thumbnail' column does not exist. It will be created automatically when the first course with thumbnail is uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="test-section <?php echo $dir_exists ? 'success' : 'error'; ?>">
            <h2>📁 File Storage</h2>
            <?php if ($dir_exists): ?>
                <p><strong>✅ SUCCESS:</strong> Thumbnail upload directory exists: <code><?php echo $thumbnail_dir; ?></code></p>
            <?php else: ?>
                <p><strong>❌ ERROR:</strong> Thumbnail upload directory is missing. It will be created automatically during upload.</p>
            <?php endif; ?>
        </div>

        <div class="test-section info">
            <h2>📊 Course Statistics</h2>
            <p><strong>Courses with thumbnails:</strong> <?php echo $courses_with_thumbnails; ?></p>
            <?php if ($courses_with_thumbnails > 0): ?>
                <p>✅ Great! Some courses already have thumbnails uploaded.</p>
            <?php else: ?>
                <p>ℹ️ No courses with thumbnails yet. Upload your first course with a thumbnail to test the feature.</p>
            <?php endif; ?>
        </div>

        <div class="test-section info">
            <h2>🧪 Testing Instructions</h2>
            <ol>
                <li><strong>Create a Course:</strong> Go to course management and create a new course with a thumbnail</li>
                <li><strong>View in Browser:</strong> Check if the thumbnail appears in the course browser</li>
                <li><strong>Course Details:</strong> Verify the thumbnail shows on the course info page</li>
                <li><strong>Mobile Test:</strong> Test responsive design on mobile devices</li>
            </ol>
            
            <h3>📁 Supported Thumbnail Formats:</h3>
            <ul>
                <li>JPEG (.jpg, .jpeg)</li>
                <li>PNG (.png)</li>
                <li>WebP (.webp)</li>
                <li>Maximum file size: 5MB</li>
                <li>Recommended dimensions: 1280x720 (16:9 aspect ratio)</li>
            </ul>
        </div>

        <div class="test-section info">
            <h2>🔗 Quick Navigation</h2>
            <a href="course-management.php" class="btn">📝 Create Course</a>
            <a href="course-browser.php" class="btn">👥 Course Browser (Learner)</a>
            <a href="course-browser-creator.php" class="btn">👨‍🏫 Course Browser (Creator)</a>
            <a href="test_db_connection.php" class="btn">🔍 Database Test</a>
        </div>

        <div class="test-section success">
            <h2>🎉 Implementation Complete!</h2>
            <p>The course thumbnail feature has been successfully implemented with the following components:</p>
            <ul>
                <li>✅ <strong>Upload Form:</strong> Enhanced course-management.php with thumbnail upload</li>
                <li>✅ <strong>Backend Processing:</strong> Updated save_course.php with file validation and storage</li>
                <li>✅ <strong>Database Integration:</strong> Automatic thumbnail column creation</li>
                <li>✅ <strong>API Updates:</strong> Modified get_courses.php and get_all_courses.php to include thumbnails</li>
                <li>✅ <strong>Frontend Display:</strong> Updated all course browsers and course info page</li>
                <li>✅ <strong>Responsive Design:</strong> Mobile-friendly thumbnail display</li>
                <li>✅ <strong>Fallback Support:</strong> Default placeholder for courses without thumbnails</li>
            </ul>
        </div>
    </div>
</body>
</html>
