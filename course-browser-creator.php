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
$creator_query = "SELECT full_name FROM creators WHERE id = '$user_id'";
$creator_result = mysqli_query($conn, $creator_query);
$creator = mysqli_fetch_assoc($creator_result);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Browse Courses - EduChain</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background: #f8f9fa;
      }

      /* Navigation Bar */
      .navbar {
        position: sticky;
        top: 0;
        background: white;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
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
        gap: 25px;
        align-items: center;
      }

      .nav-link {
        text-decoration: none;
        color: #333;
        font-weight: 500;
        position: relative;
        transition: color 0.3s ease;
        padding: 8px 16px;
        border-radius: 20px;
      }

      .nav-link:hover {
        color: #2c5aa0;
        background: rgba(44, 90, 160, 0.1);
      }

      .nav-link.active {
        color: #2c5aa0;
        background: rgba(44, 90, 160, 0.15);
        font-weight: 600;
      }

      .logout-btn {
        background: linear-gradient(
          135deg,
          #dc3545 0%,
          #c82333 100%
        ) !important;
        color: white !important;
        padding: 10px 20px !important;
        border-radius: 25px !important;
        transition: all 0.3s ease !important;
      }

      .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        color: white !important;
      }

      .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        padding: 5px;
        background: none;
        border: none;
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
        padding: 40px 20px;
        max-width: 1200px;
        margin: 0 auto;
      }

      .page-header {
        text-align: center;
        margin-bottom: 40px;
      }

      .page-title {
        font-size: 2.5rem;
        font-weight: 600;
        color: #1e3f73;
        margin-bottom: 10px;
      }

      .page-subtitle {
        color: #666;
        font-size: 1.2rem;
      }

      /* Search and Filter Section */
      .filter-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
      }

      .search-bar {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        font-size: 1.1rem;
        margin-bottom: 25px;
        transition: all 0.3s ease;
      }

      .search-bar:focus {
        outline: none;
        border-color: #2c5aa0;
        box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
      }

      .category-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
      }

      .category-btn {
        padding: 10px 20px;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        color: #666;
      }

      .category-btn:hover {
        background: #f8f9fa;
        color: #333;
      }

      .category-btn.active {
        background: #2c5aa0;
        color: white;
        border-color: #2c5aa0;
      }

      /* Course Grid */
      .courses-section {
        margin-bottom: 40px;
      }

      .section-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #1e3f73;
        margin-bottom: 25px;
      }

      .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
      }

      .course-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 2px solid transparent;
      }

      .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border-color: #2c5aa0;
      }

      .course-thumbnail {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: white;
        position: relative;
      }

      .course-category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.9);
        color: #2c5aa0;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
      }

      .course-info {
        padding: 25px;
      }

      .course-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1e3f73;
        line-height: 1.4;
      }

      .course-description {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #666;
      }

      .course-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #28a745;
      }

      .course-actions {
        display: flex;
        gap: 10px;
      }

      .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 20px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
        flex: 1;
      }

      .btn-primary {
        background: linear-gradient(135deg, #2c5aa0 0%, #1e3f73 100%);
        color: white;
      }

      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
      }

      .btn-secondary {
        background: white;
        color: #2c5aa0;
        border: 2px solid #2c5aa0;
      }

      .btn-secondary:hover {
        background: #2c5aa0;
        color: white;
      }

      .btn-enrolled {
        background: #28a745;
        color: white;
      }

      .btn-enrolled:hover {
        background: #218838;
      }

      /* No Results */
      .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #666;
      }

      .no-results h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
      }

      /* Loading State */
      .loading {
        text-align: center;
        padding: 40px;
        color: #666;
      }

      .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #2c5aa0;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }
        100% {
          transform: rotate(360deg);
        }
      }

      /* Success Message */
      .success-message {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        z-index: 2000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
      }

      .success-message.show {
        transform: translateX(0);
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
          gap: 20px;
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

        .courses-grid {
          grid-template-columns: 1fr;
        }

        .category-filters {
          justify-content: flex-start;
          overflow-x: auto;
          padding-bottom: 10px;
        }

        .category-btn {
          white-space: nowrap;
        }

        main {
          padding: 20px 15px;
        }

        .page-title {
          font-size: 2rem;
        }

        .filter-section {
          padding: 20px;
        }
      }

      @media (max-width: 480px) {
        .course-actions {
          flex-direction: column;
        }

        .course-info {
          padding: 20px;
        }

        .success-message {
          right: 10px;
          left: 10px;
          transform: translateY(-100px);
        }

        .success-message.show {
          transform: translateY(0);
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

      /* Focus styles for accessibility */
      .btn:focus,
      .category-btn:focus,
      .search-bar:focus {
        outline: 2px solid #2c5aa0;
        outline-offset: 2px;
      }
    </style>
  </head>
  <body>
    <!-- Navigation -->
    <nav class="navbar">
      <div class="nav-container">
        <a href="#" class="logo">EduChain</a>

        <ul class="nav-menu" id="navMenu">
          <li><a href="home-creator.php" class="nav-link">Home</a></li>
          <li>
            <a href="course-browser-creator.php" class="nav-link active">Courses</a>
          </li>
          <li>
            <a href="course-management.php" class="nav-link"
              >Course Management</a
            >
          </li>
          <li>
            <a href="creator-profile.php" class="nav-link">Profile</a>
          </li>
          <li><a href="login.html" class="nav-link logout-btn">Logout</a></li>
        </ul>

        <button
          class="hamburger"
          id="hamburger"
          aria-label="Toggle navigation menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <!-- Page Header -->
      <header class="page-header">
        <h1 class="page-title">Explore Courses</h1>
        <p class="page-subtitle">
          Discover amazing courses and start your learning journey, <?php echo htmlspecialchars($creator['full_name']); ?>!
        </p>
      </header>

      <!-- Search and Filter Section -->
      <section class="filter-section">
        <input
          type="text"
          class="search-bar"
          id="searchBar"
          placeholder="🔍 Search courses by title, description, or instructor..."
          aria-label="Search courses"
        />

        <div class="category-filters" id="categoryFilters">
          <button class="category-btn active" data-category="all">
            All Courses
          </button>
          <!-- Categories will be loaded dynamically -->
        </div>
      </section>

      <!-- Courses Section -->
      <section class="courses-section">
        <!-- Heading removed per request (All Courses no longer shown) -->

        <div class="loading" id="loadingIndicator" style="display: none">
          <div class="spinner"></div>
          <p>Loading courses...</p>
        </div>

        <div class="courses-grid" id="coursesGrid">
          <!-- Courses will be dynamically inserted here -->
        </div>

        <div class="no-results" id="noResults" style="display: none">
          <h3>No courses found</h3>
          <p>
            Try adjusting your search criteria or browse different categories.
          </p>
        </div>
      </section>
    </main>

    <!-- Success Message -->
    <div class="success-message" id="successMessage">
      Course created successfully! 🎉
    </div>

    <script>
      // Course Management System
      class CourseManager {
        constructor() {
          this.courses = [];
          this.filteredCourses = [];
          this.currentCategory = "all";
          this.currentSearch = "";
          this.categories = [];
          this.init();
        }

        async init() {
          await this.loadCoursesFromDatabase();
          this.loadCategories();
          this.bindEvents();
          this.renderCourses();
        }

        async loadCoursesFromDatabase() {
          try {
            console.log('Loading courses from database...');
            
            // Try get_all_courses.php first
            let response = await fetch('get_all_courses.php');
            let data;
            
            try {
              data = await response.json();
              console.log('get_all_courses.php response:', data);
            } catch (jsonError) {
              console.error('JSON parse error for get_all_courses.php:', jsonError);
              console.log('Raw response:', await response.text());
            }
            
            // If get_all_courses fails, try get_courses.php
            if (!data || !data.success) {
              console.log('get_all_courses.php failed, trying get_courses.php');
              response = await fetch('get_courses.php');
              try {
                data = await response.json();
                console.log('get_courses.php response:', data);
              } catch (jsonError) {
                console.error('JSON parse error for get_courses.php:', jsonError);
                console.log('Raw response:', await response.text());
              }
            }
            
            console.log('Final API Response:', data);
            
            if (data && data.success && data.courses) {
              this.courses = data.courses;
              this.filteredCourses = [...this.courses];
              console.log(`Successfully loaded ${this.courses.length} courses`);
              
              if (this.courses.length === 0) {
                this.showError('No courses found in the database. Please add some courses first.');
              }
            } else {
              console.error('No courses data received:', data);
              this.showError(data?.message || 'No courses found in database. Check the database connection and ensure courses exist.');
            }
          } catch (error) {
            console.error('Network error:', error);
            this.showError('Failed to load courses. Please check console for details and ensure the server is running.');
          }
        }

        loadCategories() {
          // Extract unique categories from courses
          this.categories = [...new Set(this.courses.map(course => course.category))];
          this.renderCategoryFilters();
        }

        renderCategoryFilters() {
          const container = document.getElementById('categoryFilters');
          const allButton = container.querySelector('.category-btn[data-category="all"]');
          
          // Add event listener to the existing "All Courses" button
          if (allButton) {
            console.log('Adding event listener to All Courses button');
            allButton.addEventListener('click', (e) => this.handleCategoryClick(e));
          } else {
            console.error('All Courses button not found!');
          }
          
          // Remove existing category buttons (except "All")
          const existingButtons = container.querySelectorAll('.category-btn:not([data-category="all"])');
          existingButtons.forEach(btn => btn.remove());
          
          // Add category buttons
          this.categories.forEach(category => {
            const button = document.createElement('button');
            button.className = 'category-btn';
            button.dataset.category = category;
            button.textContent = category;
            button.addEventListener('click', (e) => this.handleCategoryClick(e));
            container.appendChild(button);
          });
          
          console.log('Category filters rendered. Total categories:', this.categories.length);
        }

        handleCategoryClick(e) {
          console.log('Category clicked:', e.target.dataset.category);
          
          // Update active state
          document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
          e.target.classList.add('active');
          
          this.currentCategory = e.target.dataset.category;
          this.filterCourses();
        }

        bindEvents() {
          // Search functionality
          const searchBar = document.getElementById("searchBar");
          searchBar.addEventListener("input", (e) => {
            this.currentSearch = e.target.value.toLowerCase().trim();
            this.filterCourses();
          });

          // Mobile menu toggle
          const hamburger = document.getElementById("hamburger");
          const navMenu = document.getElementById("navMenu");

          hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
          });

          // Close mobile menu when clicking on a link
          document.querySelectorAll(".nav-link").forEach((link) => {
            link.addEventListener("click", () => {
              hamburger.classList.remove("active");
              navMenu.classList.remove("active");
            });
          });

          // Close mobile menu when clicking outside
          document.addEventListener("click", (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
              hamburger.classList.remove("active");
              navMenu.classList.remove("active");
            }
          });
        }

        filterCourses() {
          this.showLoading();

          // Simulate loading delay for better UX
          setTimeout(() => {
            this.filteredCourses = this.courses.filter((course) => {
              const matchesCategory =
                this.currentCategory === "all" ||
                course.category === this.currentCategory;
              const matchesSearch =
                !this.currentSearch ||
                course.title.toLowerCase().includes(this.currentSearch) ||
                course.description.toLowerCase().includes(this.currentSearch) ||
                course.instructor.toLowerCase().includes(this.currentSearch);

              return matchesCategory && matchesSearch;
            });

            this.hideLoading();
            this.renderCourses();
          }, 300);
        }

        renderCourses() {
          const container = document.getElementById("coursesGrid");
          const noResults = document.getElementById("noResults");

          console.log('Rendering courses:', this.filteredCourses.length);

          if (this.filteredCourses.length === 0) {
            container.style.display = "none";
            noResults.style.display = "block";
            return;
          }

          container.style.display = "grid";
          noResults.style.display = "none";

          container.innerHTML = this.filteredCourses
            .map((course) => {
              return this.createCourseCard(course);
            })
            .join("");
        }

        createCourseCard(course) {
          const instructor = course.creator_name || course.instructor || "Unknown Instructor";
          
          return `
                    <article class="course-card" data-course-id="${course.id}">
                        <div class="course-thumbnail">
                            ${course.thumbnail ? 
                                `<img src="${course.thumbnail}" alt="${this.escapeHtml(course.title)}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">` : 
                                `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; border-radius: 8px;">📘</div>`
                            }
                            <span class="course-category-badge">${this.escapeHtml(course.category)}</span>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">${this.escapeHtml(course.title)}</h3>
                            <p class="course-description">${this.escapeHtml(course.description)}</p>
                            <div class="course-meta">
                                <span>👨‍🏫 ${this.escapeHtml(instructor)}</span>
                                <span class="course-price">${
                                  parseFloat(course.price) === 0
                                    ? "Free"
                                    : "$" + parseFloat(course.price).toFixed(2)
                                }</span>
                            </div>
                        </div>
                    </article>
                `;
        }

        showError(message) {
          const container = document.getElementById('coursesGrid');
          container.style.display = 'block';
          container.innerHTML = `
            <div style="background: #dc3545; color: white; padding: 1rem; border-radius: 8px; text-align: center; grid-column: 1 / -1;">
              <h3>Error Loading Courses</h3>
              <p>${message}</p>
            </div>
          `;
        }

        showLoading() {
          document.getElementById("loadingIndicator").style.display = "block";
          document.getElementById("coursesGrid").style.display = "none";
          document.getElementById("noResults").style.display = "none";
        }

        hideLoading() {
          document.getElementById("loadingIndicator").style.display = "none";
        }

        escapeHtml(text) {
          const div = document.createElement("div");
          div.textContent = text || '';
          return div.innerHTML;
        }
      }

      // Initialize course manager when DOM is loaded
      document.addEventListener("DOMContentLoaded", () => {
        new CourseManager();
      });

      // Keyboard accessibility
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          const hamburger = document.getElementById("hamburger");
          const navMenu = document.getElementById("navMenu");
          hamburger.classList.remove("active");
          navMenu.classList.remove("active");
        }
      });
    </script>
  </body>
</html>
