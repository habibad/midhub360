<?php
include "login.check.php"; 
include "class/Request.php";
include "class/DB.php";
include "class/Config.php";
include "class/Pagination.php";
include "class/Report.php";
include "header.php";
?>
    
	<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
					<a href="recharge_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">history</span> </div>
                    <p class="small text-white">Recharge Hist.</p>
					</a>
                </div>
                <div class="col-auto">
					<a href="send_recharge.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">phone_iphone</span> </div>
                    <p class="small text-white">Send Recharge</p>
					</a>
                </div>
                <div class="col-auto">
					<a href="help.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">support_agent</span> </div>
                    <p class="small text-white">Contact</p>
					</a>
                </div>                
            </div>
        </div>
	
<div class="main-container">

		<div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>

					<span class="title-text">Drive History</span>
        </div>
	
	
	
		
		<?php if (Session::exists('error')){
						?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
						  <strong>On Snap!</strong> <?=Session::Flush('error');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						</div>
						<?php
				  }?>
				<?php if (Session::exists('success')){
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
						  <strong>Well Done!</strong> <?=Session::Flush('success');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						</div>
						<?php
				}?>
                
		
		
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-recharge-history" role="tabpanel" aria-labelledby="nav-recharge-history-tab">
                <div class="">
                    <table class="table mb-0">
					
                        <tbody>
						<?php 
						ini_set('memory_limit', '-1');
						$amountTotal ='';
                        $costTotal ='';
                                            // $rows = isset($_POST['viewall']) ? $_POST['viewall'] : 30;
                                            $rows = 5;
                                            $i = 1; 
                                            $paging = new Pagination($rows,'page');
                                            $start = $paging->getStart();
                                            
                                            $paging->setTotal(Report::totalDriveHistoryById(Session::get('us_id')));
                                            $res = DB::getInstance()->get(Report::history_drive_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                         
							/* */
								if(!empty($res))
								{
									foreach($res as $pending):
								
								$transactionid = Request::get_drive_transaction_id($pending->id);
								$trans = preg_replace("/.You/"," ",$transactionid);
								
								
						   ?>
                            <tr>
                                <td>
								<div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">mobile_friendly</span></div>
                                </td>
                                <td>
                                    <span class="transaction-id"><?php echo $pending->phone; ?></span><br>
									<span><strong>Amount: </strong></span><span class="transaction-amount"><b>৳ <?php echo $pending->balance ?></b></span><br>
									<span><strong>Cost: </strong></span><span class="transaction-amount"><b>৳ <?php echo $pending->cost ?></b></span><br>									
                                    <span class="tr-cost">Type : <?php echo $pending->load_type; ?></span> <br>									
                                    <span class="tr-number"><b>Trxn :</b> <?php echo empty($trans) ? "N/A" : $trans ?></span>
									
								</td>
                                <td>
                                    <div class="tr-time-status">
                                        <span class="date"><b><?=date("M j, Y", strtotime($pending->submitted_date));?></b></span><br>
                                        <span class="time"><?=date("g:i a", strtotime($pending->submitted_date));?></span><br>
                                        <span class="status">
										<?php
											  if($pending->status == '0'):
													echo '<span class="badge badge-warning p-2">Pending</span>';
											  elseif($pending->status == '1'):
													echo '<span class="badge badge-success p-2">Success</span>';
											  elseif($pending->status == '2'):
													echo '<span class="badge badge-danger p-2">Cancelled</span>';
											  elseif ($pending->status == '4'):
													echo '<span class="badge badge-info p-2">Waiting</span>';
													
												elseif($pending->status == '3'):
													echo '<span class="badge badge-danger p-2">Failed</span>';	
											  endif;
										?> 
										</span>
                                    </div>
                                </td>
                            </tr>
							<?php endforeach; 
								}
								else{
									?><tr>
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
                </div>
            </div>
            

        </div>
	

</div>
	</main>							


   <?php include "footer.php";?>