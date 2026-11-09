<?php
class Update
{
    public static function UpdateMyProfile()
    {
        global $connect;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        include "login.check.php";
        include "../db.php";

        if (!Session::get('us_id')) {
            $_SESSION['error'] = "You are not logged in.";
            header("Location: update_myprofile.php");
            exit;
        }

        $user_id  = Session::get('us_id');
        $fullname = mysqli_real_escape_string($connect, trim($_POST['fullname'] ?? ''));
        $email    = mysqli_real_escape_string($connect, trim($_POST['email'] ?? ''));
        $phone    = mysqli_real_escape_string($connect, trim($_POST['phonenumber'] ?? ''));
        $pin      = mysqli_real_escape_string($connect, trim($_POST['selfpin'] ?? ''));

        if (empty($fullname) || empty($email) || empty($phone) || empty($pin)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: update_myprofile.php");
            exit;
        }

        // Check PIN with md5
        $pin_md5 = md5($pin);
        $check_pin = mysqli_query($connect, "SELECT pin_code FROM user WHERE id = '$user_id'");
        $row = mysqli_fetch_assoc($check_pin);

        if (!$row || $row['pin_code'] !== $pin_md5) {
            $_SESSION['error'] = "Invalid PIN. Please try again.";
            header("Location: update_myprofile.php");
            exit;
        }

        $update = mysqli_query($connect, "UPDATE user SET fullname = '$fullname', email = '$email', phone = '$phone' WHERE id = '$user_id'");

        if ($update) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile. Try again.";
        }

        header("Location: update_myprofile.php");
        exit;
    }

    public static function UpdateMySettings()
    {
        global $connect;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        include "login.check.php";
        include "db.php";

        if (!Session::get('us_id')) {
            $_SESSION['error'] = "You are not logged in.";
            header("Location: update_mysettings.php");
            exit;
        }

        $user_id = Session::get('us_id');

        $newpass = mysqli_real_escape_string($connect, trim($_POST['newpass'] ?? ''));
        $newpin  = mysqli_real_escape_string($connect, trim($_POST['newpin'] ?? ''));
        $oldpass = mysqli_real_escape_string($connect, trim($_POST['oldpass'] ?? ''));
        $oldpin  = mysqli_real_escape_string($connect, trim($_POST['oldpin'] ?? ''));

        if (empty($oldpass) || empty($oldpin)) {
            $_SESSION['error'] = "Old Password and Old PIN are required.";
            header("Location: update_mysettings.php");
            exit;
        }

        // Hash old credentials for checking
        $oldpass_md5 = md5($oldpass);
        $oldpin_md5  = md5($oldpin);

        $check = mysqli_query($connect, "SELECT pass, pin_code FROM user WHERE id = '$user_id' LIMIT 1");
        $row = mysqli_fetch_assoc($check);

        if (!$row) {
            $_SESSION['error'] = "User not found.";
            header("Location: update_mysettings.php");
            exit;
        }

        // Validate old password & pin
        if ($row['pass'] !== $oldpass_md5) {
            $_SESSION['error'] = "Old password is incorrect.";
            header("Location: update_mysettings.php");
            exit;
        }

        if ($row['pin_code'] !== $oldpin_md5) {
            $_SESSION['error'] = "Old PIN is incorrect.";
            header("Location: update_mysettings.php");
            exit;
        }

        // Build update fields with md5 hash
        $updates = [];
        if (!empty($newpass)) $updates[] = "pass = '" . md5($newpass) . "'";
        if (!empty($newpin))  $updates[] = "pin_code = '" . md5($newpin) . "'";

        if (count($updates) > 0) {
            $sql = "UPDATE user SET " . implode(', ', $updates) . " WHERE id = '$user_id'";
            $ok = mysqli_query($connect, $sql);

            if ($ok) {
                $_SESSION['success'] = "Settings updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update settings.";
            }
        } else {
            $_SESSION['error'] = "Nothing to update.";
        }

        header("Location: update_mysettings.php");
        exit;
    }
}
