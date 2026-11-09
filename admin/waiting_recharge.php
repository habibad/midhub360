<?php 
include "login.check.php";
include "header.php";

$total_amount = Mixedfn::totalwaitingre();
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
                                            <li class="breadcrumb-item active">Recharge Waiting History</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Recharge Waiting History</h4>
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
                                                    <th>Number</th>
                                                    <th>Operator</th>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>ID</th> 
                                                    <th>Resend</th> 

                                                                                                        
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
                                            
                        $paging->setTotal(Report::totalWaitingFHistoryById(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::waiting_flexi_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                         
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
                                                   <td><?php echo $pending->submitted_date; ?></td>                                                  
                                                   <td><?php echo $username ?></td>
                                                   <td><?php echo $bossname ?></td>
                                                   <td><span class="hist-number"><?php echo $pending->phone;?></span></td>
                                                   <td><?php echo $pending->operator; ?></td>
                                                   <td><?php echo $pending->load_type; ?></td>
                                                   <td><span class="hist-number"><?php echo $pending->balance; ?></span></td>
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
                                                  
                                                <td>                                                      
                                                       <a class="btn btn-success" role="button" data-bs-toggle="modal" data-bs-target="#suceessrecharge" data-number="<?php echo $pending->phone; ?>" data-id="<?php echo $pending->id; ?>" data-amount="<?php echo $pending->balance; ?>" data-ucid="<?php echo $pending->user_id; ?>" data-company="<?php echo $pending->operator; ?>">Success</a>
                                                       <a class="btn btn-danger" role="button" data-bs-toggle="modal" data-bs-target="#cancelrecharge" data-number="<?php echo $pending->phone; ?>" data-id="<?php echo $pending->id; ?>" data-amount="<?php echo $pending->balance; ?>" data-ucid="<?php echo $pending->user_id; ?>" data-company="<?php echo $pending->operator; ?>">Cancel</a>
                                                                                                         
                                                </td>
                                                   <td><?php echo $pending->id; ?></td>
                                                  <td> 
                                                        <a class="btn btn-primary" role="button" data-bs-toggle="modal" data-bs-target="#resendrecharge" data-number="<?php echo $pending->phone; ?>" data-id="<?php echo $pending->id; ?>" data-amount="<?php echo $pending->balance; ?>" data-ucid="<?php echo $pending->user_id; ?>" data-company="<?php echo $pending->operator; ?>">Resend</a>
                                                  </td>
                                                   
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


         <!-- Recharge Success modal start-->
         <div id="suceessrecharge" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Success Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=resuccess" method="POST">
            <div class="modal-body" id="load_request_data">
                                                       
            </div>  
            
           
                <div class="px-3">   
                    
                    <input type="hidden" name="sttype" id="sttype" value="waiting">                                  
      
                    <div class="mb-3">
                        <label for="username" class="form-label">Transaction ID</label>
                            <input class="form-control" type="text" name="txnid" id="txnid" required="" placeholder="Transaction ID" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25">
                    </div>  
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Message</label>
                            <input class="form-control" type="text" name="msg" id="msg" required="" placeholder="Message">
                    </div>  
                                                          
                </div>
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-success">Complete</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Success modal End-->


<!-- Recharge Cancel modal start-->
<div id="cancelrecharge" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Cancel Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=recancel" method="POST">
            <div class="modal-body" id="load_request_cldata">
                                                       
            </div>                

                <div class="px-3">  
                    
                    <input type="hidden" name="sttype" id="sttype" value="waiting">                                
                                             
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

<!-- Recharge Cancel modal End-->


<!-- Recharge Resend modal start-->
<div id="resendrecharge" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Resend Confirmation Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=resendrecharge" method="POST">
            <div class="modal-body" id="load_request_redatadata">
                                                       
            </div>                

                <div class="px-3">             
                                       
                   
                    
                </div>
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-primary">Resend</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Cancel modal End-->


       
<?php 
include "footer-data.php";
?>