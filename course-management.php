<?php
session_start();
include 'db.php';

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get creator info
$creator_query = "SELECT full_name FROM creators WHERE id = ?";
$stmt = $conn->prepare($creator_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$creator_result = $stmt->get_result();
$creator = $creator_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - NFT Learning Platform</title>
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

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666;
        }

        .form-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-upload-area {
            border: 2px dashed #e1e5e9;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: border-color 0.3s, background 0.3s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background: #e7f3ff;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .file-upload-subtext {
            color: #999;
            font-size: 0.9rem;
        }

        .file-info {
            display: none;
            background: #e7f3ff;
            border: 1px solid #667eea;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .file-info.show {
            display: block;
        }

        .file-name {
            font-weight: bold;
            color: #333;
        }

        .file-size {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group .error {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-group.has-error input,
        .form-group.has-error select,
        .form-group.has-error textarea,
        .form-group.has-error .file-upload-area {
            border-color: #dc3545;
        }

        .form-group.has-error .error {
            display: block;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            justify-self: start;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .upload-progress {
            display: none;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .upload-progress.show {
            display: block;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e1e5e9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .success-message,
        .error-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .courses-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .courses-section h2 {
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #fafbfc;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .course-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: #666;
        }

        .course-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .course-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #28a745;
            margin: 1rem 0;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            flex: 1;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .watch-btn {
            background: #28a745;
            color: white;
        }

        .watch-btn:hover {
            background: #218838;
        }

        .edit-btn {
            background: #667eea;
            color: white;
        }

        .edit-btn:hover {
            background: #5a6fd8;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .no-courses {
            text-align: center;
            color: #666;
            padding: 3rem;
            font-style: italic;
        }

        .no-courses i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .course-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🎓 NFT Learning - Creator</div>
            <ul class="nav-links">
                <li><a href="home-creator.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="course-browser-creator.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="course-management.php"><i class="fas fa-plus"></i> Course Management</a></li>
                <li><a href="creator-profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="login.html"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Course Management</h1>
            <p>Create and manage your courses with video content, <?php echo htmlspecialchars($creator['full_name']); ?>!</p>
        </div>

        <!-- Course Creation Form -->
        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i> Create New Course</h2>
            <div id="form-messages"></div>
            
            <!-- Course Form -->
            <form id="courseForm" class="course-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="courseName">Course Name *</label>
                        <input type="text" id="courseName" name="courseName" required>
                        <div class="error">Course name is required (min 3 characters)</div>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile Development">Mobile Development</option>
                            <option value="Data Science">Data Science</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Blockchain">Blockchain</option>
                            <option value="Cybersecurity">Cybersecurity</option>
                            <option value="Game Development">Game Development</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Business">Business</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="error">Please select a category</div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Course Description *</label>
                    <textarea id="description" name="description" placeholder="Describe what students will learn in this course..." required></textarea>
                    <div class="error">Course description is required (min 10 characters)</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (USD) *</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" placeholder="0.00" required>
                        <div class="error">Please enter a valid price</div>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (Hours) *</label>
                        <input type="number" id="duration" name="duration" min="1" max="500" placeholder="10" required>
                        <div class="error">Duration must be between 1 and 500 hours</div>
                    </div>
                </div>

                <!-- Course Thumbnail Upload Section -->
                <div class="form-group full-width">
                    <label for="courseThumbnail">Course Thumbnail *</label>
                    <div class="file-upload-area" onclick="document.getElementById('courseThumbnail').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="file-upload-text">Click to upload thumbnail or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: JPG, PNG, WebP (Max: 5MB, Recommended: 1280x720)</div>
                    </div>
                    <input type="file" id="courseThumbnail" name="courseThumbnail" accept="image/*" style="display: none;" required>
                    <div class="error">Please select a thumbnail image</div>
                    
                    <!-- Thumbnail Preview -->
                    <div class="thumbnail-preview" id="thumbnailPreview" style="display: none;">
                        <img id="thumbnailImg" src="" alt="Course Thumbnail Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 1rem;">
                        <p style="color: #666; margin-top: 0.5rem;">Thumbnail Preview</p>
                    </div>
                </div>

                <!-- Video Upload Section -->
                <div class="form-group full-width">
                    <label for="courseVideo">Course Video *</label>
                    <div class="file-upload-area" onclick="document.getElementById('courseVideo').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="file-upload-text">Click to upload video or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: MP4, WebM, AVI (Max: 500MB)</div>
                    </div>
                    <input type="file" id="courseVideo" name="courseVideo" accept="video/*" style="display: none;" required>
                    <div class="error">Please select a video file</div>
                    
                    <!-- File Info Display -->
                    <div class="file-info" id="fileInfo">
                        <div class="file-name" id="fileName"></div>
                        <div class="file-size" id="fileSize"></div>
                    </div>

                    <!-- Upload Progress -->
                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText">Uploading...</div>
                    </div>
                </div>

                <!-- NFT Certificate Image Upload Section -->
                <div class="form-group full-width">
                    <label for="nftCertificate">NFT Certificate Template *</label>
                    <div class="file-upload-area" onclick="document.getElementById('nftCertificate').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="file-upload-text">Click to upload certificate template or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: PNG, JPG (Max: 10MB, Recommended: 1200x800)</div>
                    </div>
                    <input type="file" id="nftCertificate" name="nftCertificate" accept="image/*" style="display: none;" required>
                    <div class="error">Please select an NFT certificate template</div>
                    
                    <!-- Certificate Preview -->
                    <div class="certificate-preview" id="certificatePreview" style="display: none;">
                        <img id="certificateImg" src="" alt="Certificate Template Preview" style="max-width: 400px; max-height: 300px; border-radius: 8px; margin-top: 1rem; border: 2px solid #ddd;">
                        <p style="color: #666; margin-top: 0.5rem;">Certificate Template Preview - This will be awarded as NFT when learners complete the course</p>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-save"></i> Create Course
                </button>
            </form>
        </div>

        <!-- Courses Grid -->
        <div class="courses-section">
            <h2><i class="fas fa-book"></i> My Courses</h2>
            <div id="coursesGrid" class="courses-grid">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading courses...
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedFile = null;
        let selectedThumbnail = null;
        let selectedCertificate = null;

        // Initialize event listeners when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeFileUpload();
            initializeThumbnailUpload();
            initializeCertificateUpload();
            loadCourses();
        });

        /**
         * Initialize thumbnail upload functionality with drag and drop
         */
        function initializeThumbnailUpload() {
            const thumbnailInput = document.getElementById('courseThumbnail');
            const thumbnailUploadArea = document.querySelector('.file-upload-area');
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const thumbnailImg = document.getElementById('thumbnailImg');

            // Thumbnail input change event
            thumbnailInput.addEventListener('change', function(e) {
                handleThumbnailSelect(e.target.files[0]);
            });

            // Drag and drop events for thumbnail (target the first upload area)
            const thumbnailUploadAreas = document.querySelectorAll('.file-upload-area');
            const thumbnailArea = thumbnailUploadAreas[0]; // First upload area is for thumbnail

            thumbnailArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                thumbnailArea.classList.add('dragover');
            });

            thumbnailArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                thumbnailArea.classList.remove('dragover');
            });

            thumbnailArea.addEventListener('drop', function(e) {
                e.preventDefault();
                thumbnailArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleThumbnailSelect(files[0]);
                }
            });
        }

        /**
         * Handle thumbnail selection and validation
         */
        function handleThumbnailSelect(file) {
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('courseThumbnail'), 'Please select a valid image file (JPG, PNG, WebP)');
                return;
            }

            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('courseThumbnail'), 'Thumbnail size must be less than 5MB');
                return;
            }

            // Store selected thumbnail
            selectedThumbnail = file;

            // Display thumbnail preview
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const thumbnailImg = document.getElementById('thumbnailImg');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnailImg.src = e.target.result;
                thumbnailPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Clear any previous errors
            const formGroup = document.getElementById('courseThumbnail').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        /**
         * Initialize NFT certificate upload functionality
         */
        function initializeCertificateUpload() {
            const certificateInput = document.getElementById('nftCertificate');
            const certificateUploadAreas = document.querySelectorAll('.file-upload-area');
            const certificateArea = certificateUploadAreas[2]; // Third upload area is for certificate
            const certificatePreview = document.getElementById('certificatePreview');
            const certificateImg = document.getElementById('certificateImg');

            // Certificate input change event
            certificateInput.addEventListener('change', function(e) {
                handleCertificateSelect(e.target.files[0]);
            });

            // Drag and drop events for certificate
            certificateArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                certificateArea.classList.add('dragover');
            });

            certificateArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                certificateArea.classList.remove('dragover');
            });

            certificateArea.addEventListener('drop', function(e) {
                e.preventDefault();
                certificateArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleCertificateSelect(files[0]);
                }
            });
        }

        /**
         * Handle certificate template selection and validation
         */
        function handleCertificateSelect(file) {
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('nftCertificate'), 'Please select a valid image file (JPG, PNG)');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('nftCertificate'), 'Certificate image size must be less than 10MB');
                return;
            }

            // Store selected certificate
            selectedCertificate = file;

            // Display certificate preview
            const certificatePreview = document.getElementById('certificatePreview');
            const certificateImg = document.getElementById('certificateImg');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                certificateImg.src = e.target.result;
                certificatePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Clear any previous errors
            const formGroup = document.getElementById('nftCertificate').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        /**
         * Initialize file upload functionality with drag and drop
         */
        function initializeFileUpload() {
            const fileInput = document.getElementById('courseVideo');
            const uploadAreas = document.querySelectorAll('.file-upload-area');
            const uploadArea = uploadAreas[1]; // Second upload area is for video
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            // File input change event
            fileInput.addEventListener('change', function(e) {
                handleFileSelect(e.target.files[0]);
            });

            // Drag and drop events
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });
        }

        /**
         * Handle file selection and validation
         */
        function handleFileSelect(file) {
            if (!file) return;

            // Validate file type
            const allowedTypes = ['video/mp4', 'video/webm', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('courseVideo'), 'Please select a valid video file (MP4, WebM, AVI)');
                return;
            }

            // Validate file size (500MB max)
            const maxSize = 500 * 1024 * 1024; // 500MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('courseVideo'), 'File size must be less than 500MB');
                return;
            }

            // Store selected file
            selectedFile = file;

            // Display file info
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileInfo = document.getElementById('fileInfo');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.add('show');

            // Clear any previous errors
            const formGroup = document.getElementById('courseVideo').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        /**
         * Format file size for display
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        /**
         * Form submission handler
         */
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                submitCourse();
            }
        });

        /**
         * Comprehensive form validation
         */
        function validateForm() {
            let isValid = true;
            const form = document.getElementById('courseForm');
            const formGroups = form.querySelectorAll('.form-group');
            
            // Clear previous errors
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
            
            // Course name validation
            const courseName = document.getElementById('courseName');
            if (courseName.value.trim().length < 3) {
                showFieldError(courseName, 'Course name must be at least 3 characters');
                isValid = false;
            }
            
            // Category validation
            const category = document.getElementById('category');
            if (!category.value) {
                showFieldError(category, 'Please select a category');
                isValid = false;
            }
            
            // Description validation
            const description = document.getElementById('description');
            if (description.value.trim().length < 10) {
                showFieldError(description, 'Description must be at least 10 characters');
                isValid = false;
            }
            
            // Price validation
            const price = document.getElementById('price');
            if (price.value === '' || parseFloat(price.value) < 0) {
                showFieldError(price, 'Please enter a valid price (0 or greater)');
                isValid = false;
            }
            
            // Duration validation
            const duration = document.getElementById('duration');
            if (duration.value === '' || parseInt(duration.value) < 1 || parseInt(duration.value) > 500) {
                showFieldError(duration, 'Duration must be between 1 and 500 hours');
                isValid = false;
            }

            // Thumbnail validation
            const thumbnailInput = document.getElementById('courseThumbnail');
            if (!selectedThumbnail && !thumbnailInput.files[0]) {
                showFieldError(thumbnailInput, 'Please select a thumbnail image');
                isValid = false;
            }

            // Video file validation
            const videoInput = document.getElementById('courseVideo');
            if (!selectedFile && !videoInput.files[0]) {
                showFieldError(videoInput, 'Please select a video file');
                isValid = false;
            }

            // Certificate validation
            const certificateInput = document.getElementById('nftCertificate');
            if (!selectedCertificate && !certificateInput.files[0]) {
                showFieldError(certificateInput, 'Please select an NFT certificate template');
                isValid = false;
            }
            
            return isValid;
        }

        /**
         * Show field-specific error message
         */
        function showFieldError(field, message) {
            const formGroup = field.closest('.form-group');
            const errorDiv = formGroup.querySelector('.error');
            formGroup.classList.add('has-error');
            errorDiv.textContent = message;
        }

        /**
         * Submit course form with file upload
         */
        function submitCourse() {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            uploadProgress.classList.add('show');
            
            // Create form data
            const formData = new FormData();
            formData.append('courseName', document.getElementById('courseName').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('price', document.getElementById('price').value);
            formData.append('duration', document.getElementById('duration').value);
            
            // Add thumbnail file
            const thumbnailFile = selectedThumbnail || document.getElementById('courseThumbnail').files[0];
            formData.append('courseThumbnail', thumbnailFile);
            
            // Add video file
            const videoFile = selectedFile || document.getElementById('courseVideo').files[0];
            formData.append('courseVideo', videoFile);

            // Add certificate file
            const certificateFile = selectedCertificate || document.getElementById('nftCertificate').files[0];
            formData.append('nftCertificate', certificateFile);

            // Create XMLHttpRequest for upload progress tracking
            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressFill.style.width = percentComplete + '%';
                    progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
                }
            });

            // Handle response
            xhr.addEventListener('load', function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    // Reset UI
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    uploadProgress.classList.remove('show');
                    progressFill.style.width = '0%';
                    
                    if (response.success) {
                        showMessage('Course created successfully!', 'success');
                        resetForm();
                        loadCourses(); // Refresh courses grid
                    } else {
                        showMessage(response.message || 'Error creating course', 'error');
                    }
                } catch (error) {
                    showMessage('Error processing response', 'error');
                    resetUI();
                }
            });

            // Handle errors
            xhr.addEventListener('error', function() {
                showMessage('Network error. Please try again.', 'error');
                resetUI();
            });

            // Send request
            xhr.open('POST', 'save_course_with_nft.php', true);
            xhr.send(formData);

            function resetUI() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                uploadProgress.classList.remove('show');
                progressFill.style.width = '0%';
            }
        }

        /**
         * Reset form after successful submission
         */
        function resetForm() {
            document.getElementById('courseForm').reset();
            selectedFile = null;
            selectedThumbnail = null;
            selectedCertificate = null;
            document.getElementById('fileInfo').classList.remove('show');
            document.getElementById('thumbnailPreview').style.display = 'none';
            document.getElementById('certificatePreview').style.display = 'none';
            
            // Clear all error states
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
        }

        /**
         * Display success/error messages
         */
        function showMessage(message, type) {
            const messagesDiv = document.getElementById('form-messages');
            messagesDiv.innerHTML = `<div class="${type}-message">${message}</div>`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messagesDiv.innerHTML = '';
            }, 5000);
        }

        /**
         * Load and display creator's courses
         */
        function loadCourses() {
            const coursesGrid = document.getElementById('coursesGrid');
            coursesGrid.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading courses...</div>';
            
            fetch('get_courses.php')
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debug logging
                if (data.success) {
                    displayCourses(data.courses);
                } else {
                    coursesGrid.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                coursesGrid.innerHTML = '<div class="error-message">Error loading courses. Check console for details.</div>';
                console.error('Error:', error);
            });
        }

        /**
         * Display courses in grid format
         */
        function displayCourses(courses) {
            const coursesGrid = document.getElementById('coursesGrid');
            
            // Debug logging
            console.log('Displaying courses:', courses);
            
            if (courses.length === 0) {
                coursesGrid.innerHTML = `
                    <div class="no-courses">
                        <i class="fas fa-book"></i>
                        <p>You haven't created any courses yet. Create your first course above!</p>
                    </div>
                `;
                return;
            }
            
            const coursesHTML = courses.map(course => {
                // Debug each course
                console.log('Processing course:', course);
                
                const title = course.title && course.title.trim() !== '' ? course.title : 'Untitled Course';
                const description = course.description && course.description.trim() !== '' ? course.description : 'No description available';
                const category = course.category && course.category.trim() !== '' ? course.category : 'General';
                
                return `
                <div class="course-card">
                    ${course.thumbnail ? 
                        `<div style="margin-bottom: 1rem;">
                            <img src="${course.thumbnail}" alt="${escapeHtml(title)}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                        </div>` : 
                        `<div style="width: 100%; height: 150px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; border-radius: 8px; margin-bottom: 1rem;">📚</div>`
                    }
                    <h3>${escapeHtml(title)}</h3>
                    <div class="course-meta">
                        <span><i class="fas fa-tag"></i> ${escapeHtml(category)}</span>
                        <span><i class="fas fa-clock"></i> ${course.duration || 0} hours</span>
                        <span><i class="fas fa-users"></i> ${course.students_enrolled || 0} students</span>
                    </div>
                    <div class="course-price">$${parseFloat(course.price || 0).toFixed(2)}</div>
                    <p style="color: #666; margin-bottom: 1rem;">${escapeHtml(description.substring(0, 100))}${description.length > 100 ? '...' : ''}</p>
                    <div class="course-actions">
                        <button class="action-btn edit-btn" onclick="editCourse(${course.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteCourse(${course.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `}).join('');
            
            coursesGrid.innerHTML = coursesHTML;
        }

        /**
         * Edit course functionality (placeholder)
         */
        function editCourse(courseId) {
            alert('Edit functionality will be implemented. Course ID: ' + courseId);
            // TODO: Implement edit modal or redirect to edit page
        }

        /**
         * Delete course with confirmation
         */
        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                fetch('delete_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ courseId: courseId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Course deleted successfully!', 'success');
                        loadCourses();
                    } else {
                        showMessage(data.message || 'Error deleting course', 'error');
                    }
                })
                .catch(error => {
                    showMessage('Network error. Please try again.', 'error');
                    console.error('Error:', error);
                });
            }
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>