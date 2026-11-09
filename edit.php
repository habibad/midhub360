<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit bKash Send Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            padding: 8px;
            width: 100%;
            max-width: 400px;
        }
        .form-group button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Edit bKash Send Entry</h2>

<form id="searchForm">
    <div class="form-group">
        <label for="id">Enter ID:</label>
        <input type="number" id="id" name="id" required>
        <button type="submit">Fetch Data</button>
    </div>
</form>

<form id="editForm" style="display: none;">
    <div class="form-group">
        <label for="company_name">Company Name:</label>
        <input type="text" id="company_name" name="company_name" required>
    </div>
    <div class="form-group">
        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" required>
    </div>
    <div class="form-group">
        <label for="balance">Balance:</label>
        <input type="number" id="balance" name="balance" required>
    </div>
    <div class="form-group">
        <label for="charge">Charge:</label>
        <input type="number" id="charge" name="charge" required>
    </div>
    <div class="form-group">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" required>
    </div>
    <div class="form-group">
        <label for="submitted_date">Submitted Date:</label>
        <input type="date" id="submitted_date" name="submitted_date" required>
    </div>
    <div class="form-group">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" required>
    </div>
    <button type="submit">Update</button>
</form>

<script>
    document.getElementById('searchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        let id = document.getElementById('id').value;

        fetch(`fetch.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('editForm').style.display = 'block';
                    document.getElementById('company_name').value = data.result.company_name;
                    document.getElementById('ac_number').value = data.result.ac_number;
                    document.getElementById('balance').value = data.result.balance;
                    document.getElementById('charge').value = data.result.charge;
                    document.getElementById('amount').value = data.result.amount;
                    document.getElementById('submitted_date').value = data.result.submitted_date;
                    document.getElementById('status').value = data.result.status;
                } else {
                    alert('No data found for this ID');
                }
            });
    });

    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(document.getElementById('editForm'));
        formData.append('id', document.getElementById('id').value);

        fetch('update.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Data updated successfully!');
                } else {
                    alert('Failed to update data.');
                }
            });
    });
</script>

</body>
</html>
