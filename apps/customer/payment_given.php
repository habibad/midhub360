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
					<a href="add_fund.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">add</span> </div>
                    <p class="small text-white">Add Balance</p>
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

					<span class="title-text">Given Payment History</span>
            </div>
	
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-recharge-history" role="tabpanel" aria-labelledby="nav-recharge-history-tab">
                <div class="">
                    <table class="table mb-0">
					
                        <tbody>
						<?php 
						// $rows = isset($_POST['viewall']) ? $_POST['viewall'] : 30;
						$rows = 5;
						$i = 1; 
						$pagin = new Pagination($rows,'page');
						$start = $pagin->getStart();
						$return_payment = 0;
						$payment_added = 0;
						
						$pagin->setTotal(Report::totalPaymentHistory(Session::get('us_id')));
						
						$res = DB::getInstance()->get(Report::payment_history(Session::get('us_id')) . $pagin->getLimit())->result();
						//$res = DB::getInstance()->get(Report::payment_history(Session::get('us_id')))->result();
						
						if(!empty($res))
						{
							foreach($res as $pending):
						
    						$transactionid = Request::get_transaction_id($pending->id);
    						$trans = preg_replace("/.You/"," ",$transactionid);
    						
    						if($pending->return_payment == 1)
							{
								$return_payment = $pending->amount;
							}
							else{
								$payment_added = $pending->amount;
							}
							
							$total = $payment_added - $return_payment;
							$balance = ($total <= 0) ? 0 : $total;

							$trans_amount = $pending->amount;
							$trans_status = $pending->return_payment;
							$new_bl = $pending->new_balance;
							$old_bl = $pending->old_balance;
        						
        				   ?>
                            <tr>
                                <td>
								<?php
										if($trans_status == 0){
										?>
											<div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">arrow_forward</span></div>
										<?php
										}
										elseif($trans_status == 1)
										{
										?>
											<div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">arrow_back</span></div>
										<?php
										}
										else{

											echo "Unknown";

										}
									?>
                                </td>
                                <td>
                                    <span class="tr-number">By: You </span></br>
									<span class="tr-number"><b>Date :</b> <?=date("M j, Y g:i a", strtotime($pending->updated));?></span><br>
									<span class="transaction-id"><b>User :</b> <?php echo username($pending->user_id) ?></span><br>
                                    <span class="tr-number"><b>Note :</b> <?php echo $pending->note; ?></span>
                                     
                                </td>
                                <td>
                                    <div class="tr-time-status">

									<?php
										if($trans_status == 0){
										?>
											<span><b>Added :</b></span><span class="credit"><b> ৳ <?php echo "$trans_amount";?></b></span><br>
											<span class="tr-number">New Balance : ৳</b><?php echo "$new_bl"; ?></span><br>
											<span class="tr-number">Old Balance : ৳</b><?php echo "$old_bl"; ?></span>
										<?php
										}
										elseif($trans_status == 1)
										{
										?>
											<span><b>Return :</b></span> <span class="debit"><b> ৳ <?php echo "$trans_amount";?></b></span><br>
											<span class="tr-number">New Balance : ৳</b><?php echo "$new_bl"; ?></span><br>
											<span class="tr-number">Old Balance : ৳</b><?php echo "$old_bl"; ?></span>
                                        
										
										<?php
										}
										else
										{
											echo "Unknown";

										}
									?>	

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
                            <?php echo $pagin->pageLinks(); ?>
                        </nav>
                    </div>
                </div>
            </div>
            

        </div>
</div>
</main>	
   <?php include "footer.php";?>