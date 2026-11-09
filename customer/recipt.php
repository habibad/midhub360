<?php
include "login.check.php"; 
include "header.php"; 
?>

<?php
// Assume $transaction holds the result from the SQL query
$transaction = [
    'id' => 'TXN123456',
    's_number' => '017XXXXXXXX',
    'amount' => 500,
    's_date' => '2024-08-30 14:25:00',
's_time'=> '14:25:00',
    'rv_name' => 'John Doe'
];

echo "<h2>Sales Receipt</h2>";
echo "<p>Transaction ID: " . $transaction['id'] . "</p>";
echo "<p>Date: " . $transaction['s_date'] . "</p>";
echo "<p>Time: " . $transaction['s_time'] . "</p>";
echo "<p>Sender Phone: " . $transaction['s_number'] . "</p>";
echo "<p>Receiver Name: " . $transaction['rv_name'] . "</p>";
echo "<p>Amount: Tk. " . $transaction['amount'] . "</p>";
echo "<hr>";
echo "<p>Thank you for your transaction!</p>";
?>

















<?php 
include "footer.php"; 
?>
