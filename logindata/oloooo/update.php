<?php
session_start();
require_once dirname(__DIR__, 2) . '/lib/guard.php'; midhub_guard_super_admin(); // SECURITY: any logged-in customer could edit bkash_send

if (!isset($_SESSION['user_id'])) {
    die("Access denied: Please log in first.");
}

require_once dirname(__DIR__, 2) . '/config/env.php'; // DB credentials come from .env, never from code
$conn = midhub_get_db_connection();

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
