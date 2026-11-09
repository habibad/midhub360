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
