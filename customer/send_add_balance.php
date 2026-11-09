<?php 
include "login.check.php";
include "header.php";

$query= "SELECT * from payment_account where id='1'";

$result           = mysqli_query($connect, $query);
$reseller_data    = mysqli_fetch_object($result);
$bkash_agent      = $reseller_data->bkash_agent ?? '01700000000';
$bkash_personal	  = $reseller_data->bkash_personal ?? '01700000000';
$rocket_agent     = $reseller_data->rocket_agent ?? '01700000000';
$rocket_personal  = $reseller_data->rocket_personal ?? '01700000000';
$nagad_agent      = $reseller_data->nagad_agent ?? '01700000000';
$nagad_personal   = $reseller_data->nagad_personal ?? '01700000000';


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
                                            <li class="breadcrumb-item active">Add Balance Request</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Balance Request</h4>

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

                                    <ul class="nav nav-pills nav-fill navtab-bg">
                                            <li class="nav-item">
                                                <a href="#aboutme" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Bank Transfer
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Mobile Banking
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Online
                                                </a>
                                            </li>
                                        </ul>


                                    <div class="tab-content"> 

                                        <div class="tab-pane" id="aboutme">
    
                                        Comming Soon....


                                        </div> <!-- end tab-pane -->
                                        <!-- end about me section content -->

                                        <div class="tab-pane show active" id="timeline">
    
                                        <form action="route.php?action=addFund" method="post" accept-charset="utf-8">
                                       
                                       <div class="text-start mt-3">

                                                <div class="mb-3">
                                                        <label class="mb-2">Balance Type <span class="text-danger">*</span></label>
                                                        <br/>
                                                        <select name="bltype" class="form-control" id="bltype">
                                                            <option value="Recharge">Recharge Balance</option>
                                                            <option value="Mobile Banking">Mobile Banking Balance</option>                                                                                      
                                                        </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                    <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                                                    <span class="request-page-balance">Current Balance: Recharge: Tk. <strong><?php echo $re_balance;?></strong> M. Bank: <strong><?php echo $mb_balance;?></strong></span>
                                                </div>
                                                <br>
                                                <h3 class="page-title">Payment Information</h3> <br>

                                                <div class="mb-3">
                                                        <label class="mb-2">Company <span class="text-danger">*</span></label>
                                                        <br/>
                                                        <select name="mcategory" class="form-control" id="mcategory">
                                                            <option value="bKash">bKash</option>
                                                            <option value="Nagad">Nagad</option>
                                                            <option value="Rocket">Rocket</option>
                                                            <option value="Upay">Upay</option>                                                        
                                                        </select>
                                                </div>

                                                <div class="mb-3">
                                                        <label class="mb-2">Type <span class="text-danger">*</span></label>
                                                        <br/>
                                                        <select name="mtype" class="form-control" id="mtype">
                                                            <option value="Cash In">Cash In</option>
                                                            <option value="Cash Out">Cash Out</option>
                                                            <option value="Send Money">Send Money</option>
                                                            <option value="Payment">Payment</option>                        
                                                        </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Payment Number<span class="text-danger">*</span></label>
                                                    <input type="number" name="acnumber" id="acnumber" class="form-control" placeholder="Enter Payment Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>                                                    
                                                    <a data-bs-target="#myModal" data-bs-toggle="modal" class="for-custom-space" id="MainNavHelp" href="#myModal">List of Payment Number</a>
                                                </div>


                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Transaction ID<span class="text-danger">*</span></label>
                                                    <input type="text" name="transid" id="transid" class="form-control" placeholder="Enter Transaction ID" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "15" required>                                                   
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Sending Number<span class="text-danger">*</span></label>
                                                    <input type="number" name="sendnumber" id="sendnumber" class="form-control" placeholder="Enter Sending Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>                                                   
                                                </div>


                                                <div class="button-list pe-xl-4 d-grid">
                                                    <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                                                </div> 

                                   </form>
                                       
                                           </div> 
                                           

                                        </div> <!-- end tab-pane -->
                                        <!-- end timeline section content -->

                                        <div class="tab-pane" id="settings">
    
                                           <div class="tab-pane show active" id="timeline">
                                                <form action="route.php?action=addFundThroughOnline" method="post" accept-charset="utf-8">
                                                    <div class="text-start mt-3">
                                                        <div class="mb-3">
                                                            <label class="mb-2">Balance Type <span class="text-danger">*</span></label>
                                                            <br/>
                                                            <select name="bltype" class="form-control" id="bltype">
                                                                <option value="recharge">Recharge Balance</option>
                                                                <option value="mobile_banking">Mobile Banking Balance</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                                                            <span class="request-page-balance">Current Balance: Recharge: Tk. <strong><?php echo $re_balance; ?></strong> M. Bank: <strong><?php echo $mb_balance; ?></strong></span>
                                                        </div>
                                                        <br>
                                                        <div class="button-list pe-xl-4 d-grid">
                                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                        </div> <!-- end tab-pane -->
                                        <!-- end about me section content -->

                                    
                                    </div> <!-- end tab-content -->



                                   
                                                                               

                                       
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
                                                    <th>Balance Type</th>
                                                    <th>Amount</th>                                                    
                                                    <th>Status</th>
                                                    <th>Payment Number</th>
                                                    <th>Payment Type</th>
                                                    <th>Company</th>
                                                    <th>Type</th>
                                                    <th>Sending Number</th>
                                                    <th>Transaction ID</th>                                                   
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
                                            
                        $paging->setTotal(Report::add_totalPaymentHistory(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::add_payment_history(Session::get('us_id')) . $paging->getLimit())->result();
                                         
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
                                              
                                           <td><?php echo $pending->date; ?></td>                                                  
                                           <td><?php echo $pending->balance_type;?></td>
                                           <td><?php echo $pending->amount;?></td>
                                          
                                             
                                           <td>

                                                <?php
                                                    if($pending->status == '0'):
                                                            echo '<span class="badge bg-warning p-2">Proccessing</span>';
                                                    elseif($pending->status == '1'):
                                                            echo '<span class="badge bg-success p-2">Accepted</span>';
                                                    elseif($pending->status == '2'):
                                                            echo '<span class="badge bg-danger p-2">Cancelled</span>';											  
                                                        elseif($pending->status == '3'):
                                                            echo '<span class="badge bg-danger p-2">Failed</span>';	
                                                    endif;
                                                ?> 
                                            </td>
                                            <td><?php echo $pending->account_number;?></td>
                                             
                                            <td><?php echo $pending->category;?></td>
                                                <td><?php echo $pending->mcategory;?></td>
                                                <td><?php echo $pending->type;?></td>
                                                <td><?php echo $pending->sending_number;?></td>
                                                <td><?php echo $pending->transaction_id;?></td>
                                                 
                                                
                                                   <td><?php echo $pending->id; ?></td>
                                              
                                              
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

                
                                 <!-- Standard modal content -->
                                    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="standard-modalLabel">List of Payment Number</h4>
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
                                                                                <p class="text-danger mb-1">bKash Agent Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $bkash_agent;?></h3>                                                                                              
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
                                                                                <p class="text-danger mb-1 text-truncate">bKash Personal Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $bkash_personal;?></h3>                                                                                              
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
                                                                                <p class="text-primary mb-1 text-truncate">Rocket Agent Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $rocket_agent;?></h3>                                                                                              
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
                                                                                <p class="text-primary mb-1 text-truncate">Rcoket Personal Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $rocket_personal;?></h3>                                                                                              
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
                                                                                <p class="text-warning mb-1 text-truncate">Nagad Agent Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $nagad_agent;?></h3>                                                                                              
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
                                                                                <p class="text-warning mb-1 text-truncate">Nagad Personal Number</p>
                                                                                    <h3 class="text-dark mt-1"><?php echo $nagad_personal;?></h3>                                                                                              
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
                                   

<?php    
include "footer-data.php";
?>

