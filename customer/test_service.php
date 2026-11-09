<?php
include "login.check.php";
include "header.php";
?>

<!-- Content Page -->
<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <!-- Start Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="customer_list.php">Customer List</a></li>
                                <li class="breadcrumb-item active">Customer Profile</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Test Service</h4>
                        <!-- Error Code Start -->
                        <?php if (Session::exists('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= Session::flush('error'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (Session::exists('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Well Done!</strong> <?= Session::flush('success'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <!-- Error Code End -->
                    </div>
                </div>
            </div>
            <!-- End Page Title -->
            <div class="row">
                <div class="col-lg-6 col-xl-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <form action="route.php?action=testService" role="form" method="post" accept-charset="utf-8" id="testServiceForm">
                                <div class="text-start mt-3">
                                    <div class="mb-3">
                                        <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                                        <span class="request-page-balance">Current Balance: Tk. <strong><?php echo $re_balance; ?></strong></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment-method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="" selected disabled>Select Payment Method</option>
                                            <option value="Wallet">Pay With Wallet</option>
                                            <option value="Online">Online Payment</option>
                                        </select>
                                    </div>
                                    <div class="button-list pe-xl-4 d-grid">
                                        <input type="hidden" name="service_type" id="service_type" value="topup">
                                        <!-- <button type="button" class="btn btn-lg btn-primary waves-effect waves-light" id="submitButton">Submit</button> -->
                                        <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>  
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills nav-fill navtab-bg">
                                <li class="nav-item">
                                    <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                        Last 10 History
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane show active" id="timeline">
                                    <table id="basic-datatable" class="table table-striped dt-responsive w-100">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Payment Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Invoice No</th>
                                                <th>ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $c = 0;
                                            ini_set('memory_limit', '-1');
                                            $amountTotal = '';
                                            $costTotal = '';
                                            $rows = 20; // Number of rows per page
                                            $i = 1;
                                            $paging = new Pagination($rows, 'page');
                                            $start = $paging->getStart();

                                            // Set the total number of records for pagination
                                            $paging->setTotal(Report::totalFlexiHistoryById(Session::get('us_id')));

                                            // Modify the query to fetch from testservice_request table with pagination
                                            $query = "SELECT * FROM testservice_request WHERE user_id = " . Session::get('us_id') . " LIMIT " . $start . ", " . $rows;
                                            $res = DB::getInstance()->get($query)->result();

                                            if (!empty($res)) {
                                                foreach ($res as $pending) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $pending->submitted_date; ?></td>
                                                        <td><?php echo $pending->wallet_type; ?></td>
                                                        <td><?php echo $pending->amount; ?></td>
                                                        <td>
                                                            <?php
                                                            if ($pending->status == '0') {
                                                                echo '<span class="badge bg-warning p-2">Pending</span>';
                                                            } elseif ($pending->status == '1') {
                                                                echo '<span class="badge bg-success p-2">Success</span>';
                                                            } elseif ($pending->status == '2') {
                                                                echo '<span class="badge bg-danger p-2">Cancelled</span>';
                                                            } elseif ($pending->status == '4') {
                                                                echo '<span class="badge bg-info p-2">Waiting</span>';
                                                            } elseif ($pending->status == '3') {
                                                                echo '<span class="badge bg-danger p-2">Failed</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo $pending->invoice_no; ?></td>
                                                        <td><?php echo $pending->id; ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <tr>
                                                    <td>No Data Found</td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Iframe Container -->
<div id="payment-iframe-container" style="display: none;">
    <iframe id="payment-iframe" width="100%" height="500px" frameborder="0"></iframe>
</div>

<!-- Recharge Request Modal -->
<div id="confirmation-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Recharge Request Confirmation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="route.php?action=requestSend" role="form" method="post" accept-charset="utf-8">
                <div class="modal-body" id="load_request_data"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Next</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // document.getElementById('submitButton').addEventListener('click', function() {
    //     var paymentMethod = document.getElementById('payment-method').value;
    //     var number = document.getElementById('number').value;
    //     var amount = document.getElementById('amount').value;
    //     var type = document.querySelector('input[name="type"]:checked').value;
    //     var invoiceNumber = "INV" + Math.random().toString(36).substr(2, 9).toUpperCase();

    //     var chk_validation = validation_check();
    //     if(chk_validation == true)
    //     {
    //         var request;
    //         var $form = $("#topupSubmitForm");
        
    //         var serializedData = $form.serialize();
    //         serializedData += "&invoice_number=" + encodeURIComponent(invoiceNumber);
            
    //         request = $.ajax({
    //             url: "process_payment.php",
    //             type: "post",
    //             data: serializedData
    //         });
            
    //         request.done(function (response, textStatus, jqXHR){
    //             // // $('#loads').addClass('hidden');
    //             // //$("#msg").text(response);
    //             // var data = JSON.parse(response);
    //             // if (data.status == 200) {
    //             //     // $('.resultDiv').addClass('green');
    //             //     // message_dialog_final(data);
    //             // }else{
    //             //     // $('.resultDiv').addClass('red');
    //             //     // message_dialog_final(data);
    //             // }
    //             // $("#trans_id").val("");
    //             // $("#total_amount").val("0");
    //             // $("#service_type").val("WASA_Enquiry");
    //             // $("#bill_no").val("");
    //             // $("#phone").val("");
    //             // $("#pin").val("");
    //             console.log(response);
    //         });
        
    //         // Callback handler that will be called on failure
    //         request.fail(function (jqXHR, textStatus, errorThrown){
    //             // Log the error to the console
    //             console.error(
    //                 "The following error occurred: "+
    //                 textStatus, errorThrown
    //             );
    //         });
    //     }
    // });

    // function validation_check()
    // {
    //     if($("#number").val()=="" || $("#number").val().length > 11 || $("#number").val().length < 11)
    //     {
    //         swal("Phone Number is empty or invalid length").then(function(){
    //             $("#number").focus();
    //         });
    //         return false;
    //     }
    //     else if($("#amount").val()=="" || $("#amount").val() < 0)
    //     {
    //         swal("Amount is empty").then(function(){
    //             $("#amount").focus();
    //         });
    //         return false;
    //     }
    //     else
    //     {
    //         return true;
    //     }
    // }
</script>

<?php
include "footer-data.php";
?>
