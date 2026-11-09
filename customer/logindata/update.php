<?php
// update.php
include "login.check.php";    
include "header.php"; 

include 'db.php';
include 'check_auth.php';

$id = $_GET['id'] ?? '';

if ($id) {
    // Fetch data from database
    $sql = "SELECT * FROM bkash_send WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
} else {
    echo "No ID provided.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update record
    $company_name = $_POST['company_name'];
    $ac_number = $_POST['ac_number'];
    $balance = $_POST['balance'];
    $charge = $_POST['charge'];
    $amount = $_POST['amount'];
    $submitted_date = $_POST['submitted_date'];
    $status = $_POST['status'];

    $update_sql = "UPDATE bkash_send SET company_name=?, ac_number=?, balance=?, charge=?, amount=?, submitted_date=?, status=? WHERE id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssdddssi", $company_name, $ac_number, $balance, $charge, $amount, $submitted_date, $status, $id);

    if ($update_stmt->execute()) {
        echo "Record updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update bKash Send</title>
    <style>
        /* Basic CSS styling */
        .container { width: 50%; margin: 0 auto; }
        input { display: block; margin-bottom: 10px; width: 100%; padding: 8px; }
        button { padding: 10px 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Update bKash Send Entry</h2>
    <form method="POST" id="updateForm">
        <input type="text" name="company_name" value="<?= $data['company_name']; ?>" placeholder="Company Name" required>
        <input type="text" name="ac_number" value="<?= $data['ac_number']; ?>" placeholder="Account Number" required>
        <input type="number" step="0.01" name="balance" value="<?= $data['balance']; ?>" placeholder="Balance" required>
        <input type="number" step="0.01" name="charge" value="<?= $data['charge']; ?>" placeholder="Charge" required>
        <input type="number" step="0.01" name="amount" value="<?= $data['amount']; ?>" placeholder="Amount" required>
        <input type="date" name="submitted_date" value="<?= $data['submitted_date']; ?>" required>
        <input type="text" name="status" value="<?= $data['status']; ?>" placeholder="Status" required>
        <button type="submit">Update</button>
    </form>
</div>

<script>
// Simple JavaScript validation (optional)
document.getElementById('updateForm').addEventListener('submit', function(e) {
    // Add any frontend validation here
});
</script>
</body>
</html>
