<?php
// fetch_data.php
include 'db.php'; // Ensure this file contains your database connection code

// Fetch last 5 mobile banking payments
$sql = "SELECT username, loaded_by, amount, old_balance, new_balance FROM bkash_send ORDER BY submitted_date DESC LIMIT 5";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "No data found";
}

// Return data as JSON (if using AJAX) or render directly
echo json_encode($data);
?>
