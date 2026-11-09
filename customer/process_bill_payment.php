<?php
// echo "Got this";
// exit();
require_once 'autoload.php';
session_start();
include "../db.php";
// echo json_encode($_POST);
	$action = $_REQUEST["service_type"];

    function getCharge($amount) {
        /* Start Bill Pay Charge */
        $slot1_charge = Mixedfn::billpaycharge("1","slot1");
        $slot2_charge = Mixedfn::billpaycharge("1","slot2");
        $slot3_charge = Mixedfn::billpaycharge("1","slot3");
        $slot4_charge = Mixedfn::billpaycharge("1","slot4");
        /* End Bill Pay Charge */

        if ($amount >= 1 && $amount <= 400) {
            return $slot1_charge;
        } elseif ($amount >= 401 && $amount <= 1500) {
            return $slot2_charge;
        } elseif ($amount >= 1501 && $amount <= 5000) {
            return $slot3_charge;
        } elseif ($amount >= 5001 && $amount <= 30000) {
            return $slot4_charge;
        }

        return 0; // fallback if amount is outside range
    }

	switch ($action) {
        case "bill_fetch_request":

        $time = time();

        $number          = $_POST['number'];
        $amount          = $_POST['amount'];
        $type            = $_POST['type'];
        $company         = $_POST['company'];
        $company_type    = $_POST['company_type'];
        $cs_name         = $_POST['cs_name'];
        $month           = $_POST['month'];
        $ownpin          = $_POST['pincode'];
        $contact_number  = $_POST['contact_number']; 
        $userlevel       = Session::get('level');
        $us_id           = Session::get('us_id');

        $cost  = $amount; 
        
        /* Function for prevent of sql injection */
         global $connect;
         $number         = stripcslashes($number);     
         $amount         = stripcslashes($amount);
         $type           = stripcslashes($type);
         $company        = stripcslashes($company);
         $company_type   = stripcslashes($company_type);
         $cs_name        = stripcslashes($cs_name);
         $month          = stripcslashes($month); 
         $contact_number = stripcslashes($contact_number);     
         $ownpin         = stripcslashes($ownpin);

               
         $number         = mysqli_real_escape_string($connect, $number); 
         $amount         = mysqli_real_escape_string($connect, $amount);
         $type           = mysqli_real_escape_string($connect, $type);
         $company        = mysqli_real_escape_string($connect, $company);
         $company_type   = mysqli_real_escape_string($connect, $company_type);
         $cs_name        = mysqli_real_escape_string($connect, $cs_name);
         $month          = mysqli_real_escape_string($connect, $month); 
         $contact_number = mysqli_real_escape_string($connect, $contact_number);        
         $ownpin = mysqli_real_escape_string($connect, $ownpin);    

        $ip=$_SERVER["REMOTE_ADDR"];
        $refrom='web';
        $charge = 0;

        $r1 = $us_id;

        $query = "SELECT * from user where id='$r1'";
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);        
        $r5 = $reseller_data->created_by;

        // echo json_encode($_POST);
        // exit();

        if($company=="Palli Bidyut"){

            $date = DateTime::createFromFormat("F Y", $month);

            $monthActual = $date->format("m"); // 09
            $yearActual  = $date->format("Y"); // 2025

            $curl = curl_init();

            $requestData = array('bill_no' => $number,'bill_month' => $monthActual,'bill_year' => $yearActual);
            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/palli-bidyut-postpaid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                $bill_month = $fetchData['data']['bill_month'];
                $bill_year = $fetchData['data']['bill_year'];

                $charge = getCharge($bill_total_amount);
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '$month', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="BPDB"){

            // $date = DateTime::createFromFormat("F Y", $month);

            // $monthActual = $date->format("m"); // 09
            // $yearActual  = $date->format("Y"); // 2025

            $curl = curl_init();

            $requestData = array('meter_no' => $number,'biller_mobile_no' => $contact_number,'amount' => $amount);
            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/bpdb-prepaid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                $responseData['result'] = "failed";
                $responseData['message'] = $fetchData['message'];
                echo json_encode($responseData);
            }

        }

        if($company=="NESCO"){

            $curl = curl_init();

            $requestData = array('bill_no' => $number, 'biller_mobile_no' => $contact_number);
            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/nesco-postpaid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                $customer_name = $fetchData['data']['customer_name'];
                $meter_type = $fetchData['data']['meter_type'];
                $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="West Zone"){

            $curl = curl_init();

            $requestData = array('biller_acc_no' => $number, 'biller_mobile_no' => $contact_number);
            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/westzonepower-postpaid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="DESCO"){

            $curl = curl_init();

            if($company_type=='Postpaid'){
                $requestData = array('bill_no' => $number, 'biller_mobile_no' => $contact_number);
                $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/desco-postpaid';
            }

            if($company_type=='Prepaid'){
                $requestData = array('biller_acc_no' => $number, 'biller_mobile_no' => $contact_number, 'amount' => $amount);
                $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/desco-prepaid';
            }

            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="Dhaka WASA"){

            $curl = curl_init();

            $requestData = array('bill_no' => $number, 'biller_mobile_no' => $contact_number);
            $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/dhaka-wasa';

            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="Jalalabad Gas"){

            $company_type  = "Postpaid";

            $curl = curl_init();

            $requestData = array('biller_acc_no' => $number, 'biller_mobile_no' => $contact_number);
            $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/jalalabad-gas';

            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="Bakhrabad Gas"){

            $company_type  = "Postpaid";

            $curl = curl_init();

            $requestData = array('biller_acc_no' => $number, 'biller_mobile_no' => $contact_number);
            $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/bakhrabad-gas';

            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        if($company=="Paschimanchal Gas"){

            $company_type  = "Postpaid";

            $curl = curl_init();

            $requestData = array('biller_acc_no' => $number, 'biller_mobile_no' => $contact_number);
            $url = 'https://shl.com.bd/api/appapi/billpay/fetch/sandbox/paschimanchal-gas';

            // echo json_encode($requestData);

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_HTTPHEADER => array(
                'token: randomToken'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            // echo $response;

            $fetchData = json_decode($response, true);

            // Check if the result is "success"
            if ($fetchData['result'] == 'success') {
                // Store bill_ref values
                $bill_payment_id = $fetchData['bill_ref']['bill_payment_id'];
                $bill_refer_id = $fetchData['bill_ref']['bill_refer_id'];

                // Store data values
                $bill_name = $fetchData['data']['bill_name'];
                $bill_no = $fetchData['data']['bill_no'];
                $biller_acc_no = $fetchData['data']['biller_acc_no'];
                $biller_mobile = $fetchData['data']['biller_mobile'];
                $bill_amount = $fetchData['data']['bill_amount'];
                $bill_vat = $fetchData['data']['bill_vat'];
                $vendor_charge = $fetchData['data']['charge'];
                $bill_total_amount = $fetchData['data']['bill_total_amount'];
                $is_bill_paid = $fetchData['data']['is_bill_paid'];
                $bllr_id = $fetchData['data']['bllr_id'];
                // $customer_name = $fetchData['data']['customer_name'];
                // $meter_type = $fetchData['data']['meter_type'];
                // $tariff_program = $fetchData['data']['tariff_program'];
                // $bill_month = $fetchData['data']['bill_month'];
                // $bill_year = $fetchData['data']['bill_year'];
            
                $query  = "INSERT INTO billpay_request (
                    user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost,
                    billing_month, contact_number, submitted_date, s_time, s_date, level1, level2, level3, level4, level5,
                    ip, rfrom, txnid, status, trx_status, bill_payment_id, bill_refer_id,
                    bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount, is_bill_paid, biller_id,
                    created_at
                ) VALUES (
                    '$us_id', '$type', '$company', '$cs_name', '$number', '$company_type', '$bill_amount', '$charge', '$bill_total_amount',
                    '', '$contact_number', NOW(), '$time', CURDATE(), '0', '0', '0', '$r1', '$r5',
                    '$ip', '$refrom', '0', '0', '1', '$bill_payment_id', '$bill_refer_id',
                    '$bill_no', '$biller_acc_no', '$biller_mobile', '$bill_amount', '$bill_vat', '$vendor_charge', '$bill_total_amount', '$is_bill_paid', '$bllr_id',
                    NOW()
                )";
 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));
                }

                echo $response;
            } else {
                // Handle error case
                echo "Error: " . ($fetchData['message'] ?? "Failed to fetch invoice.");
            }

        }

        break;

        case "bill_payment_request":

            $userlevel       = Session::get('level');
            $us_id           = Session::get('us_id');

            $bill_payment_id = $_POST['bill_payment_id'];

            $query="SELECT * from billpay_request where bill_payment_id='$bill_payment_id'";
            $row=mysqli_query($connect, $query);
            $reseller_data= mysqli_fetch_object($row);
            $bill_total_amount = $reseller_data->bill_total_amount;

            $query = "UPDATE user set bkash_balance=bkash_balance-'$bill_total_amount' where id='$us_id'";
            $result = mysqli_query($connect, $query);    
            if(!$result){
                
                die("Query Failed!" . mysqli_error($connect));
            }

            $query = "UPDATE billpay_request set trx_status = 2, is_bill_paid = 'Y', status = 1 where bill_payment_id='$bill_payment_id'";
            $result = mysqli_query($connect, $query);    
            if(!$result){
                
                die("Query Failed!" . mysqli_error($connect));
            }

            $response['result'] = 'success';
            $response['message'] = 'Bill payment processed successfully';
            echo json_encode($response);
        break;

    }