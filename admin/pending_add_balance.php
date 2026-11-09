<?php 
include "login.check.php";
include "header.php";

$total_amount = Mixedfn::totalpendingaddfund();
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
                                            <li class="breadcrumb-item active">Recharge History</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Balance Request Pending History</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                               
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>


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

                                         <div class="total-balance">
                                            <span>Total Pending Amount: </span><span class="text-danger"><strong><?php echo $total_amount; ?></strong></span>
                                        </div> <br>                                      
                                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                    <th>SL.</th>
                                                    <th>Date</th>
                                                    <th>Username</th>
                                                    <th>Created</th>
                                                    <th>Balance Type</th>
                                                    <th>Amount</th>
                                                    <th>Payment Number</th>
                                                    <th>Status</th>
                                                    <th>Payment Type</th>
                                                    <th>Company</th>
                                                    <th>Type</th>
                                                    <th>Sending Number</th>
                                                    <th>Transaction ID</th>
                                                    <th>Action</th>
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
                         $rows = 50;
                         $i = 1; 
                        $paging = new Pagination($rows,'page');
                        $start = $paging->getStart();
                                            
                        $paging->setTotal(Report::totalPendingAddBalreqHistoryById(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::pending_balancereq_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                         
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

                                    $pending_status = $pending->status;


						   ?>
                                        
                                        
                                                <tr>
                                                   <td><?php echo $c ?></td>
                                                   <td><?php echo $pending->date; ?></td>                                                  
                                                   <td><?php echo $pending->username;?></td>
                                                   <td><?php echo $bossname ?></td>
                                                   <td><?php echo $pending->balance_type;?></td>
                                                   <td><span class="hist-number"><?php echo $pending->amount;?></span></td>
                                                   <td><span class="hist-number"><?php echo $pending->account_number;?></span></td>                                                  
                                                  
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

                                                <td><?php echo $pending->category;?></td>
                                                <td><?php echo $pending->mcategory;?></td>
                                                <td><?php echo $pending->type;?></td>
                                                <td><?php echo $pending->sending_number;?></td>
                                                <td><?php echo $pending->transaction_id;?></td>

                                                  
                                                <td>                                                      
                                                       <a class="btn btn-success" role="button" data-bs-toggle="modal" data-bs-target="#paymentreqaccept" data-id="<?php echo $pending->id; ?>" data-amount="<?php echo $pending->amount; ?>" data-ucid="<?php echo $pending->user_id; ?>" data-username="<?php echo $pending->username; ?>" data-bltype="<?php echo $pending->balance_type; ?>">Accept</a>
                                                       <a class="btn btn-danger" role="button" data-bs-toggle="modal" data-bs-target="#paymentreqcancel" data-id="<?php echo $pending->id; ?>" data-amount="<?php echo $pending->amount; ?>" data-ucid="<?php echo $pending->user_id; ?>" data-username="<?php echo $pending->username; ?>" data-bltype="<?php echo $pending->balance_type; ?>">Cancel</a>                                                                                               
                                                </td>
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


                                        <div>
                                    <nav aria-label="Page navigation example">
                                        <?php echo $paging->pageLinks(); ?>
                                    </nav>
                                </div>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->


         <!-- Payment Request Accept modal start-->
         <div id="paymentreqaccept" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Balance Add Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=balaaddsuccess" method="POST">
            <div class="modal-body" id="balance_request_data">
                                                       
            </div>                

               
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-success">Complete</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Payment Request Accept modal End-->


<!-- Payment Request Cancel modal start-->
<div id="paymentreqcancel" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Cancel Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=baladdcancel" method="POST">
            <div class="modal-body" id="payment_request_cldata">
                                                       
            </div>                

                <div class="px-3">             
                                       
                    <div class="mb-3">
                        <label for="username" class="form-label">Reason</label>
                            <input class="form-control" type="text" name="remsg" id="remsg" required="" placeholder="Message">
                    </div>  
                    
                </div>
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-danger">Cancel</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Payment Request Cancel modal End-->


       
<?php 
include "footer-data.php";
?>