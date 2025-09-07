<?php
session_start();

header('Content-Type: application/json');

echo json_encode([
    'session_data' => $_SESSION,
    'is_logged_in' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'full_name' => $_SESSION['full_name'] ?? null,
    'email' => $_SESSION['email'] ?? null
]);
?>
