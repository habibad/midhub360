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
                        <span class="material-icons">keyboard_arrow_left</span>
                    </button>
					<span class="title-text">Mobile Banking History</span>
        </div>

    
        
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
                                            
                                            $paging->setTotal(Report::totalMbankiHistoryById(Session::get('us_id')));
                                            $res = DB::getInstance()->get(Report::history_m_userid(Session::get('us_id')) . $paging->getLimit())->result();
                                         
						
								if(!empty($res))
								{
									foreach($res as $pending):
								
								$transactionid = Request::get_m_transaction_id($pending->id);
								$trans = preg_replace("/.You/"," ",$transactionid);
								
								$sending_number = Request::get_m_sending_number($pending->id);
								$s_number = preg_replace("/.You/"," ",$sending_number);
								
						   ?>
                            <tr>
                                								
								<td>
									<?php
									if($pending->company_name == 'bKash')
									{
										?>
										<img src="../img/icon/bKash.png" alt="bKash Image">
										<?php
									}
									
									elseif($pending->company_name == 'Nagad')
									{
										?>
										<img src="../img/icon/nagad.png" alt="Nagad Image">
										<?php
									}
									elseif($pending->company_name == 'Rocket')
									{
										?>
										<img src="../img/icon/Rocket.png" alt="Rocket Image">
										<?php
									}
									elseif($pending->company_name == 'Upay')
									{
										?>
										<img src="../img/icon/upay.png" alt="Upay Image">
										<?php
									}
									else{
										?>
										<img src="../img/icon/default.png" class="history-img" width="50" height="50">
										<?php
									}
									?>
                                </td>																		
								
                                <td>
                                    <span class="transaction-id"><?php echo $pending->ac_number; ?></span><br>
									<span><strong>Amount: </strong></span><span class="transaction-amount">৳ <?php echo $pending->amount ?></span><br>
									<span class="tr-cost">Company : <?php echo $pending->company_name; ?></span> <br>
                                    <span class="tr-cost">Type : <?php echo $pending->type; ?></span>
                                    <span class="tr-cost">Type : <?php echo $transactionid; ?></span>
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
													echo '<span class="badge badge-success p-2">Success</span><br>';
													echo '<span class="tr-number"><b>Trxn : </b>' . $transactionid . '</span><br>';
													echo '<span class="tr-number"><b>Sending Number : </b>' . $sending_number . '</span><br>';
											  elseif($pending->status == '2'):
													echo '<span class="badge badge-danger p-2">Cancelled</span>';
											  elseif($pending->status == '3'):
													echo '<span class="badge badge-danger p-2">Cancelled</span>';	
											  elseif ($pending->status == '4'):
													echo '<span class="badge badge-info p-2">Waiting</span>';
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
            <div class="tab-pane fade" id="nav-report" role="tabpanel" aria-labelledby="nav-report-tab">
					<?php include "report_tab.php";?>
				</div>

        </div>

	</div>
</main>								

    </section>
   <?php include "footer.php";?>