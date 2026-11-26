<?php
require_once __DIR__ . '/lib/guard.php'; midhub_guard_super_admin(); // SECURITY: was reachable without admin login
// Database connection
require_once __DIR__ . '/config/env.php'; // DB credentials come from .env, never from code
$conn = midhub_get_db_connection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $conn->real_escape_string($_POST['id']); // Secure the input

    // Fetch record based on ID
    $sql = "SELECT * FROM bkash_send WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // Return data as JSON
    } else {
        echo json_encode([]); // Return an empty array if no record found
    }

    $conn->close();
}
?>
