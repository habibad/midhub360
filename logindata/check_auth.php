<?php
// check_auth.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page
    exit();
}
?>
