<?php
// Database connection
$servername = "124.6.224.121";
$username = "midhubDBuser"; // Update with your database username
$password = "2345Midh@b#4$2025"; // Update with your database password
$dbname = "midhub-db"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

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
