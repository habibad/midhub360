<?php
session_start();
include "../db.php";
// Get the response values
$status          = isset($_GET['status']) ? $_GET['status'] : null;
$invoice_number  = isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null;
$trx_id          = isset($_GET['trx_id']) ? $_GET['trx_id'] : null;

// Your merchant details
$merchantId = '104-1653730183'; // replace with yours

if ($status === 'Successful' && $invoice_number) {
    // Call Transaction Status API to verify
    $url = "https://sandbox.paystation.com.bd/transaction-status";

    $postData = [
        'invoice_number' => $invoice_number
    ];

    $headers = [
        'merchantId: ' . $merchantId
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (!empty($result) && isset($result['status_code']) && $result['status_code'] == '200') {
        if (isset($result['data']) && $result['data']['trx_status'] === 'successful') {

            $balance_type = $result['data']['checkout_items'];
            $invoiceNumber = $result['data']['invoice_number'];
            $amount = $result['data']['payment_amount'];

            $queryFund  = "SELECT * FROM add_fund where invoice_no='$invoiceNumber'";    
            $resultFund = mysqli_query($connect, $queryFund);
            $resultFundData= mysqli_fetch_object($resultFund);

            if ($resultFund) {

                if($balance_type=="Recharge"){
                    // Query to update the balance
                    $query = "UPDATE user SET balance2 = balance2 + $amount WHERE id = $resultFundData->user_id";
                }else{
                    // Query to update the balance
                    $query = "UPDATE user SET bkash_balance = bkash_balance + $amount WHERE id = $resultFundData->user_id";
                }

                // Execute the query
                $success = mysqli_query($connect, $query);

                if ($success) {
                    $queryRequest = "UPDATE add_fund SET status = 1, transaction_id = '$trx_id' WHERE invoice_no = '$invoiceNumber'";
                    $update = mysqli_query($connect, $queryRequest);
                    $verifiedStatus = 'Successful';
                    $message = 'Your payment has been verified successfully!';
                } else { 
                    $verifiedStatus = 'Failed';
                    $message = 'Payment failed or cancelled.';
                }

            }

        }
    } else {
        $verifiedStatus = 'Failed';
        $message = 'Payment verification failed. Please contact support.';
    }
} else {
    $verifiedStatus = 'Failed';
    $message = 'Payment failed or cancelled.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Status</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 500px;
        margin: 50px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        padding: 25px;
        text-align: center;
    }
    .status {
        font-size: 1.5em;
        font-weight: bold;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .status.success {
        background: #d4edda;
        color: #155724;
    }
    .status.failed {
        background: #f8d7da;
        color: #721c24;
    }
    .highlight {
        font-size: 1.2em;
        background: #f1f1f1;
        padding: 10px;
        border-radius: 6px;
        margin: 10px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    table td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    .footer {
        margin-top: 20px;
        font-size: 0.9em;
        color: #666;
    }
    .go-back-btn {
        display: inline-block;
        margin-top: 20px;
        background: #007bff;
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1em;
    }
    .go-back-btn:hover {
        background: #0056b3;
    }
</style>
</head>
<body>
<div class="container">
    <div class="status <?php echo strtolower($verifiedStatus); ?>">
        <?php echo htmlspecialchars($verifiedStatus); ?>
    </div>
    <p><?php echo htmlspecialchars($message); ?></p>

    <div class="highlight">
        <strong>Invoice Number:</strong> <?php echo htmlspecialchars($invoice_number); ?><br>
        <strong>Transaction ID:</strong> <?php echo htmlspecialchars($trx_id); ?>
    </div>

    <table>
        <tr>
            <td><strong>Invoice Number:</strong></td>
            <td><?php echo htmlspecialchars($invoice_number); ?></td>
        </tr>
        <tr>
            <td><strong>Transaction ID:</strong></td>
            <td><?php echo htmlspecialchars($trx_id); ?></td>
        </tr>
        <tr>
            <td><strong>Payment Status:</strong></td>
            <td><?php echo htmlspecialchars($status); ?></td>
        </tr>
    </table>
    <a href="https://www.midhub360.com/customer/home.php" class="go-back-btn">Go Back</a>
    <div class="footer">
        &copy; <?php echo date("Y"); ?> Your Company Name. All rights reserved.
    </div>
</div>
</body>
</html>