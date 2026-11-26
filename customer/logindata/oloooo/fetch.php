<?php
session_start();
require_once dirname(__DIR__, 3) . '/lib/guard.php'; midhub_guard_super_admin(); // SECURITY: any logged-in customer could edit bkash_send

if (!isset($_SESSION['user_id'])) {
    die("Access denied: Please log in first.");
}

require_once dirname(__DIR__, 3) . '/config/env.php'; // DB credentials come from .env, never from code
$conn = midhub_get_db_connection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "SELECT * FROM bkash_send WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'result' => $result->fetch_assoc()]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
