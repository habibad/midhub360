<?php
require_once __DIR__ . '/lib/guard.php'; midhub_guard_super_admin(); // SECURITY: was reachable without admin login
// Database connection
require_once __DIR__ . '/config/env.php'; // DB credentials come from .env, never from code
$conn = midhub_get_db_connection();

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
