<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Access denied: Please log in first.");
}

$servername = "124.6.224.121";
$username = "midhubDBuser"; // Update with your database username
$password = "2345Midh@b#4$2025"; // Update with your database password
$dbname = "midhub-db"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$company_name = $_POST['company_name'];
$ac_number = $_POST['ac_number'];
$balance = $_POST['balance'];
$charge = $_POST['charge'];
$amount = $_POST['amount'];
$submitted_date = $_POST['submitted_date'];
$status = $_POST['status'];

$sql = "UPDATE bkash_send SET company_name = ?, ac_number = ?, balance = ?, charge = ?, amount = ?, submitted_date = ?, status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdddssi", $company_name, $ac_number, $balance, $charge, $amount, $submitted_date, $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
