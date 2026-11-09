<?php 
include "login.check.php";
include "header.php";

$slot1_charge   = Mixedfn::billpaycharge("1","slot1");
$slot2_charge   = Mixedfn::billpaycharge("1","slot2");
$slot3_charge   = Mixedfn::billpaycharge("1","slot3");
$slot4_charge   = Mixedfn::billpaycharge("1","slot4");
?>
           

           
           
           <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>                                            
                                            <li class="breadcrumb-item active">Gas Bill Pay Request</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Gas Bill Pay Request</h4>

                                    <!-- Error Code Start-->

                                    <?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>							 
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">   
								
							  </button>
							</div>
							<?php
					  }?>
					<?php if (Session::exists('success')){
						?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
							  <strong>Well Done!</strong> <?=Session::Flush('success');?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">							 
								
							  </button>
							</div>
							<?php
					}?>

                     <!-- Error Code End-->

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-6 col-xl-6">
                                <div class="card text-center">
                                    <div class="card-body">

                             <!-- --><h5><span class="text-danger">বিল দেওয়ার ক্ষেত্রে অতিরিক্ত ফি লাগতে পারে। ফি এর লিস্ট দেখতে এখানে ক্লিক করুন। </span></h5>

                        <a data-bs-target="#myModal" data-bs-toggle="modal" class="for-custom-space" id="MainNavHelp" href="#myModal">List of Bill Pay Charge</a>

                                <form action="" role="form"  method="post" accept-charset="utf-8">

                                        <div class="text-start mt-3">

                                        <input type="hidden" name="type" id="type" value="Gas">

                                            <div class="mb-3">
                                                <label class="mb-2">Select Company <span class="text-danger">*</span></label>
                                                <br/>
                                                <select name="company" id="company" class="form-control">
                                                    <!-- <option value="Titas Gas" selected>Titas Gas</option> -->
                                                    <!-- <option value="Karnaphuli Gas">Karnaphuli Gas</option> -->
                                                    <option value="Jalalabad Gas">Jalalabad Gas</option>
                                                    <!-- <option value="Sundarban Gas">Sundarban Gas</option> -->
                                                    <option value="Bakhrabad Gas">Bakhrabad Gas</option>
                                                    <option value="Paschimanchal Gas">Paschimanchal Gas</option>  
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Customer No/Code<span class="text-danger">*</span></label>
                                                <input type="number" name="number" id="number" class="form-control" placeholder="Enter Account Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Customer Name<span class="text-danger">*</span></label>
                                                <input type="text" name="cs_name" id="cs_name" class="form-control" placeholder="Enter Customer Name" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Contact Number<span class="text-danger">*</span></label>
                                                <input type="number" name="contact_number" id="contact_number" class="form-control" placeholder="Enter Account Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                                            </div>
                                           
                                            <div class="button-list pe-xl-4 d-grid">
                                                    <button type="button" class="btn btn-lg btn-primary waves-effect waves-light" data-bs-remote="false" data-bs-toggle="modal" data-bs-target="#confirmation-modal-billpay">Submit</button>                                                    
                                            </div> 

                                    </form>   
                                        
                                        </div>                                    

                                       
                                    </div>                                 
                                </div> <!-- end card -->

                               



                            </div> <!-- end col-->

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
                                            <div class="tab-pane" id="aboutme">
    
                                                No Data


    
                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->
    
                                            <div class="tab-pane show active" id="timeline">
    
                                             
                                                <!-- Start Payment History Table-->


                                                <table id="basic-datatable" class="table table-striped dt-responsive w-100">
                                   
                                        
                                            <thead>
                                                    <tr>
                                                        
                                                    <th>Date</th>
                                                        <th>Company</th>
                                                        <th>Number</th>
                                                        <th>Amount</th>                                                        
                                                        <th>Status</th>
                                                        <th>ID</th>  
                                                                                                      
                                                    </tr>
                                                </thead>

                                            <tbody>
                                                                         
                    <?php 
                        $c=0;
						ini_set('memory_limit', '-1');
						$amountTotal ='';
                        $costTotal ='';
                        // $rows = isset($_POST['viewall']) ? $_POST['viewall'] : 30;
                         $rows = 20;
                         $i = 1; 
                        $paging = new Pagination($rows,'page');
                        $start = $paging->getStart();
                                            
                        $paging->setTotal(Report::totalBillPayHistoryById(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::history_BillPay_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                                          
							/* */
								if(!empty($res))
								{
									foreach($res as $pending):
								
								    $transactionid = Request::get_transaction_id($pending->id);
								    $trans = preg_replace("/.You/"," ",$transactionid);
								
                                    $c++;

                                    $username=username("$pending->user_id");
                                    $bossid = created_by("$pending->user_id");
                                    $bossname = username("$bossid");
						   ?>
                                   
                                           <tr>
                                              
                                           <td><?php echo $pending->submitted_date; ?></td> 
                                           <td><?php echo $pending->company;?></td>                                                 
                                           <td><?php echo $pending->ac_number;?></td>
                                            <td><?php echo $pending->balance; ?> </td>
                                             
                                            <td>

                                        <?php
											  if($pending->status == '0'):
													echo '<span class="badge bg-warning p-2">Pending</span>';
											  elseif($pending->status == '1'):
													echo '<span class="badge bg-success p-2">Success</span>';
											  elseif($pending->status == '2'):
													echo '<span class="badge bg-danger p-2">Cancelled</span>';
											  elseif ($pending->status == '4'):
													echo '<span class="badge bg-info p-2">Waiting</span>';
													
												elseif($pending->status == '3'):
													echo '<span class="badge bg-danger p-2">Failed</span>';	
											  endif;
										?> 
                                                </td>      
                                                                                             
                                                <td><?php echo $pending->id; ?> </td>
                                              
                                              
                                           </tr>                                              
                                          
                                          

                           <?php endforeach; 
                           }
                           else{
                           ?>
                           <tr>
                           <td>
                               No Data Found
                           </td>
                           </tr>
                       <?php
                           }
                       ?>


                                       </tbody>
                                   </table>

                             <!-- End Payment History Table-->

    
                                            </div>
                                            <!-- end timeline content-->
    
                                            <div class="tab-pane" id="settings">


                                                 <!-- Start Mobile Banking Payment History Table-->


                                                

                    <!-- End Mobile Banking Payment History Table-->



                                            </div>
                                            <!-- end settings content-->
    
                                        </div> <!-- end tab-content -->
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                    </div> <!-- container -->

                </div> <!-- content -->


    

<!-- Bill Pay modal start-->
<div id="confirmation-modal-billpay" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">                                                
        <div class="modal-content">   
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Gas Bill Pay Request Confirmation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>   
            
            <form action="route.php?action=requestBillPaySend" role="form" method="post" accept-charset="utf-8" id="billFetchSubmitForm">
                
                <div class="modal-body" id="load_request_data">
                                                        
                </div>    
                
                <div class="modal-footer">
                    <input type="hidden" name="service_type" id="service_type" value="bill_fetch_request">
                    <button type="button" class="btn btn-success" id="billFetchSubmitButton">Fetch</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Bill Pay modal End-->


<!-- Bill Payment Modal -->
<div id="confirmation-modal-billpayment" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">                                                
        <div class="modal-content">   
            <div class="modal-header">
                <h4 class="modal-title" id="myCenterModalLabel">Gas Bill Fetch Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>   
            
            <form action="" role="form" method="post" accept-charset="utf-8" id="billPaymentSubmitForm">
                <div class="modal-body" id="load_request_fetch_data">

                </div>    
        
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="billPaymentSubmitButton">Pay</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


 <!-- Standard modal content -->
 <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="standard-modalLabel">List of bill pay charge</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6 col-xl-6">
                        <div class="widget-rounded-circle card">
                            <div class="card-body">
                                <div class="row">                                            
                                    <div class="col-12">                                                
                                        <div class="text-end">
                                            <p class="text-dark mb-1">Slot1 (1 - 400 Taka)</p>
                                            <h3 class="text-dark mt-1"><?php echo $slot1_charge;?> Taka</h3>                                                                                              
                                        </div>
                                    </div>
                                </div> <!-- end row-->
                            </div>
                        </div> <!-- end widget-rounded-circle-->
                    </div> <!-- end col-->

                    <div class="col-md-6 col-xl-6">
                        <div class="widget-rounded-circle card">
                            <div class="card-body">
                                <div class="row">                                            
                                    <div class="col-12">                                                
                                    <div class="text-end">
                                            <p class="text-dark mb-1">Slot2 (401 - 1500 Taka)</p>
                                            <h3 class="text-dark mt-1"><?php echo $slot2_charge;?> Taka</h3>                                                                                              
                                        </div>
                                    </div>
                                </div> <!-- end row-->
                            </div>
                        </div> <!-- end widget-rounded-circle-->
                    </div> <!-- end col-->

                    <div class="col-md-6 col-xl-6">
                        <div class="widget-rounded-circle card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">                                                
                                    <div class="text-end">
                                            <p class="text-dark mb-1">Slot3 (1501 - 5000 Taka)</p>
                                            <h3 class="text-dark mt-1"><?php echo $slot3_charge;?> Taka</h3>                                                                                              
                                        </div>
                                    </div>
                                </div> <!-- end row-->
                            </div>
                        </div> <!-- end widget-rounded-circle-->
                    </div> <!-- end col-->

                    <div class="col-md-6 col-xl-6">
                        <div class="widget-rounded-circle card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">                                                
                                    <div class="text-end">
                                            <p class="text-dark mb-1">Slot4 (5001 - 30000 Taka)</p>
                                            <h3 class="text-dark mt-1"><?php echo $slot4_charge;?> Taka</h3>                                                                                              
                                        </div>
                                    </div>
                                </div> <!-- end row-->
                            </div>
                        </div> <!-- end widget-rounded-circle-->
                    </div> <!-- end col-->


                </div>
                                                            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>                                                        
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>

document.getElementById('billFetchSubmitButton').addEventListener('click', function() {
    var invoiceNumber = "INV" + Math.random().toString(36).substr(2, 9).toUpperCase();

    // var chk_validation = validation_check();
    // if(chk_validation == true)
    // {
        var request;
        var $form = $("#billFetchSubmitForm");
    
        var serializedData = $form.serialize();
        serializedData += "&invoice_number=" + encodeURIComponent(invoiceNumber);
        
        request = $.ajax({
            url: "process_bill_payment.php",
            type: "post",
            data: serializedData
        });
        
        request.done(function (response, textStatus, jqXHR){
            console.log(response);
            var data = JSON.parse(response);

            if (data.result == 'success') {
                // Build invoice table
                let invoiceHtml = '<table class="table table-bordered">';
                data.invoice.forEach(function(item) {
                    invoiceHtml += `
                        <tr ${item.is_total_row ? 'style="font-weight:bold;background:#f5f5f5;"' : ''}>
                            <td>${item.label_en}</td>
                            <td>${item.value}</td>
                        </tr>
                    `;
                });
                invoiceHtml += '</table>';

                // Append hidden values to form
                $("#billPaymentSubmitForm").append(`
                    <input type="hidden" name="bill_payment_id" value="${data.bill_ref.bill_payment_id}">
                    <input type="hidden" name="bill_refer_id" value="${data.bill_ref.bill_refer_id}">
                    <input type="hidden" name="bill_total_amount" value="${data.data.bill_total_amount}">
                    <input type="hidden" name="service_type" value="bill_payment_request">
                `);

                // Insert into modal body
                $("#load_request_fetch_data").html(invoiceHtml);

                // Show modal
                $("#confirmation-modal-billpayment").modal("show");
                $("#confirmation-modal-billpay").modal("hide");

                // swal("Fetch Successfully", '', 'success');
                // setTimeout(function () {
                //     location.reload();
                // }, 3000); // 3 seconds
            } else {
                swal(data.message, '', 'warning');
            }
        });
    
        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });
    // }
});

document.getElementById('billPaymentSubmitButton').addEventListener('click', function() {

    var request;
    var $form = $("#billPaymentSubmitForm");

    var serializedData = $form.serialize();
    
    request = $.ajax({
        url: "process_bill_payment.php",
        type: "post",
        data: serializedData
    });
    
    request.done(function (response, textStatus, jqXHR){
        console.log(response);
        var data = JSON.parse(response);

        if (data.result == 'success') {
            
            // Insert into modal body
            // $("#load_request_fetch_data").html(invoiceHtml);

            // Show modal
            $("#confirmation-modal-billpayment").modal("hide");

            swal(data.message, "", "success");
            setTimeout(function () {
                location.reload();
            }, 2000);
        } else {
            swal(data.message, '', 'warning');
        }
    });

    // Callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        console.error(
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });

});

function validation_check()
{
    if($("#number").val()=="" || $("#number").val().length > 11 || $("#number").val().length < 11)
    {
        swal("Phone Number is empty or invalid length").then(function(){
            $("#number").focus();
        });
        return false;
    }
    else if($("#amount").val()=="" || $("#amount").val() < 0)
    {
        swal("Amount is empty").then(function(){
            $("#amount").focus();
        });
        return false;
    }
    else if(!$("#payment-method").val())
    {
        swal("Select Payment Method First").then(function(){
            $("#payment-method").focus();
        });
        return false;
    }
    else
    {
        return true;
    }
}

</script>

<?php    
include "footer-data.php";
?>
