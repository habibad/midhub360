<?php
// Database connection
$servername = "124.6.224.121";
$username = "midhubDBuser"; // Update with your database username
$password = "2345Midh@b#4$2025"; // Update with your database password
$dbname = "midhub-db"; // Update with your database name


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id = $_POST['id'];
    $company_name = $_POST['company_name'];
    $ac_number = $_POST['ac_number'];
    $type = $_POST['type'];
    $remarks = $_POST['remarks'];
    $balance = $_POST['balance'];
    $charge = $_POST['charge'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    // SQL query to update the bkash_send table
    $sql = "UPDATE bkash_send SET 
            company_name='$company_name', 
            ac_number='$ac_number', 
            type='$type', 
            remarks='$remarks', 
            balance='$balance', 
            charge='$charge', 
            amount='$amount', 
            status='$status' 
            WHERE id=$id";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
