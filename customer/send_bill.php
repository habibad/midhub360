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
                                            <li class="breadcrumb-item active">Bill Pay New Request Profile</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Bill Pay New Request</h4>

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
                                       
                                        <div class="text-start mt-3">

                                                <div class="row">

                                                        <h4 class="page-title">Select Service</h4><br><br>
                                                       
                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="bill_electricity.php">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                                                <i class="mdi mdi-lightbulb-on-outline font-22 avatar-title text-primary"></i>
                                                                            </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">Electricity</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="bill_gas.php">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                                                
                                                                                    <img src="../assets/images/icon/gas.png" class="dash-img" alt="Gas Bill Pay">                                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">Gas</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="bill_water.php">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                                                
                                                                                    <img src="../assets/images/icon/water.png" class="dash-img" alt="Water Bill">                                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">Water</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="#">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                                                
                                                                                    <img src="../assets/images/icon/internet.png" class="dash-img" alt="Internet BIll">                                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">Internet</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="#">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                                                
                                                                                    <img src="../assets/images/icon/telephone.png" class="dash-img" alt="Telephone BIll">                                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">Telephone</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="widget-rounded-circle card">
                                                                <a href="#">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                                                
                                                                                    <img src="../assets/images/icon/tv.png" class="dash-img" alt="TV BIll">                                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="text-end">                                                   
                                                                                    <p class="text-muted mb-1 text-truncate">TV</p>
                                                                                </div>
                                                                            </div>
                                                                        </div> <!-- end row-->
                                                                    </div>
                                                                    </a>  
                                                                </div> <!-- end widget-rounded-circle-->
                                                            </div> <!-- end col-->

                                                           

                                                </div>
                                            <!-- end row-->


                                          
                                        
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
                                                        <th>Cost</th>                                                        
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
                                            <td><?php echo $pending->cost; ?> </td>
                                                                                        
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


    

<!-- Recharge Request modal start-->
<div id="confirmation-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Recharge Request Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>   
                    
        <form action="route.php?action=requestSend" role="form" method="post" accept-charset="utf-8">
              
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
