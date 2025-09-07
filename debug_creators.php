<?php
include 'db.php';

echo "Creators table structure:\n";
$result = mysqli_query($conn, "DESCRIBE creators");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nSample creator data:\n";
$creators = mysqli_query($conn, "SELECT id, full_name, profile_picture FROM creators LIMIT 3");
while($creator = mysqli_fetch_assoc($creators)) {
    echo "ID: " . $creator['id'] . ", Name: " . $creator['full_name'] . ", Picture: " . ($creator['profile_picture'] ?? 'NULL') . "\n";
}
?>
