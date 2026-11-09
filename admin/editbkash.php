<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit bKash Send Table</title>
</head>
<body>
    <h2>Edit bKash Send Record</h2>
    <form method="POST" action="">
        <label for="id">Enter ID:</label>
        <input type="number" name="id" id="id" required>
        <input type="submit" name="fetch" value="Fetch Data">
    </form>

    <?php
    if (isset($_POST['fetch'])) {
        $id = $_POST['id'];

        // Database connection
        $dsn = 'mysql:host=124.6.224.121;dbname=midhub-db'; // Change 'your_database_name' to your actual database name
        $username = 'midhubDBuser'; // Change 'your_username' to your actual username
        $password = '2345Midh@b#4$2025'; // Change 'your_password' to your actual password

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch data
            $stmt = $pdo->prepare("SELECT * FROM bkash_send WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                ?>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <label for="company_name">Company Name:</label>
                    <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($row['company_name']); ?>" required><br>
                    
                    <label for="ac_number">Account Number:</label>
                    <input type="text" name="ac_number" id="ac_number" value="<?php echo htmlspecialchars($row['ac_number']); ?>" required><br>
                    
                    <label for="balance">Balance:</label>
                    <input type="number" name="balance" id="balance" value="<?php echo $row['balance']; ?>" required><br>
                    
                    <label for="charge">Charge:</label>
                    <input type="number" name="charge" id="charge" value="<?php echo $row['charge']; ?>" required><br>
                    
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" value="<?php echo $row['amount']; ?>" required><br>
                    
                    <label for="submitted_date">Submitted Date:</label>
                    <input type="date" name="submitted_date" id="submitted_date" value="<?php echo $row['submitted_date']; ?>" required><br>
                    
                    <label for="status">Status:</label>
                    <input type="text" name="status" id="status" value="<?php echo htmlspecialchars($row['status']); ?>" required><br>
                    
                    <input type="submit" name="update" value="Update">
                </form>
                <?php
            } else {
                echo "Record not found.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Update data
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $company_name = $_POST['company_name'];
        $ac_number = $_POST['ac_number'];
        $balance = $_POST['balance'];
        $charge = $_POST['charge'];
        $amount = $_POST['amount'];
        $submitted_date = $_POST['submitted_date'];
        $status = $_POST['status'];

        try {
            $stmt = $pdo->prepare("UPDATE bkash_send SET company_name = :company_name, ac_number = :ac_number, balance = :balance, charge = :charge, amount = :amount, submitted_date = :submitted_date, status = :status WHERE id = :id");
            $stmt->execute([
                ':company_name' => $company_name,
                ':ac_number' => $ac_number,
                ':balance' => $balance,
                ':charge' => $charge,
                ':amount' => $amount,
                ':submitted_date' => $submitted_date,
                ':status' => $status,
                ':id' => $id
            ]);

            echo "Record updated successfully.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>
</body>
</html>
