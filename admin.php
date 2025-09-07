<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT full_name, email FROM admins WHERE id = '$admin_id'";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learning Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-260px);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #fff;
            color: white;
        }

        .nav-icon {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 70px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
            transition: left 0.3s ease;
        }

        .header.full-width {
            left: 0;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #667eea;
            cursor: pointer;
            padding: 5px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 25px;
            padding: 8px 20px;
            width: 300px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            flex: 1;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .search-icon {
            color: #666;
            margin-right: 10px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification {
            position: relative;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #666;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .notification:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
        }

        /* Notification Dropdown Styles */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
            animation: notificationSlideIn 0.3s ease-out;
        }

        @keyframes notificationSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 20px 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: #2c5aa0;
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s ease;
        }

        .mark-all-read:hover {
            background: rgba(44, 90, 160, 0.1);
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid #f8f9fa;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: rgba(44, 90, 160, 0.02);
            border-left: 3px solid #2c5aa0;
        }

        .notification-item.read {
            opacity: 0.7;
        }

        .notification-icon {
            font-size: 20px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
        }

        .notification-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
        }

        .btn-small {
            padding: 4px 8px;
            font-size: 11px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 50px;
        }

        .btn-small.btn-primary {
            background: #2c5aa0;
            color: white;
        }

        .btn-small.btn-primary:hover {
            background: #1e3f73;
        }

        .btn-small.btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .btn-small.btn-secondary:hover {
            background: #e9ecef;
        }

        .notification-footer {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            text-align: center;
        }

        .view-all-notifications {
            background: none;
            border: none;
            color: #2c5aa0;
            font-size: 14px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s ease;
            width: 100%;
        }

        .view-all-notifications:hover {
            background: rgba(44, 90, 160, 0.1);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-name {
            font-weight: 500;
            color: #333;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }

        .main-content.full-width {
            margin-left: 0;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 300;
            color: #333;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card.users {
            border-left-color: #667eea;
        }

        .stat-card.courses {
            border-left-color: #28a745;
        }

        .stat-card.nfts {
            border-left-color: #ffc107;
        }

        .stat-card.completion {
            border-left-color: #17a2b8;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.users {
            background: #667eea;
        }

        .stat-icon.courses {
            background: #28a745;
        }

        .stat-icon.nfts {
            background: #ffc107;
        }

        .stat-icon.completion {
            background: #17a2b8;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .trend-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: #28a745;
            margin-top: 5px;
        }

        /* Activity Table */
        .activity-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 500;
            color: #333;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 15px 30px;
            text-align: left;
            border-bottom: 1px solid #f1f3f4;
        }

        .activity-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-table td {
            color: #666;
        }

        .activity-table tbody tr:hover {
            background: #f8f9fa;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .action-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .action-badge.login {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-badge.course {
            background: #d4edda;
            color: #155724;
        }

        .action-badge.nft {
            background: #fff3cd;
            color: #856404;
        }

        /* Pagination */
        .pagination {
            padding: 20px 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .page-btn {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .page-btn:hover,
        .page-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-260px);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .header {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger {
                display: block;
            }

            .search-box {
                width: 200px;
            }

            .admin-name {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .activity-table {
                font-size: 0.9rem;
            }

            .activity-table th,
            .activity-table td {
                padding: 12px 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .main-content {
                padding: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .search-box {
                width: 150px;
            }

            .header {
                padding: 0 15px;
            }

            .header-right {
                gap: 10px;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
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

        /* Users Section Styles */
        .content-section {
            width: 100%;
        }

        .user-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn:hover {
            color: #2c5aa0;
            background: rgba(44, 90, 160, 0.05);
        }

        .tab-btn.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
            background: rgba(44, 90, 160, 0.1);
        }

        .users-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .users-table td {
            font-size: 14px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 12px;
            color: #666;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-badge.creator {
            background: #e3f2fd;
            color: #1976d2;
        }

        .role-badge.learner {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-badge.inactive {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-badge.suspended {
            background: #ffebee;
            color: #d32f2f;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s ease;
            font-size: 14px;
        }

        .btn-icon:hover {
            background: #f0f0f0;
        }

        .btn-icon.delete:hover {
            background: #ffebee;
        }

        .stat-icon.creators {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        }

        .stat-icon.learners {
            background: linear-gradient(135deg, #9c27b0 0%, #6a1b9a 100%);
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        }

        .stat-icon.new {
            background: linear-gradient(135deg, #ff9800 0%, #e65100 100%);
        }

        .activity-chart-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }

        .chart-placeholder {
            text-align: center;
            color: #666;
        }

        .chart-placeholder p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .chart-placeholder small {
            font-size: 14px;
            color: #999;
        }

        /* Responsive adjustments for users table */
        @media (max-width: 768px) {
            .users-table {
                font-size: 12px;
            }
            
            .users-table th,
            .users-table td {
                padding: 10px 8px;
            }
            
            .header-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        /* Courses Section Styles */
        .course-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0;
            overflow-x: auto;
        }

        .courses-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .course-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .course-image {
            position: relative;
            height: 180px;
            overflow: hidden;
        }

        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-status {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .course-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .course-status.approved {
            background: #d4edda;
            color: #155724;
        }

        .course-status.rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .course-status.published {
            background: #d1ecf1;
            color: #0c5460;
        }

        .course-info {
            padding: 20px;
        }

        .course-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .course-creator {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
        }

        .course-description {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .course-meta span {
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .course-details {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .course-details p {
            margin-bottom: 5px;
        }

        .course-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-primary {
            background: #2c5aa0;
            color: white;
        }

        .btn-primary:hover {
            background: #1e3f73;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }

        .stat-icon.approved {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .stat-icon.rejected {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .stat-icon.published {
            background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%);
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 20px 25px;
            border-top: 1px solid #e0e0e0;
        }

        .review-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .review-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .review-tab:hover {
            color: #2c5aa0;
            background: rgba(44, 90, 160, 0.05);
        }

        .review-tab.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .course-overview p {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .content-checklist {
            margin-bottom: 20px;
        }

        .content-checklist h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .content-checklist label {
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .content-checklist input[type="checkbox"] {
            margin-right: 10px;
        }

        .rating-section {
            margin-bottom: 20px;
        }

        .rating-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .rating-stars {
            display: flex;
            gap: 5px;
        }

        .star {
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .star:hover {
            transform: scale(1.2);
        }

        .star.selected {
            color: #ffc107;
        }

        .feedback-section textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .feedback-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        /* Responsive adjustments for courses */
        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .course-actions {
                justify-content: center;
            }
            
            .course-meta {
                justify-content: center;
            }
            
            .modal-content {
                margin: 10px;
                max-height: 95vh;
            }
            
            .course-tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        /* Analytics Section Styles */
        .analytics-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .time-selector {
            display: flex;
            gap: 10px;
        }

        .time-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .time-btn:hover {
            background: #f8f9fa;
            border-color: #2c5aa0;
        }

        .time-btn.active {
            background: #2c5aa0;
            color: white;
            border-color: #2c5aa0;
        }

        .export-controls {
            display: flex;
            gap: 10px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .analytics-grid-secondary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .analytics-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .chart-controls select,
        .chart-filter {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-placeholder {
            text-align: center;
            color: #666;
            width: 100%;
        }

        .chart-placeholder.large {
            height: 100%;
        }

        .chart-mock {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .chart-bars {
            display: flex;
            align-items: end;
            gap: 8px;
            margin-bottom: 20px;
            height: 120px;
        }

        .bar {
            width: 20px;
            background: linear-gradient(180deg, #2c5aa0 0%, #1e3f73 100%);
            border-radius: 4px 4px 0 0;
            animation: barGrow 1s ease-out;
        }

        @keyframes barGrow {
            from {
                height: 0;
            }
            to {
                height: var(--height);
            }
        }

        .chart-placeholder p {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .chart-placeholder small {
            font-size: 12px;
            color: #999;
        }

        .demographics-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .demo-section h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .country-list,
        .device-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .country-item,
        .device-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }

        .flag,
        .device-icon {
            font-size: 16px;
            width: 20px;
        }

        .country-name,
        .device-name {
            flex: 1;
            font-size: 14px;
        }

        .country-percentage,
        .device-percentage {
            font-weight: 600;
            color: #2c5aa0;
            font-size: 14px;
        }

        .performance-content {
            display: flex;
            flex-direction: column;
        }

        .performance-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .metric-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .metric-icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
        }

        .metric-details {
            display: flex;
            flex-direction: column;
        }

        .metric-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
        }

        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .realtime-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #28a745;
            font-size: 14px;
            font-weight: 500;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .realtime-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .realtime-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c5aa0;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .realtime-feed h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .activity-feed {
            max-height: 200px;
            overflow-y: auto;
        }

        .feed-item {
            display: flex;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .feed-time {
            color: #666;
            min-width: 50px;
        }

        .feed-action {
            flex: 1;
        }

        .feed-location {
            color: #999;
        }

        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            margin-top: 20px;
        }

        .page-list,
        .source-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .page-item,
        .source-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .page-info,
        .source-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .source-info {
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .page-title,
        .source-name {
            font-weight: 600;
            color: #333;
        }

        .page-url {
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        .page-views,
        .source-percentage {
            font-weight: 600;
            color: #2c5aa0;
        }

        .source-icon {
            font-size: 16px;
        }

        .analytics-table-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .table-filter {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-size: 14px;
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .analytics-table th,
        .analytics-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .analytics-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .analytics-table td {
            font-size: 14px;
        }

        .table-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .item-title {
            font-weight: 600;
            color: #333;
        }

        .item-url {
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        .stat-icon.traffic {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        .stat-icon.pageviews {
            background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%);
        }

        .stat-icon.sessions {
            background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
        }

        .stat-icon.bounce {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        }

        /* Responsive adjustments for analytics */
        @media (max-width: 1024px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .demographics-content {
                grid-template-columns: 1fr;
            }
            
            .performance-metrics {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .analytics-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .time-selector {
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .analytics-grid-secondary {
                grid-template-columns: 1fr;
            }
            
            .realtime-stats {
                justify-content: center;
            }
            
            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
       <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>EduChain</h2>
            <p>Admin Panel</p>
        </div>
        
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="admin.php" class="nav-link">
                    <span class="nav-icon">🏠</span>
                    Home
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link active" data-tab="dashboard">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-tab="users">
                    <span class="nav-icon">👥</span>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-tab="courses">
                    <span class="nav-icon">📚</span>
                    Courses
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-tab="analytics">
                    <span class="nav-icon">📈</span>
                    Analytics
                </a>
            </li>
            <li class="nav-item">
                <a href="login.html" class="nav-link">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </li>
        </ul>
    </nav>

        <!-- Header -->
    <header class="header" id="header">
        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <span class="search-icon">🔍</span>
        </div>
        
        <div class="header-right">
            <div class="notification" id="notificationIcon">
                <span>🔔</span>
                <span class="notification-badge" id="notificationCount">3</span>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item unread" data-id="1">
                            <div class="notification-icon">📚</div>
                            <div class="notification-content">
                                <div class="notification-title">New Course Submitted</div>
                                <div class="notification-message">"Advanced React Development" has been submitted for review</div>
                                <div class="notification-time">2 minutes ago</div>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-primary" onclick="viewCourse('course-1')">Review</button>
                                <button class="btn-small btn-secondary" onclick="markAsRead(1)">×</button>
                            </div>
                        </div>
                        
                        <div class="notification-item unread" data-id="2">
                            <div class="notification-icon">👤</div>
                            <div class="notification-content">
                                <div class="notification-title">New User Registration</div>
                                <div class="notification-message">Sarah Johnson has registered as a creator</div>
                                <div class="notification-time">15 minutes ago</div>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-primary" onclick="viewUser('user-1')">View</button>
                                <button class="btn-small btn-secondary" onclick="markAsRead(2)">×</button>
                            </div>
                        </div>
                        
                        <div class="notification-item unread" data-id="3">
                            <div class="notification-icon">⚠️</div>
                            <div class="notification-content">
                                <div class="notification-title">High Traffic Alert</div>
                                <div class="notification-message">Website traffic has increased by 150% in the last hour</div>
                                <div class="notification-time">1 hour ago</div>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-primary" onclick="viewAnalytics()">View Analytics</button>
                                <button class="btn-small btn-secondary" onclick="markAsRead(3)">×</button>
                            </div>
                        </div>
                        
                        <div class="notification-item read" data-id="4">
                            <div class="notification-icon">✅</div>
                            <div class="notification-content">
                                <div class="notification-title">Course Approved</div>
                                <div class="notification-message">"Python Machine Learning" has been approved and published</div>
                                <div class="notification-time">3 hours ago</div>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-secondary" onclick="markAsRead(4)">×</button>
                            </div>
                        </div>
                        
                        <div class="notification-item read" data-id="5">
                            <div class="notification-icon">💰</div>
                            <div class="notification-content">
                                <div class="notification-title">Revenue Milestone</div>
                                <div class="notification-message">Platform revenue has reached $10,000 this month</div>
                                <div class="notification-time">1 day ago</div>
                            </div>
                            <div class="notification-actions">
                                <button class="btn-small btn-secondary" onclick="markAsRead(5)">×</button>
                            </div>
                        </div>
                    </div>
                    <div class="notification-footer">
                        <button class="view-all-notifications" onclick="viewAllNotifications()">View All Notifications</button>
                    </div>
                </div>
            </div>
            
            <div class="admin-info">
                <div class="admin-avatar"><?php echo strtoupper(substr($admin['full_name'] ?? 'A', 0, 1)); ?></div>
                <span class="admin-name"><?php echo htmlspecialchars($admin['full_name'] ?? 'Administrator'); ?></span>
            </div>
        </div>
    </header>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

       <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Dashboard Content -->
        <div class="content-section" id="dashboardContent">
            <div class="page-title">
                <h1>Admin Dashboard</h1>
                <p>Monitor and manage your learning platform</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-header">
                        <div class="stat-icon users">👥</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card courses">
                    <div class="stat-header">
                        <div class="stat-icon courses">📚</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Active Courses</div>
                </div>

                <div class="stat-card nfts">
                    <div class="stat-header">
                        <div class="stat-icon nfts">🏆</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">NFT Certificates</div>
                </div>

                <div class="stat-card completion">
                    <div class="stat-header">
                        <div class="stat-icon completion">📊</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="activity-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Activity</h2>
                </div>
                
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Course</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">SJ</div>
                                    <span>Sarah Johnson</span>
                                </div>
                            </td>
                            <td><span class="action-badge login">Login</span></td>
                            <td>-</td>
                            <td>2 min ago</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">MC</div>
                                    <span>Mike Chen</span>
                                </div>
                            </td>
                            <td><span class="action-badge course">Completed</span></td>
                            <td>React Fundamentals</td>
                            <td>1 hour ago</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">ED</div>
                                    <span>Emily Davis</span>
                                </div>
                            </td>
                            <td><span class="action-badge nft">NFT Earned</span></td>
                            <td>Web Development</td>
                            <td>3 hours ago</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">AR</div>
                                    <span>Alex Rodriguez</span>
                                </div>
                            </td>
                            <td><span class="action-badge login">Registered</span></td>
                            <td>-</td>
                            <td>1 day ago</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <button class="page-btn" disabled>Previous</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">Next</button>
                </div>
            </div>
        </div>

        <!-- Users Content -->
        <div class="content-section" id="usersContent" style="display: none;">
            <div class="page-title">
                <h1>User Management</h1>
                <p>Manage creators, learners, and monitor their activities</p>
            </div>

            <!-- User Stats -->
            <div class="stats-grid">
                <div class="stat-card creators">
                    <div class="stat-header">
                        <div class="stat-icon creators">🎨</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Creators</div>
                </div>

                <div class="stat-card learners">
                    <div class="stat-header">
                        <div class="stat-icon learners">📖</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Learners</div>
                </div>

                <div class="stat-card active-users">
                    <div class="stat-header">
                        <div class="stat-icon active">🟢</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Active Today</div>
                </div>

                <div class="stat-card new-users">
                    <div class="stat-header">
                        <div class="stat-icon new">⭐</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">New This Week</div>
                </div>
            </div>

            <!-- User Type Tabs -->
            <div class="user-tabs">
                <button class="tab-btn active" data-tab="all">All Users</button>
                <button class="tab-btn" data-tab="creators">Creators</button>
                <button class="tab-btn" data-tab="learners">Learners</button>
            </div>

            <!-- Users Table -->
            <div class="users-section">
                <div class="section-header">
                    <h2 class="section-title">User List</h2>
                    <div class="header-actions">
                        <div class="search-box">
                            <input type="text" placeholder="Search users..." id="userSearch">
                            <span class="search-icon">🔍</span>
                        </div>
                        <select class="filter-select">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Courses</th>
                            <th>Join Date</th>
                            <th>Last Activity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Sample Data - Will be populated dynamically -->
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">SJ</div>
                                    <div class="user-details">
                                        <span class="user-name">Sarah Johnson</span>
                                        <span class="user-email">sarah@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge creator">Creator</span></td>
                            <td>3 Created</td>
                            <td>Jan 15, 2025</td>
                            <td>2 hours ago</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="View Profile">👁️</button>
                                    <button class="btn-icon edit" title="Edit User">✏️</button>
                                    <button class="btn-icon delete" title="Suspend User">🚫</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">MC</div>
                                    <div class="user-details">
                                        <span class="user-name">Mike Chen</span>
                                        <span class="user-email">mike@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge learner">Learner</span></td>
                            <td>5 Enrolled</td>
                            <td>Feb 22, 2025</td>
                            <td>1 day ago</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="View Profile">👁️</button>
                                    <button class="btn-icon edit" title="Edit User">✏️</button>
                                    <button class="btn-icon delete" title="Suspend User">🚫</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">ED</div>
                                    <div class="user-details">
                                        <span class="user-name">Emily Davis</span>
                                        <span class="user-email">emily@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge creator">Creator</span></td>
                            <td>2 Created</td>
                            <td>Mar 10, 2025</td>
                            <td>3 hours ago</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="View Profile">👁️</button>
                                    <button class="btn-icon edit" title="Edit User">✏️</button>
                                    <button class="btn-icon delete" title="Suspend User">🚫</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">AR</div>
                                    <div class="user-details">
                                        <span class="user-name">Alex Rodriguez</span>
                                        <span class="user-email">alex@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge learner">Learner</span></td>
                            <td>8 Enrolled</td>
                            <td>Apr 05, 2025</td>
                            <td>5 min ago</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="View Profile">👁️</button>
                                    <button class="btn-icon edit" title="Edit User">✏️</button>
                                    <button class="btn-icon delete" title="Suspend User">🚫</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <button class="page-btn" disabled>Previous</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">Next</button>
                </div>
            </div>

            <!-- User Activity Chart -->
            <div class="activity-chart-section">
                <div class="section-header">
                    <h2 class="section-title">User Activity Overview</h2>
                </div>
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <p>📊 User activity chart will be displayed here</p>
                        <small>Integration with database required</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Content -->
        <div class="content-section" id="coursesContent" style="display: none;">
            <div class="page-title">
                <h1>Course Management</h1>
                <p>Review, approve, and manage courses before they go live</p>
            </div>

            <!-- Course Stats -->
            <div class="stats-grid">
                <div class="stat-card pending">
                    <div class="stat-header">
                        <div class="stat-icon pending">⏳</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Pending Review</div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-header">
                        <div class="stat-icon approved">✅</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Approved Courses</div>
                </div>

                <div class="stat-card rejected">
                    <div class="stat-header">
                        <div class="stat-icon rejected">❌</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Rejected</div>
                </div>

                <div class="stat-card published">
                    <div class="stat-header">
                        <div class="stat-icon published">🌟</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Published</div>
                </div>
            </div>

            <!-- Course Status Tabs -->
            <div class="course-tabs">
                <button class="tab-btn active" data-course-tab="all">All Courses</button>
                <button class="tab-btn" data-course-tab="pending">Pending Review</button>
                <button class="tab-btn" data-course-tab="approved">Approved</button>
                <button class="tab-btn" data-course-tab="rejected">Rejected</button>
                <button class="tab-btn" data-course-tab="published">Published</button>
            </div>

            <!-- Courses Review Section -->
            <div class="courses-section">
                <div class="section-header">
                    <h2 class="section-title">Course Review Queue</h2>
                    <div class="header-actions">
                        <div class="search-box">
                            <input type="text" placeholder="Search courses..." id="courseSearch">
                            <span class="search-icon">🔍</span>
                        </div>
                        <select class="filter-select" id="categoryFilter">
                            <option value="all">All Categories</option>
                            <option value="web-development">Web Development</option>
                            <option value="data-science">Data Science</option>
                            <option value="mobile-development">Mobile Development</option>
                            <option value="ui-ux-design">UI/UX Design</option>
                            <option value="blockchain">Blockchain</option>
                        </select>
                    </div>
                </div>
                
                <!-- Course Cards Grid -->
                <div class="courses-grid" id="coursesGrid">
                    <!-- Sample Course 1 - Pending -->
                    <div class="course-card" data-status="pending" data-category="web-development">
                        <div class="course-image">
                            <img src="https://via.placeholder.com/300x180/4f46e5/ffffff?text=React+Course" alt="React Fundamentals">
                            <div class="course-status pending">Pending Review</div>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">Advanced React Development</h3>
                            <p class="course-creator">👤 John Smith</p>
                            <p class="course-description">Learn advanced React concepts including hooks, context, and performance optimization...</p>
                            <div class="course-meta">
                                <span class="course-category">💻 Web Development</span>
                                <span class="course-duration">⏱️ 8 hours</span>
                                <span class="course-price">💰 $129.99</span>
                            </div>
                            <div class="course-details">
                                <p><strong>Submitted:</strong> Aug 20, 2025</p>
                                <p><strong>Modules:</strong> 12 modules, 45 lessons</p>
                            </div>
                            <div class="course-actions">
                                <button class="btn btn-primary" onclick="reviewCourse('course-1')">📝 Review</button>
                                <button class="btn btn-success" onclick="approveCourse('course-1')">✅ Approve</button>
                                <button class="btn btn-danger" onclick="rejectCourse('course-1')">❌ Reject</button>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Course 2 - Approved -->
                    <div class="course-card" data-status="approved" data-category="data-science">
                        <div class="course-image">
                            <img src="https://via.placeholder.com/300x180/059669/ffffff?text=Python+ML" alt="Python Machine Learning">
                            <div class="course-status approved">Approved</div>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">Python Machine Learning Mastery</h3>
                            <p class="course-creator">👤 Sarah Johnson</p>
                            <p class="course-description">Complete guide to machine learning with Python, scikit-learn, and TensorFlow...</p>
                            <div class="course-meta">
                                <span class="course-category">📊 Data Science</span>
                                <span class="course-duration">⏱️ 15 hours</span>
                                <span class="course-price">💰 $199.99</span>
                            </div>
                            <div class="course-details">
                                <p><strong>Approved:</strong> Aug 18, 2025</p>
                                <p><strong>Modules:</strong> 20 modules, 78 lessons</p>
                            </div>
                            <div class="course-actions">
                                <button class="btn btn-primary" onclick="reviewCourse('course-2')">👁️ View</button>
                                <button class="btn btn-success" onclick="publishCourse('course-2')">🌟 Publish</button>
                                <button class="btn btn-warning" onclick="editCourse('course-2')">✏️ Edit</button>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Course 3 - Published -->
                    <div class="course-card" data-status="published" data-category="ui-ux-design">
                        <div class="course-image">
                            <img src="https://via.placeholder.com/300x180/dc2626/ffffff?text=UI%2FUX+Design" alt="UI/UX Design">
                            <div class="course-status published">Published</div>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">Complete UI/UX Design Course</h3>
                            <p class="course-creator">👤 Emily Davis</p>
                            <p class="course-description">Master modern UI/UX design principles, tools, and workflows from scratch...</p>
                            <div class="course-meta">
                                <span class="course-category">🎨 UI/UX Design</span>
                                <span class="course-duration">⏱️ 12 hours</span>
                                <span class="course-price">💰 $149.99</span>
                            </div>
                            <div class="course-details">
                                <p><strong>Published:</strong> Aug 15, 2025</p>
                                <p><strong>Enrollments:</strong> 243 students</p>
                            </div>
                            <div class="course-actions">
                                <button class="btn btn-primary" onclick="viewCourseStats('course-3')">📊 Analytics</button>
                                <button class="btn btn-warning" onclick="unpublishCourse('course-3')">📥 Unpublish</button>
                                <button class="btn btn-secondary" onclick="editCourse('course-3')">✏️ Edit</button>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Course 4 - Rejected -->
                    <div class="course-card" data-status="rejected" data-category="blockchain">
                        <div class="course-image">
                            <img src="https://via.placeholder.com/300x180/7c2d12/ffffff?text=Blockchain" alt="Blockchain Basics">
                            <div class="course-status rejected">Rejected</div>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">Blockchain Development Basics</h3>
                            <p class="course-creator">👤 Mike Chen</p>
                            <p class="course-description">Introduction to blockchain technology and smart contract development...</p>
                            <div class="course-meta">
                                <span class="course-category">⛓️ Blockchain</span>
                                <span class="course-duration">⏱️ 6 hours</span>
                                <span class="course-price">💰 $89.99</span>
                            </div>
                            <div class="course-details">
                                <p><strong>Rejected:</strong> Aug 19, 2025</p>
                                <p><strong>Reason:</strong> Insufficient content quality</p>
                            </div>
                            <div class="course-actions">
                                <button class="btn btn-primary" onclick="reviewCourse('course-4')">📝 Re-review</button>
                                <button class="btn btn-info" onclick="provideFeedback('course-4')">💬 Feedback</button>
                                <button class="btn btn-danger" onclick="deleteCourse('course-4')">🗑️ Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="pagination">
                    <button class="page-btn" disabled>Previous</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">Next</button>
                </div>
            </div>

            <!-- Course Review Modal -->
            <div class="modal" id="courseReviewModal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Course Review</h2>
                        <button class="modal-close" onclick="closeReviewModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="review-content">
                            <h3 id="reviewCourseTitle">Course Title</h3>
                            <div class="review-tabs">
                                <button class="review-tab active" data-review-tab="overview">Overview</button>
                                <button class="review-tab" data-review-tab="content">Content</button>
                                <button class="review-tab" data-review-tab="quality">Quality Check</button>
                            </div>
                            <div class="review-tab-content">
                                <div id="overviewTab" class="tab-pane active">
                                    <div class="course-overview">
                                        <p><strong>Creator:</strong> <span id="reviewCreator">John Smith</span></p>
                                        <p><strong>Category:</strong> <span id="reviewCategory">Web Development</span></p>
                                        <p><strong>Duration:</strong> <span id="reviewDuration">8 hours</span></p>
                                        <p><strong>Price:</strong> <span id="reviewPrice">$129.99</span></p>
                                        <p><strong>Description:</strong></p>
                                        <p id="reviewDescription">Course description will appear here...</p>
                                    </div>
                                </div>
                                <div id="contentTab" class="tab-pane">
                                    <div class="content-checklist">
                                        <h4>Content Quality Checklist:</h4>
                                        <label><input type="checkbox"> Clear learning objectives</label>
                                        <label><input type="checkbox"> Well-structured modules</label>
                                        <label><input type="checkbox"> High-quality video content</label>
                                        <label><input type="checkbox"> Practical exercises included</label>
                                        <label><input type="checkbox"> Appropriate difficulty level</label>
                                        <label><input type="checkbox"> Updated and relevant content</label>
                                    </div>
                                </div>
                                <div id="qualityTab" class="tab-pane">
                                    <div class="quality-assessment">
                                        <h4>Quality Assessment:</h4>
                                        <div class="rating-section">
                                            <label>Content Quality:</label>
                                            <div class="rating-stars">
                                                <span class="star" data-rating="1">⭐</span>
                                                <span class="star" data-rating="2">⭐</span>
                                                <span class="star" data-rating="3">⭐</span>
                                                <span class="star" data-rating="4">⭐</span>
                                                <span class="star" data-rating="5">⭐</span>
                                            </div>
                                        </div>
                                        <div class="feedback-section">
                                            <label for="adminFeedback">Admin Feedback:</label>
                                            <textarea id="adminFeedback" rows="4" placeholder="Provide detailed feedback for the course creator..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" onclick="approveCourseFromModal()">✅ Approve Course</button>
                        <button class="btn btn-danger" onclick="rejectCourseFromModal()">❌ Reject Course</button>
                        <button class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Content -->
        <div class="content-section" id="analyticsContent" style="display: none;">
            <div class="page-title">
                <h1>Analytics Dashboard</h1>
                <p>Monitor website performance, user behavior, and platform growth</p>
            </div>

            <!-- Analytics Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card traffic">
                    <div class="stat-header">
                        <div class="stat-icon traffic">📈</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Visitors</div>
                </div>

                <div class="stat-card pageviews">
                    <div class="stat-header">
                        <div class="stat-icon pageviews">👁️</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Page Views</div>
                </div>

                <div class="stat-card sessions">
                    <div class="stat-header">
                        <div class="stat-icon sessions">⏱️</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Avg. Session Duration</div>
                </div>

                <div class="stat-card bounce">
                    <div class="stat-header">
                        <div class="stat-icon bounce">📊</div>
                        <span class="trend-indicator">+0%</span>
                    </div>
                    <div class="stat-value">0%</div>
                    <div class="stat-label">Bounce Rate</div>
                </div>
            </div>

            <!-- Analytics Time Period Selector -->
            <div class="analytics-controls">
                <div class="time-selector">
                    <button class="time-btn active" data-period="7">Last 7 Days</button>
                    <button class="time-btn" data-period="30">Last 30 Days</button>
                    <button class="time-btn" data-period="90">Last 3 Months</button>
                    <button class="time-btn" data-period="365">Last Year</button>
                </div>
                <div class="export-controls">
                    <button class="btn btn-secondary">📊 Export Report</button>
                    <button class="btn btn-primary">📧 Schedule Report</button>
                </div>
            </div>

            <!-- Main Analytics Charts -->
            <div class="analytics-grid">
                <!-- Website Traffic Chart -->
                <div class="analytics-card traffic-chart">
                    <div class="card-header">
                        <h3>Website Traffic Overview</h3>
                        <div class="chart-controls">
                            <select class="chart-filter">
                                <option value="visitors">Visitors</option>
                                <option value="pageviews">Page Views</option>
                                <option value="sessions">Sessions</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder large">
                            <div class="chart-mock">
                                <div class="chart-bars">
                                    <div class="bar" style="height: 20%"></div>
                                    <div class="bar" style="height: 35%"></div>
                                    <div class="bar" style="height: 45%"></div>
                                    <div class="bar" style="height: 30%"></div>
                                    <div class="bar" style="height: 60%"></div>
                                    <div class="bar" style="height: 80%"></div>
                                    <div class="bar" style="height: 70%"></div>
                                </div>
                                <p>📈 Traffic Analytics Chart</p>
                                <small>Real-time data integration required</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Demographics -->
                <div class="analytics-card demographics">
                    <div class="card-header">
                        <h3>User Demographics</h3>
                    </div>
                    <div class="demographics-content">
                        <div class="demo-section">
                            <h4>Top Countries</h4>
                            <div class="country-list">
                                <div class="country-item">
                                    <span class="flag">🇺🇸</span>
                                    <span class="country-name">United States</span>
                                    <span class="country-percentage">0%</span>
                                </div>
                                <div class="country-item">
                                    <span class="flag">🇬🇧</span>
                                    <span class="country-name">United Kingdom</span>
                                    <span class="country-percentage">0%</span>
                                </div>
                                <div class="country-item">
                                    <span class="flag">🇨🇦</span>
                                    <span class="country-name">Canada</span>
                                    <span class="country-percentage">0%</span>
                                </div>
                                <div class="country-item">
                                    <span class="flag">🇩🇪</span>
                                    <span class="country-name">Germany</span>
                                    <span class="country-percentage">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="demo-section">
                            <h4>Device Types</h4>
                            <div class="device-stats">
                                <div class="device-item">
                                    <span class="device-icon">💻</span>
                                    <span class="device-name">Desktop</span>
                                    <span class="device-percentage">0%</span>
                                </div>
                                <div class="device-item">
                                    <span class="device-icon">📱</span>
                                    <span class="device-name">Mobile</span>
                                    <span class="device-percentage">0%</span>
                                </div>
                                <div class="device-item">
                                    <span class="device-icon">📟</span>
                                    <span class="device-name">Tablet</span>
                                    <span class="device-percentage">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Analytics Grid -->
            <div class="analytics-grid-secondary">
                <!-- Course Performance -->
                <div class="analytics-card course-performance">
                    <div class="card-header">
                        <h3>Course Performance</h3>
                    </div>
                    <div class="performance-content">
                        <div class="performance-metrics">
                            <div class="metric-item">
                                <div class="metric-icon">📚</div>
                                <div class="metric-details">
                                    <span class="metric-value">0</span>
                                    <span class="metric-label">Total Enrollments</span>
                                </div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-icon">✅</div>
                                <div class="metric-details">
                                    <span class="metric-value">0%</span>
                                    <span class="metric-label">Completion Rate</span>
                                </div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-icon">⭐</div>
                                <div class="metric-details">
                                    <span class="metric-value">0.0</span>
                                    <span class="metric-label">Avg. Rating</span>
                                </div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-icon">💰</div>
                                <div class="metric-details">
                                    <span class="metric-value">$0</span>
                                    <span class="metric-label">Revenue</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Activity -->
                <div class="analytics-card realtime-activity">
                    <div class="card-header">
                        <h3>Real-time Activity</h3>
                        <div class="realtime-indicator">
                            <div class="pulse-dot"></div>
                            <span>Live</span>
                        </div>
                    </div>
                    <div class="realtime-content">
                        <div class="realtime-stats">
                            <div class="realtime-stat">
                                <span class="stat-number">0</span>
                                <span class="stat-label">Active Users</span>
                            </div>
                            <div class="realtime-stat">
                                <span class="stat-number">0</span>
                                <span class="stat-label">Pages/Session</span>
                            </div>
                        </div>
                        <div class="realtime-feed">
                            <h4>Live Activity Feed</h4>
                            <div class="activity-feed">
                                <div class="feed-item">
                                    <span class="feed-time">--:--</span>
                                    <span class="feed-action">No activity</span>
                                    <span class="feed-location">--</span>
                                </div>
                                <p class="no-data">Waiting for live data...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Pages -->
                <div class="analytics-card top-pages">
                    <div class="card-header">
                        <h3>Top Pages</h3>
                    </div>
                    <div class="pages-content">
                        <div class="page-list">
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-title">Home Page</span>
                                    <span class="page-url">/</span>
                                </div>
                                <span class="page-views">0 views</span>
                            </div>
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-title">Course Browser</span>
                                    <span class="page-url">/courses</span>
                                </div>
                                <span class="page-views">0 views</span>
                            </div>
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-title">Login Page</span>
                                    <span class="page-url">/login</span>
                                </div>
                                <span class="page-views">0 views</span>
                            </div>
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-title">Register Page</span>
                                    <span class="page-url">/register</span>
                                </div>
                                <span class="page-views">0 views</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traffic Sources -->
                <div class="analytics-card traffic-sources">
                    <div class="card-header">
                        <h3>Traffic Sources</h3>
                    </div>
                    <div class="sources-content">
                        <div class="source-list">
                            <div class="source-item">
                                <div class="source-info">
                                    <span class="source-icon">🔍</span>
                                    <span class="source-name">Organic Search</span>
                                </div>
                                <span class="source-percentage">0%</span>
                            </div>
                            <div class="source-item">
                                <div class="source-info">
                                    <span class="source-icon">🔗</span>
                                    <span class="source-name">Direct</span>
                                </div>
                                <span class="source-percentage">0%</span>
                            </div>
                            <div class="source-item">
                                <div class="source-info">
                                    <span class="source-icon">📱</span>
                                    <span class="source-name">Social Media</span>
                                </div>
                                <span class="source-percentage">0%</span>
                            </div>
                            <div class="source-item">
                                <div class="source-info">
                                    <span class="source-icon">📧</span>
                                    <span class="source-name">Email</span>
                                </div>
                                <span class="source-percentage">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Table -->
            <div class="analytics-table-section">
                <div class="section-header">
                    <h2 class="section-title">Detailed Analytics</h2>
                    <div class="table-controls">
                        <select class="table-filter">
                            <option value="pages">Pages</option>
                            <option value="users">Users</option>
                            <option value="courses">Courses</option>
                            <option value="events">Events</option>
                        </select>
                        <button class="btn btn-secondary">📊 Export Data</button>
                    </div>
                </div>
                
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Page/Item</th>
                            <th>Views</th>
                            <th>Unique Visitors</th>
                            <th>Avg. Time</th>
                            <th>Bounce Rate</th>
                            <th>Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-item">
                                    <span class="item-title">Home Page</span>
                                    <span class="item-url">/</span>
                                </div>
                            </td>
                            <td>0</td>
                            <td>0</td>
                            <td>0:00</td>
                            <td>0%</td>
                            <td>0%</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-item">
                                    <span class="item-title">Course Browser</span>
                                    <span class="item-url">/courses</span>
                                </div>
                            </td>
                            <td>0</td>
                            <td>0</td>
                            <td>0:00</td>
                            <td>0%</td>
                            <td>0%</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-item">
                                    <span class="item-title">Login Page</span>
                                    <span class="item-url">/login</span>
                                </div>
                            </td>
                            <td>0</td>
                            <td>0</td>
                            <td>0:00</td>
                            <td>0%</td>
                            <td>0%</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <button class="page-btn" disabled>Previous</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">Next</button>
                </div>
            </div>
        </div>
    </main>

       <script>
        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');
        const header = document.getElementById('header');

        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            overlay.classList.toggle('show');
            mainContent.classList.toggle('full-width');
            header.classList.toggle('full-width');
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
            overlay.classList.remove('show');
            mainContent.classList.add('full-width');
            header.classList.add('full-width');
        });

        // Close sidebar on mobile when clicking a nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    overlay.classList.remove('show');
                    mainContent.classList.add('full-width');
                    header.classList.add('full-width');
                }
            });
        });

        // Handle responsive behavior
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('full-width');
                header.classList.add('full-width');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('full-width');
                header.classList.remove('full-width');
                overlay.classList.remove('show');
            }
        }

        window.addEventListener('resize', handleResize);
        window.addEventListener('load', handleResize);

        // Animate stats on load
        function animateStats() {
            const statValues = document.querySelectorAll('.stat-value');
            const values = [0, 0, 0, 0];
            
            statValues.forEach((stat, index) => {
                let current = 0;
                const target = values[index];
                const increment = target / 100;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (index === 3) { // Completion rate
                        stat.textContent = current.toFixed(1) + '%';
                    } else {
                        stat.textContent = Math.floor(current).toLocaleString();
                    }
                }, 20);
            });
        }

        // Initialize animations
        window.addEventListener('load', () => {
            setTimeout(animateStats, 500);
            initNotificationDropdown();
        });

        // Tab switching functionality
        function showContent(tabName) {
            // Hide all content sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected content section
            const targetContent = document.getElementById(tabName + 'Content');
            if (targetContent) {
                targetContent.style.display = 'block';
                
                // If showing users content, reinitialize user tabs and search
                if (tabName === 'users') {
                    setTimeout(() => {
                        initUserTabs();
                        initUserSearch();
                    }, 100);
                }
                
                // If showing courses content, reinitialize course tabs and search
                if (tabName === 'courses') {
                    setTimeout(() => {
                        initCourseTabs();
                        initCourseSearch();
                    }, 100);
                }
                
                // If showing analytics content, reinitialize analytics features
                if (tabName === 'analytics') {
                    setTimeout(() => {
                        initAnalytics();
                    }, 100);
                }
            }
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            const activeLink = document.querySelector(`[data-tab="${tabName}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        // Add click event listeners to nav links with data-tab attribute
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.getAttribute('data-tab');
                showContent(tabName);
                
                // Close mobile menu if open
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    overlay.classList.remove('show');
                    mainContent.classList.add('full-width');
                    header.classList.add('full-width');
                }
            });
        });

        // Notification dropdown functionality
        function initNotificationDropdown() {
            const notificationIcon = document.querySelector('.notification');
            const notificationDropdown = document.querySelector('.notification-dropdown');
            
            if (!notificationIcon || !notificationDropdown) return;
            
            // Toggle dropdown when notification icon is clicked
            notificationIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
                
                // Update notification badge if dropdown is opened
                if (notificationDropdown.classList.contains('show')) {
                    updateNotificationBadge();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });
            
            // Mark all as read functionality
            const markAllReadBtn = notificationDropdown.querySelector('.mark-all-read');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', () => {
                    markAllNotificationsAsRead();
                });
            }
            
            // Individual notification interactions
            const notificationItems = notificationDropdown.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                // Mark as read when clicked
                item.addEventListener('click', () => {
                    markNotificationAsRead(item);
                });
                
                // Handle action buttons
                const actionBtns = item.querySelectorAll('.btn-small');
                actionBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleNotificationAction(btn, item);
                    });
                });
            });
        }
        
        function updateNotificationBadge() {
            const badge = document.querySelector('.notification-badge');
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            
            if (badge) {
                if (unreadItems.length > 0) {
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        function markNotificationAsRead(item) {
            item.classList.remove('unread');
            item.classList.add('read');
            updateNotificationBadge();
        }
        
        function markAllNotificationsAsRead() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            unreadItems.forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
            });
            updateNotificationBadge();
        }
        
        function handleNotificationAction(button, notificationItem) {
            const action = button.textContent.toLowerCase().trim();
            
            if (action === 'approve' || action === 'review') {
                // Handle approval/review actions
                console.log('Handling notification action:', action);
                
                // Mark as read and potentially remove the notification
                markNotificationAsRead(notificationItem);
                
                // You can add specific logic here for different notification types
                // For example, opening relevant modals or redirecting to specific content
            }
        }

        // User tab functionality
        function initUserTabs() {
            const userTabBtns = document.querySelectorAll('.tab-btn');
            
            userTabBtns.forEach((btn) => {
                // Remove existing event listeners by cloning the element
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    // Update active tab
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    newBtn.classList.add('active');
                    
                    // Filter users based on selected tab
                    const tabType = newBtn.getAttribute('data-tab');
                    filterUsers(tabType);
                });
            });
        }

        function filterUsers(type) {
            const tableRows = document.querySelectorAll('#usersTableBody tr');
            
            tableRows.forEach((row) => {
                const roleElement = row.querySelector('.role-badge');
                if (!roleElement) return;
                
                const userRole = roleElement.textContent.toLowerCase().trim();
                
                let shouldShow = false;
                
                if (type === 'all') {
                    shouldShow = true;
                } else if (type === 'creators' && userRole === 'creator') {
                    shouldShow = true;
                } else if (type === 'learners' && userRole === 'learner') {
                    shouldShow = true;
                }
                
                row.style.display = shouldShow ? '' : 'none';
            });
        }

        // Search functionality for users
        function initUserSearch() {
            const searchInput = document.getElementById('userSearch');
            
            if (searchInput) {
                // Remove existing event listeners by cloning the element
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
                
                newSearchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#usersTableBody tr');
                    
                    tableRows.forEach(row => {
                        const userName = row.querySelector('.user-name');
                        const userEmail = row.querySelector('.user-email');
                        
                        if (userName && userEmail) {
                            const nameText = userName.textContent.toLowerCase();
                            const emailText = userEmail.textContent.toLowerCase();
                            
                            if (nameText.includes(searchTerm) || emailText.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }
        }

        // Initialize user management features when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            initUserTabs();
            initUserSearch();
        });

        // Course Management Functions
        function initCourseTabs() {
            const courseTabBtns = document.querySelectorAll('[data-course-tab]');
            
            courseTabBtns.forEach((btn) => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    document.querySelectorAll('[data-course-tab]').forEach(b => b.classList.remove('active'));
                    newBtn.classList.add('active');
                    
                    const tabType = newBtn.getAttribute('data-course-tab');
                    filterCourses(tabType);
                });
            });
        }

        function filterCourses(status) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                
                if (status === 'all' || cardStatus === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function initCourseSearch() {
            const searchInput = document.getElementById('courseSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            
            if (searchInput) {
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
                
                newSearchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    filterCoursesBySearch(searchTerm);
                });
            }
            
            if (categoryFilter) {
                const newCategoryFilter = categoryFilter.cloneNode(true);
                categoryFilter.parentNode.replaceChild(newCategoryFilter, categoryFilter);
                
                newCategoryFilter.addEventListener('change', (e) => {
                    const category = e.target.value;
                    filterCoursesByCategory(category);
                });
            }
        }

        function filterCoursesBySearch(searchTerm) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const title = card.querySelector('.course-title').textContent.toLowerCase();
                const creator = card.querySelector('.course-creator').textContent.toLowerCase();
                const description = card.querySelector('.course-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || creator.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterCoursesByCategory(category) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Course Action Functions
        function reviewCourse(courseId) {
            const modal = document.getElementById('courseReviewModal');
            modal.style.display = 'flex';
            
            // Populate modal with course data
            // This would typically fetch data from a database
            document.getElementById('reviewCourseTitle').textContent = 'Advanced React Development';
            document.getElementById('reviewCreator').textContent = 'John Smith';
            document.getElementById('reviewCategory').textContent = 'Web Development';
            document.getElementById('reviewDuration').textContent = '8 hours';
            document.getElementById('reviewPrice').textContent = '$129.99';
            document.getElementById('reviewDescription').textContent = 'Learn advanced React concepts including hooks, context, and performance optimization techniques.';
            
            // Store current course ID for approval/rejection
            modal.setAttribute('data-course-id', courseId);
            
            // Initialize review modal tabs
            initReviewTabs();
        }

        function initReviewTabs() {
            const reviewTabs = document.querySelectorAll('.review-tab');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            reviewTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    reviewTabs.forEach(t => t.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    tab.classList.add('active');
                    const tabName = tab.getAttribute('data-review-tab');
                    document.getElementById(tabName + 'Tab').classList.add('active');
                });
            });
            
            // Initialize star rating
            initStarRating();
        }

        function initStarRating() {
            const stars = document.querySelectorAll('.star');
            let selectedRating = 0;
            
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    selectedRating = index + 1;
                    updateStars(selectedRating);
                });
                
                star.addEventListener('mouseover', () => {
                    updateStars(index + 1);
                });
            });
            
            document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
                updateStars(selectedRating);
            });
            
            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            }
        }

        function closeReviewModal() {
            document.getElementById('courseReviewModal').style.display = 'none';
        }

        function approveCourse(courseId) {
            if (confirm('Are you sure you want to approve this course?')) {
                // Update course status to approved
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Approved';
                statusElement.className = 'course-status approved';
                courseCard.setAttribute('data-status', 'approved');
                
                // Update actions
                updateCourseActions(courseCard, 'approved', courseId);
                
                alert('Course approved successfully!');
            }
        }

        function approveCourseFromModal() {
            const modal = document.getElementById('courseReviewModal');
            const courseId = modal.getAttribute('data-course-id');
            closeReviewModal();
            approveCourse(courseId);
        }

        function rejectCourse(courseId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                // Update course status to rejected
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Rejected';
                statusElement.className = 'course-status rejected';
                courseCard.setAttribute('data-status', 'rejected');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Rejected:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Reason:</strong> ${reason}</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'rejected', courseId);
                
                alert('Course rejected successfully!');
            }
        }

        function rejectCourseFromModal() {
            const feedback = document.getElementById('adminFeedback').value;
            const modal = document.getElementById('courseReviewModal');
            const courseId = modal.getAttribute('data-course-id');
            
            if (feedback.trim()) {
                closeReviewModal();
                
                // Update course status to rejected with feedback
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Rejected';
                statusElement.className = 'course-status rejected';
                courseCard.setAttribute('data-status', 'rejected');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Rejected:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Reason:</strong> ${feedback}</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'rejected', courseId);
                
                alert('Course rejected with feedback!');
            } else {
                alert('Please provide feedback before rejecting the course.');
            }
        }

        function publishCourse(courseId) {
            if (confirm('Are you sure you want to publish this course to the platform?')) {
                // Update course status to published
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Published';
                statusElement.className = 'course-status published';
                courseCard.setAttribute('data-status', 'published');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Published:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Enrollments:</strong> 0 students</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'published', courseId);
                
                alert('Course published successfully!');
            }
        }

        function unpublishCourse(courseId) {
            if (confirm('Are you sure you want to unpublish this course?')) {
                // Update course status back to approved
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Approved';
                statusElement.className = 'course-status approved';
                courseCard.setAttribute('data-status', 'approved');
                
                // Update actions
                updateCourseActions(courseCard, 'approved', courseId);
                
                alert('Course unpublished successfully!');
            }
        }

        function updateCourseActions(courseCard, status, courseId) {
            const actionsElement = courseCard.querySelector('.course-actions');
            
            let actionsHTML = '';
            
            switch(status) {
                case 'pending':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">📝 Review</button>
                        <button class="btn btn-success" onclick="approveCourse('${courseId}')">✅ Approve</button>
                        <button class="btn btn-danger" onclick="rejectCourse('${courseId}')">❌ Reject</button>
                    `;
                    break;
                case 'approved':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">👁️ View</button>
                        <button class="btn btn-success" onclick="publishCourse('${courseId}')">🌟 Publish</button>
                        <button class="btn btn-warning" onclick="editCourse('${courseId}')">✏️ Edit</button>
                    `;
                    break;
                case 'published':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="viewCourseStats('${courseId}')">📊 Analytics</button>
                        <button class="btn btn-warning" onclick="unpublishCourse('${courseId}')">📥 Unpublish</button>
                        <button class="btn btn-secondary" onclick="editCourse('${courseId}')">✏️ Edit</button>
                    `;
                    break;
                case 'rejected':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">📝 Re-review</button>
                        <button class="btn btn-info" onclick="provideFeedback('${courseId}')">💬 Feedback</button>
                        <button class="btn btn-danger" onclick="deleteCourse('${courseId}')">🗑️ Delete</button>
                    `;
                    break;
            }
            
            actionsElement.innerHTML = actionsHTML;
        }

        function editCourse(courseId) {
            alert('Edit course functionality - would open course editor');
        }

        function viewCourseStats(courseId) {
            alert('Course analytics functionality - would show detailed stats');
        }

        function provideFeedback(courseId) {
            const feedback = prompt('Provide additional feedback for the course creator:');
            if (feedback) {
                alert('Feedback sent to course creator!');
            }
        }

        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to permanently delete this course?')) {
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                courseCard.remove();
                alert('Course deleted successfully!');
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            const modal = document.getElementById('courseReviewModal');
            if (e.target === modal) {
                closeReviewModal();
            }
        });

        // Analytics Functions
        function initAnalytics() {
            initTimePeriodSelector();
            initAnalyticsCharts();
            initAnalyticsTable();
            startRealTimeUpdates();
        }

        function initTimePeriodSelector() {
            const timeBtns = document.querySelectorAll('.time-btn');
            
            timeBtns.forEach(btn => {
                // Remove existing event listeners
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', () => {
                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                    newBtn.classList.add('active');
                    
                    const period = newBtn.getAttribute('data-period');
                    updateAnalyticsData(period);
                });
            });
        }

        function updateAnalyticsData(period) {
            // This would typically fetch real data from your analytics API
            console.log(`Updating analytics data for ${period} days`);
            
            // Simulate data update with animation
            animateAnalyticsStats();
            updateCharts(period);
        }

        function animateAnalyticsStats() {
            // This would be populated with real data from your backend
            const mockData = {
                visitors: 0,
                pageviews: 0,
                sessionDuration: '0:00',
                bounceRate: '0%'
            };
            
            // Update stat values with animation
            const statValues = document.querySelectorAll('#analyticsContent .stat-value');
            statValues.forEach((stat, index) => {
                // Animation logic would go here for real data
                stat.textContent = Object.values(mockData)[index];
            });
        }

        function initAnalyticsCharts() {
            // Initialize chart filters
            const chartFilters = document.querySelectorAll('.chart-filter');
            chartFilters.forEach(filter => {
                filter.addEventListener('change', (e) => {
                    const chartType = e.target.value;
                    updateChart(chartType);
                });
            });
        }

        function updateChart(chartType) {
            console.log(`Updating chart to show: ${chartType}`);
            // This would update the chart visualization
            // In a real implementation, you'd integrate with Chart.js, D3.js, etc.
        }

        function updateCharts(period) {
            // Update all charts based on time period
            console.log(`Updating all charts for ${period} days`);
            
            // Animate the mock chart bars
            const bars = document.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                // Reset animation
                bar.style.animation = 'none';
                bar.offsetHeight; // Trigger reflow
                bar.style.animation = 'barGrow 1s ease-out';
            });
        }

        function initAnalyticsTable() {
            const tableFilter = document.querySelector('.table-filter');
            if (tableFilter) {
                tableFilter.addEventListener('change', (e) => {
                    const filterType = e.target.value;
                    updateAnalyticsTable(filterType);
                });
            }
        }

        function updateAnalyticsTable(filterType) {
            console.log(`Filtering analytics table by: ${filterType}`);
            // This would update the table data based on the filter
            // In a real implementation, you'd fetch and populate with real data
        }

        function startRealTimeUpdates() {
            // Simulate real-time updates
            setInterval(() => {
                updateRealTimeData();
            }, 30000); // Update every 30 seconds
        }

        function updateRealTimeData() {
            // This would fetch real-time data from your analytics service
            const realTimeStats = document.querySelectorAll('.realtime-stat .stat-number');
            const activityFeed = document.querySelector('.activity-feed');
            
            // In a real implementation, you'd update with actual data
            // For now, we'll keep it at 0 as placeholder
            
            // Update timestamp for last update
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            
            // Could add a "last updated" indicator
            console.log(`Real-time data updated at ${timeString}`);
        }

        // Export functionality
        function exportAnalyticsReport() {
            alert('Export functionality - would generate and download analytics report');
        }

        function scheduleReport() {
            alert('Schedule report functionality - would set up automated reports');
        }

        // Add event listeners for export buttons
        document.addEventListener('DOMContentLoaded', () => {
            // Export report buttons
            const exportBtns = document.querySelectorAll('.export-controls .btn');
            exportBtns.forEach(btn => {
                if (btn.textContent.includes('Export')) {
                    btn.addEventListener('click', exportAnalyticsReport);
                } else if (btn.textContent.includes('Schedule')) {
                    btn.addEventListener('click', scheduleReport);
                }
            });
            
            // Table export button
            const tableExportBtn = document.querySelector('.table-controls .btn');
            if (tableExportBtn) {
                tableExportBtn.addEventListener('click', exportAnalyticsReport);
            }
        });
    </script>
</body>
</html>