<?php 
include "login.check.php";
include "header.php";
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
                                            <li class="breadcrumb-item active">Others M. Bank New Request</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Others M. Bank New Request</h4>

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

                                <form action="" role="form"  method="post" accept-charset="utf-8">

                                        <div class="text-start mt-3">

                                        <div class="mb-3">
                                                <label class="mb-2">Select Company <span class="text-danger">*</span></label>
                                                <br/>
                                                <select class="form-control" name="bank_name" id="bank_name">
                                                    <option value="MCash">MCash</option>
                                                    <option value="SureCash">Sure Cash</option>
                                                    <option value="UCash">UCash</option>
                                                    <option value="TCash">TCash</option> 
                                                    <option value="OkWallet">Ok Wallet</option>                     
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Account Number<span class="text-danger">*</span></label>
                                                <input type="number" name="m_number" id="m_number" class="form-control" placeholder="Enter Sending Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "5" required>
                                                <span class="request-page-balance">Current M. Bank Balance: Tk. <strong><?php echo $mb_balance;?></strong> </span>
                                            </div>


                                            <div class="mb-3">
                                                <label class="mb-2">Select Type <span class="text-danger">*</span></label>
                                                <br/>
                                                <select class="form-control" name="m_type" id="m_type">
                                                    <option value="CashIn">Cash In</option>
                                                    <option value="CashOut">Cash Out</option>
                                                    <option value="SendMoney">Send Money</option>
                                                    <option value="Payment">Payment</option>                      
                                                </select>
                                            </div>

                                                <div class="button-list pe-xl-4 d-grid">
                                                        <button type="button" class="btn btn-lg btn-primary waves-effect waves-light" data-bs-remote="false" data-bs-toggle="modal" data-bs-target="#confirmation-modal-mbank">Submit</button>                                                    
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
                                                        <th>Type</th>
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
                                            
                        $paging->setTotal(Report::totalMbankiHistoryById(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::history_m_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                                          
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
                                           <td><?php echo $pending->company_name;?></td>                                                 
                                           <td><?php echo $pending->ac_number;?></td>
                                            <td><?php echo $pending->amount; ?> </td>
                                             
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
                                             
                                                <td><?php echo $pending->type; ?> </td>
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


    

<!-- Recharge Request modal start-->
<div id="confirmation-modal-mbank" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Mobile Banking Request Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>   
                    
        <form action="route.php?action=requestMBSend" role="form" method="post" accept-charset="utf-8">
              
            <div class="modal-body" id="load_request_data">
                                                       
            </div>    
            
            
                <div class="modal-footer">
					<button type="submit" class="btn btn-success">Next</button>
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
				</div>
        </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Request modal End-->



<?php    
include "footer-data.php";
?>
