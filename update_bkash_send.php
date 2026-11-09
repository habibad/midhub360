<?php
// Database connection
$servername = "124.6.224.121";
$username = "midhubDBuser"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "midhub-db"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id = $conn->real_escape_string($_POST['id']);
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $ac_number = $conn->real_escape_string($_POST['ac_number']);
    $type = $conn->real_escape_string($_POST['type']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    $balance = $conn->real_escape_string($_POST['balance']);
    $charge = $conn->real_escape_string($_POST['charge']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $status = $conn->real_escape_string($_POST['status']);

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

    $conn->close();
}
?>
