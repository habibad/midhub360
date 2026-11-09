<?php
session_start();
include "../db.php";
	$action = $_REQUEST["service_type"];
	switch ($action) {
    case "topup":
        // echo json_encode($_POST);
        // exit();

        // ============================= GET THE OPERATOR =================================
        $raw = $_POST['number'] ?? '';
        $load_type = $_POST['type'];
        $number = preg_replace('/\D/', '', $raw); // digits only

        // If starts with country code 88 (eg. 8801878...), remove it
        if (strpos($number, '88') === 0) {
            $number = substr($number, 2);
        }

        // Ensure it starts with a 0 (local format)
        if ($number === '' || $number[0] !== '0') {
            $number = '0' . $number;
        }

        // Validate local format: 11 digits starting with 01
        if (!preg_match('/^01\d{9}$/', $number)) {
            echo 'Invalid number';
            exit;
        }

        $prefix = substr($number, 0, 3);

        $operator = "";

        // simple mapping using in_array (compatible with PHP 5+)
        if (in_array($prefix, ['018'])) {
            $operator = 'RB';
        } elseif (in_array($prefix, ['015'])) {
            $operator = 'TT';
        } elseif (in_array($prefix, ['014', '019'])) {
            $operator = 'BL';
        } elseif ($prefix === '017') {
            $operator = 'GP';
        } else {
            $operator = 'Unknown';
        }

        if($load_type == 3){
            $operator = "GP";
        }

        // echo "Number: $number, Operator: $operator";
        // ============================= GET THE OPERATOR =================================

        if ( $_POST['payment_method'] == "Online" ) {

            $invoice_no = $_POST['invoice_number'];
            $invoice_id = "C".$_SESSION["us_id"]."T".time()."INV".$invoice_no;

            $url = "https://sandbox.paystation.com.bd/initiate-payment";
            $postData = [
                'invoice_number' => $invoice_id,
                'currency' => 'BDT',
                'payment_amount' => $_POST['amount'],
                'reference' => $invoice_no,
                'cust_name' => 'Test',
                'cust_phone' => $_POST['number'],
                'cust_email' => 'test100@gmail.com',
                'cust_address' => 'Address',
                'callback_url' => 'https://sandbox.paystation.com.bd/payment-success/104',
                'checkout_items' => 'Sona-Rupa',
                'merchantId' => '104-1653730183',
                'password' => 'gamecoderstorepass',
                'opt_a' => '1',
                'opt_b' => '2',
                'opt_c' => '3'
            ];

            // Prepare data to insert
            $user_id           = $_SESSION["us_id"];
            $type              = $_POST['type'];
            $invoice_no        = mysqli_real_escape_string($connect, $postData['reference']);
            $file_name         = null;
            $comments          = mysqli_real_escape_string($connect, $postData['checkout_items']);
            $invoice_id        = mysqli_real_escape_string($connect, $postData['invoice_number']);
            $amount            = floatval($postData['payment_amount']);
            $payment_link      = null;
            $is_paid           = 0;
            $payment_trx_id    = null;
            $paid_at           = null;
            $is_started        = 0;
            $activation_status = 'active';
            $created_at        = date('Y-m-d H:i:s');
            $created_by        = 'admin';

            // Build SQL query
            $sql = "INSERT INTO recharge_request (
                user_id, type, invoice_no, file_name, comments, invoice_id, amount,
                payment_link, is_paid, payment_trx_id, paid_at, is_started, activation_status,
                created_at, created_by
            ) VALUES (
                '$user_id', '$type', '$invoice_no', NULL, '$comments', '$invoice_id', '$amount',
                NULL, '$is_paid', NULL, NULL, '$is_started', '$activation_status',
                '$created_at', '$created_by'
            )";

            // Run the query
            if (mysqli_query($connect, $sql)) {
                echo "Recharge request inserted successfully.";
            } else {
                echo "Error: " . mysqli_error($connect);
            }

            // Send Query Insert
            $phone      = mysqli_real_escape_string($connect, $_POST['number']);
            $balance    = intval($_POST['amount']);
            $submitted  = date('Y-m-d H:i:s');
            $status     = 0; // Pending or new
            $s_time     = date('YmdHis');
            $s_date     = date('Y-m-d');
            $level1     = $user_id;
            $level2     = '';
            $level3     = '';
            $level4     = '';
            $level5     = '';
            $ip         = $_SERVER['REMOTE_ADDR'] ?? '0';
            $rfrom      = 'web'; // Or use any source identifier
            $api_reid   = 0;
            $txnid      = "";
            $msg        = "Test";

            $sql2 = "INSERT INTO send_query (
                user_id, phone, load_type, balance, submitted_date, status, s_time,
                operator, s_date, level1, level2, level3, level4, level5,
                ip, rfrom, api_reid, txnid, msg
            ) VALUES (
                '$user_id', '$phone', '$load_type', '$balance', '$submitted', '$status', '$s_time',
                '$operator', '$s_date', '$level1', '$level2', '$level3', '$level4', '$level5',
                '$ip', '$rfrom', '$api_reid', '$txnid', '$msg'
            )";

            // Execute both queries
            if (mysqli_query($connect, $sql2)) {
                echo "Recharge request and send_query inserted successfully.";
            } else {
                echo "Error: " . mysqli_error($connect);
            }

            mysqli_close($connect);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postData
            ));
            $httpcode = curl_getinfo($curl);
            $resp = curl_exec($curl);
            curl_close($curl);
            // $data = json_decode($resp);
            echo $resp;
        } else {

            // Prepare data to insert
            $user_id           = $_SESSION["us_id"];
            $type              = $_POST['type'];
            $invoice_no        = mysqli_real_escape_string($connect, $postData['reference']);
            $file_name         = null;
            $comments          = mysqli_real_escape_string($connect, $postData['checkout_items']);
            $invoice_id        = mysqli_real_escape_string($connect, $postData['invoice_number']);
            $amount            = floatval($postData['payment_amount']);
            $payment_link      = null;
            $is_paid           = 0;
            $payment_trx_id    = null;
            $paid_at           = null;
            $is_started        = 0;
            $activation_status = 'active';
            $created_at        = date('Y-m-d H:i:s');
            $created_by        = 'admin';

            // Build SQL query
            $sql = "INSERT INTO recharge_request (
                user_id, type, invoice_no, file_name, comments, invoice_id, amount,
                payment_link, is_paid, payment_trx_id, paid_at, is_started, activation_status,
                created_at, created_by
            ) VALUES (
                '$user_id', '$type', '$invoice_no', NULL, '$comments', '$invoice_id', '$amount',
                NULL, '$is_paid', NULL, NULL, '$is_started', '$activation_status',
                '$created_at', '$created_by'
            )";

            // Run the query
            if (mysqli_query($connect, $sql)) {
                echo "Recharge request inserted successfully.";
            } else {
                echo "Error: " . mysqli_error($connect);
            }
            
            // Send Query Insert
            $user_id    = $_SESSION["us_id"];
            $phone      = mysqli_real_escape_string($connect, $_POST['number']);
            $balance    = intval($_POST['amount']);
            $submitted  = date('Y-m-d H:i:s');
            $status     = 0; // Pending or new
            $s_time     = date('YmdHis');
            $s_date     = date('Y-m-d');
            $level1     = $user_id;
            $level2     = '';
            $level3     = '';
            $level4     = '';
            $level5     = '';
            $ip         = $_SERVER['REMOTE_ADDR'] ?? '0';
            $rfrom      = 'web'; // Or use any source identifier
            $api_reid   = 0;
            $txnid      = "";
            $msg        = "Test";

            $sql2 = "INSERT INTO send_query (
                user_id, phone, load_type, balance, submitted_date, status, s_time,
                operator, s_date, level1, level2, level3, level4, level5,
                ip, rfrom, api_reid, txnid, msg
            ) VALUES (
                '$user_id', '$phone', '$load_type', '$balance', '$submitted', '$status', '$s_time',
                '$operator', '$s_date', '$level1', '$level2', '$level3', '$level4', '$level5',
                '$ip', '$rfrom', '$api_reid', '$txnid', '$msg'
            )";

            // Execute both queries
            if (mysqli_query($connect, $sql2)) {
                // echo "Recharge request and send_query inserted successfully.";
            } else {
                echo "Error: " . mysqli_error($connect);
            }

            // Query to update the balance
            $query = "UPDATE user SET balance2 = balance2 - $balance WHERE id = $user_id";

            // Execute the query
            $success = mysqli_query($connect, $query);

            mysqli_close($connect);

            echo json_encode([
                "status" => 200,
                "message" => "Recharge Request Submitted Successfully."
            ]);

        }
    break;

    case "topup_request":
        // echo json_encode($_POST);
        // exit();

        // ============================= GET THE OPERATOR =================================
        $raw = $_POST['number'] ?? '';
        $load_type = $_POST['type'];
        $number = preg_replace('/\D/', '', $raw); // digits only

        // If starts with country code 88 (eg. 8801878...), remove it
        if (strpos($number, '88') === 0) {
            $number = substr($number, 2);
        }

        // Ensure it starts with a 0 (local format)
        if ($number === '' || $number[0] !== '0') {
            $number = '0' . $number;
        }

        // Validate local format: 11 digits starting with 01
        if (!preg_match('/^01\d{9}$/', $number)) {
            echo 'Invalid number';
            exit;
        }

        $prefix = substr($number, 0, 3);

        $operator = "";

        // simple mapping using in_array (compatible with PHP 5+)
        if (in_array($prefix, ['018'])) {
            $operator = 'RR';
        } elseif (in_array($prefix, ['015'])) {
            $operator = 'RT';
        } elseif (in_array($prefix, ['014', '019'])) {
            $operator = 'RB';
        } elseif (in_array($prefix, ['017', '013'])) {
            $operator = 'RG';
        } elseif ($prefix === '016') {
            $operator = 'RA';
        }else {
            $operator = 'Unknown';
        }

        if($load_type == 3){
            $operator = "GP";
        }

        if($load_type == 1){
            $recharge_type = 'pre-paid';
        }

        if($load_type == 3){
            $recharge_type = 'post-paid';
        }

        $invoice_no = $_POST['invoice_number'];
        $invoice_id = "C".$_SESSION["us_id"]."T".time()."INV".$invoice_no;

        $postData = [
            'refer_no' => $invoice_id,
            'number' => $_POST['number'],
            'amount' => $_POST['amount'],
            'operator_type' => $operator,
            'recharge_type' => $recharge_type,
        ];

        // // Prepare data to insert
        // $user_id           = $_SESSION["us_id"];
        // $type              = $_POST['type'];
        // $invoice_no        = mysqli_real_escape_string($connect, $postData['reference']);
        // $file_name         = null;
        // $comments          = mysqli_real_escape_string($connect, $postData['checkout_items']);
        // $invoice_id        = mysqli_real_escape_string($connect, $postData['invoice_number']);
        // $amount            = floatval($postData['payment_amount']);
        // $payment_link      = null;
        // $is_paid           = 0;
        // $payment_trx_id    = null;
        // $paid_at           = null;
        // $is_started        = 0;
        // $activation_status = 'active';
        // $created_at        = date('Y-m-d H:i:s');
        // $created_by        = 'admin';

        // // Build SQL query
        // $sql = "INSERT INTO recharge_request (
        //     user_id, type, invoice_no, file_name, comments, invoice_id, amount,
        //     payment_link, is_paid, payment_trx_id, paid_at, is_started, activation_status,
        //     created_at, created_by
        // ) VALUES (
        //     '$user_id', '$type', '$invoice_no', NULL, '$comments', '$invoice_id', '$amount',
        //     NULL, '$is_paid', NULL, NULL, '$is_started', '$activation_status',
        //     '$created_at', '$created_by'
        // )";

        // // Run the query
        // if (mysqli_query($connect, $sql)) {
        //     echo "Recharge request inserted successfully.";
        // } else {
        //     echo "Error: " . mysqli_error($connect);
        // }

        // Send Query Insert
        $user_id    = $_SESSION["us_id"];
        $phone      = mysqli_real_escape_string($connect, $_POST['number']);
        $balance    = intval($_POST['amount']);
        $submitted  = date('Y-m-d H:i:s');
        $status     = 0; // Pending or new
        $s_time     = date('YmdHis');
        $s_date     = date('Y-m-d');
        $level1     = $user_id;
        $level2     = '';
        $level3     = '';
        $level4     = '';
        $level5     = '';
        $ip         = $_SERVER['REMOTE_ADDR'] ?? '0';
        $rfrom      = 'web'; // Or use any source identifier
        $api_reid   = 0;
        $txnid      = "";
        $msg        = "Test";

        $sql2 = "INSERT INTO send_query (
            user_id, phone, load_type, balance, submitted_date, status, s_time,
            operator, s_date, level1, level2, level3, level4, level5,
            ip, rfrom, api_reid, txnid, msg, refer_no
        ) VALUES (
            '$user_id', '$phone', '$load_type', '$balance', '$submitted', '$status', '$s_time',
            '$operator', '$s_date', '$level1', '$level2', '$level3', '$level4', '$level5',
            '$ip', '$rfrom', '$api_reid', '$txnid', '$msg', '$invoice_id'
        )";

        // Execute both queries
        if (mysqli_query($connect, $sql2)) {
            $last_id = mysqli_insert_id($connect);
            // echo "Recharge request and send_query inserted successfully.";
        } else {
            echo "Error: " . mysqli_error($connect);
        }

        // Send Recharge Request (API)
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://api.shl.com.bd:8282/recharge',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'username: 1035',
            'password: 2143',
            'auth_number: 01670869075'
        ),
        ));

        $httpcode = curl_getinfo($curl);
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        // echo $response;
        // // echo "<pre>";
        // // print_r($data);
        // echo $data['status_code'];
        // // echo $data->status_code;
        // exit();

        // --- api_log insert ---
        $api_log = [
            "type"      => "Recharge",
            "title"     => "Recharge_Request",
            "url"       => "http://api.shl.com.bd:8282/recharge",
            "request"   => json_encode($postData),
            "response"  => $response,
            "created_at"=> date("Y-m-d H:i:s"),
        ];

        $log_columns = implode(", ", array_keys($api_log));
        $log_values  = "'" . implode("','", array_map(function($v) use ($connect) {
            return mysqli_real_escape_string($connect, $v);
        }, $api_log)) . "'";

        $log_sql = "INSERT INTO api_log ($log_columns) VALUES ($log_values)";
        mysqli_query($connect, $log_sql);

        if($data['status_code'] == 2000){
            // Assuming $data is your decoded JSON response
            $pre_balance = $data['pre_balance'];
            $new_balance = $data['new_balance'];
            $message     = $data['message'];
            $trxId       = $data['trxId'];
            $refer_no    = $data['refer_no'];

            $query = "UPDATE send_query 
                    SET pre_balance = '$pre_balance', 
                        new_balance = '$new_balance', 
                        msg = '$message', 
                        txnid = '$trxId', 
                        refer_no = '$refer_no',
                        status = 1
                    WHERE id = $last_id";

            if (mysqli_query($connect, $query)) {
                // echo "Record updated successfully.";
            } else {
                echo "Error updating record: " . mysqli_error($connect);
            }

            // Query to update the balance
            $query = "UPDATE user SET balance2 = balance2 - $balance WHERE id = $user_id";

            // Execute the query
            $success = mysqli_query($connect, $query);

            mysqli_close($connect);
        }else{
            // $pre_balance = $data['pre_balance'];
            // $new_balance = $data['new_balance'];
            // $message     = $data['message'];
            // $trxId       = $data['trxId'];
            // $refer_no    = $data['refer_no'];

            $query = "UPDATE send_query 
                    SET status = 2
                    WHERE id = $last_id";

            if (mysqli_query($connect, $query)) {
                // echo "Record updated successfully.";
            } else {
                // echo "Error updating record: " . mysqli_error($connect);
            }
        }

        echo json_encode([
            "status" => 200,
            "message" => "Recharge Request Submitted Successfully."
        ]);

    break;
}