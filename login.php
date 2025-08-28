<?php

// login.php - checks admins, learners, creators tables and returns JSON
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email required.']); exit;
}
if (!$password) {
    echo json_encode(['success' => false, 'message' => 'Password required.']); exit;
}
if (!in_array($role, ['admin','learner','creator'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']); exit;
}

$tables = [
    'admin' => 'admins',
    'learner' => 'learners',
    'creator' => 'creators'
];

$table = $tables[$role] ?? null;
if (!$table) {
    echo json_encode(['success' => false, 'message' => 'Role not supported.']); exit;
}

// prepare and fetch user by email
// columns: id, email, password, full_name (if exist)
$sql = "SELECT id, email, password, full_name FROM `$table` WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']); exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']); exit;
}

// verify password (assumes passwords stored with password_hash)
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']); exit;
}

// login success: set session and return redirect
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $role;
$_SESSION['full_name'] = $user['full_name'] ?? '';

$redirect = '/';
if ($role === 'admin') $redirect = 'home-creater.html';
elseif ($role === 'learner') $redirect = 'home-learner.html';
elseif ($role === 'creator') $redirect = 'home-creater.html';

echo json_encode(['success' => true, 'redirect' => $redirect]);
exit;
?>