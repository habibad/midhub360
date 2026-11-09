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
                                            <li class="breadcrumb-item active">Internet Package New Request</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Internet Package New Request</h4>

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

                                                        <h3 class="page-title">Selected Operator: <?php echo $_GET['op_name'] ?></h3><br><br>
                                                        <br>

                                                        <?php foreach(Package::getPkg($_GET['op_id']) as $package): ?>

                                                            <div class="col-md-12 col-xl-6">
                                                                
                                                                
                                                                                                        
                                                            <a href="package_request.php" onclick="event.preventDefault();
                                                        document.getElementById('pkg-form-<?php echo $package->id ?>').submit();">
                                                            <form action="package_request.php" method="POST" style="display: none" id="pkg-form-<?php echo $package->id ?>">
                                                                <input type="text" value="<?php echo $package->id ?>" name="pkg_id" />
                                                                <input type="text" value="<?php echo $_GET['op_id'] ?>" name="op_id" />
                                                                <input type="text" value="<?php echo $_GET['op_name'] ?>" name="op_name" />
                                                                <input type="text" value="<?php echo $package->package_name ?>" name="pkg_name" />
                                                                <input type="text" value="<?php echo $package->amount ?>" name="pkg_amount" />
                                                                <input type="text" value="<?php echo $package->balance ?>" name="pkg_balance" />
                                                            </form>
                                                            <div class="card mb-2" style="background-color: #652d82; color: white; border-radius: 5px;">
                                                                <div class="card-body">
                                                                    <p style="font-size: 170%"><?php echo $package->amount ?> Tk</p>
                                                                    <p style="font-size: 120%"><?php echo $package->package_name ?></p>
                                                                    <p style="font-size: 100%">Commission: <?php echo $package->commission ?> Tk</p>
                                                                </div>
                                                            </div>
                                                        </a>
                                                                                    
                                      
                                                            </div> <!-- end col-->

                                                            <?php endforeach; ?>

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
                                            
                        $paging->setTotal(Report::totalFlexiHistoryById(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::history_flexi_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                          
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
                                           <td><?php echo $pending->phone;?></td>
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
