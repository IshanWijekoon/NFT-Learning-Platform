<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$wallet_address = $input['wallet_address'] ?? '';
$action = $input['action'] ?? 'get';

// Validate wallet address
if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $wallet_address)) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallet address format']);
    exit();
}

try {
    if ($action === 'get') {
        // Get wallet profile
        $query = "SELECT * FROM wallet_profiles WHERE wallet_address = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $wallet_address);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($profile = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            // Create default profile
            $insert_query = "INSERT INTO wallet_profiles (wallet_address, display_name) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            $display_name = substr($wallet_address, 0, 6) . '...' . substr($wallet_address, -4);
            mysqli_stmt_bind_param($stmt, 'ss', $wallet_address, $display_name);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode([
                    'success' => true, 
                    'profile' => [
                        'wallet_address' => $wallet_address,
                        'display_name' => $display_name,
                        'email' => null,
                        'bio' => null
                    ],
                    'created' => true
                ]);
            } else {
                throw new Exception('Failed to create profile');
            }
        }
    } 
    elseif ($action === 'update') {
        // Update wallet profile
        $display_name = $input['display_name'] ?? '';
        $email = $input['email'] ?? null;
        $bio = $input['bio'] ?? null;
        
        $update_query = "UPDATE wallet_profiles SET display_name = ?, email = ?, bio = ?, updated_date = NOW() WHERE wallet_address = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssss', $display_name, $email, $bio, $wallet_address);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            throw new Exception('Failed to update profile');
        }
    }
    elseif ($action === 'get_enrollments') {
        // Get wallet enrollments
        $query = "SELECT we.*, c.course_name, c.description, c.thumbnail_url 
                  FROM web3_enrollments we 
                  JOIN courses c ON we.course_id = c.id 
                  WHERE we.wallet_address = ? 
                  ORDER BY we.enrollment_date DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $wallet_address);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $enrollments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $enrollments[] = $row;
        }
        
        echo json_encode(['success' => true, 'enrollments' => $enrollments]);
    }
} catch (Exception $e) {
    error_log("Wallet profile error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}

mysqli_close($conn);
?>