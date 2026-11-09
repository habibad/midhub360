<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "124.6.224.121";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "midhub-db"; // Update with your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve username and password from POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if statement preparation was successful
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    // Bind parameters and execute the statement
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    // Fetch the result of the query
    $result = $stmt->get_result();

    // Check if the user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password (Assuming the password is stored as a hash)
        if (password_verify($password, $user['password'])) {
            // Set session variables upon successful login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: edit.php"); // Redirect to the edit page after successful login
            exit;
        } else {
            echo "Invalid credentials.";
        }
    } else {
        echo "No user found with this username.";
    }
}

// Close the database connection
$conn->close();
?>
