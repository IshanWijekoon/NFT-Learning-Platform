<?php
include 'db.php';

echo "Enrollments table structure:\n";
$result = mysqli_query($conn, "DESCRIBE enrollments");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nSample enrollment data:\n";
$enrollments = mysqli_query($conn, "SELECT * FROM enrollments LIMIT 3");
while($enrollment = mysqli_fetch_assoc($enrollments)) {
    print_r($enrollment);
}
?>
