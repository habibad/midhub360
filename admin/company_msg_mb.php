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
                                            <li class="breadcrumb-item active">Recharge History</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Mobile Banking Company Message</h4>
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
                                                                              
                                        <table id="basic-datatable" class="table table-striped dt-responsive w-100">
                                            <thead>
                                                <tr>
                                                    <th>SL.</th>
                                                    <th>Date</th>
                                                    <th>Company</th>
                                                    <th>Number</th>
                                                    <th>Amount</th>
                                                    <th>Message</th>                                                   
                                                    <th>Sim Balance</th>
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
                                            
                        $paging->setTotal(Report::totalMBSimMsg(Session::get('us_id')));
                        $res = DB::getInstance()->get(Report::mb_sim_msg(Session::get('us_id')) . $paging->getLimit())->result();
                                         
							/* */
								if(!empty($res))
								{
									foreach($res as $pending):
								
								    $transactionid = Request::get_transaction_id($pending->id);
								    $trans = preg_replace("/.You/"," ",$transactionid);
								
                                    $c++;

                                   
						   ?>
                               
                                                <tr>
                                                <td><?php echo $c ?></td>
                                                <td><?php echo $pending->datetime;?></td>
                                                    <td><?php echo $pending->company;?></td>
                                                    <td class="number-custome-style"><?php echo $pending->number;?></td>
                                                    <td class="number-custome-style"><?php echo $pending->amt;?></td>
                                                    <td><p style="width:200px;"> <?php echo $pending->msgbody;?></p> </td>                                                
                                                    <td><span class="number-custome-style"><?php echo $pending->balance;?></span></td>
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
                                                    <td><?php echo $pending->f_id;?></td>
                                                    
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

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer-data.php";
?>