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

					<span class="title-text">Balance add request History</span>
            </div>

		<div class="container">	
	
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
						
						$pagin->setTotal(Report::add_totalPaymentHistory(Session::get('us_id')));
						
						$res = DB::getInstance()->get(Report::add_payment_history(Session::get('us_id')) . $pagin->getLimit())->result();
						//$res = DB::getInstance()->get(Report::payment_history(Session::get('us_id')))->result();
						
						if(!empty($res))
						{
							foreach($res as $pending):
						
    						//$transactionid = Request::get_transaction_id($pending->id);
    						//$trans = preg_replace("/.You/"," ",$transactionid);
    						
							$amount = $pending->amount;
							$status = $pending->status;
							$category = $pending->category;

							        						
        				   ?>
                            <tr>
                                
                                <td>


								<?php
										if($category == "Mobile Banking"){
										?>
											<span><strong>Amount: </strong></span><span class="transaction-amount"><b>৳ <?php echo $pending->amount; ?></b></span><br>
                                    		<span class="tr-cost">Company: <?php echo $pending->mcategory; ?> (<?php echo $pending->type; ?>)</span> <br>
											<span class="tr-cost">Pay. Number : <?php echo $pending->account_number; ?></span> <br>	
											<span class="tr-cost">Send. Number :<?php echo $pending->sending_number; ?></span><br>
											<span class="tr-cost">TXN ID: <?php echo $pending->transaction_id; ?></span><br>								
                                    											
										<?php
										}
										elseif($category == "Bank")
										{
										?>
											<span><strong>Amount: </strong></span><span class="transaction-amount"><b>৳ <?php echo $pending->amount; ?></b></span><br>
                                    		<span class="tr-cost">Bank Name : <?php echo $pending->category; ?></span> <br>
											<span class="tr-cost">Account Name : <?php echo $pending->account_name; ?></span> <br>
											<span class="tr-cost">Account Number : <?php echo $pending->account_number; ?></span> <br>											
											<span class="tr-cost">D. No/TXN No:<?php echo $pending->transaction_id; ?></span><br>

											<?php
										}
										elseif($category == "Online")
										{
										?>
											<span><strong>Amount: </strong></span><span class="transaction-amount"><b>৳ <?php echo $pending->amount; ?></b></span><br>
                                    		<span class="tr-cost">Pay. Method : <?php echo $pending->category; ?></span> <br>
											<span class="tr-cost">Pay. Number : <?php echo $pending->account_number; ?></span> <br>	
											<span class="tr-cost">Send. Number :<?php echo $pending->sending_number; ?></span><br>
											<span class="tr-cost">TXN ID :<?php echo $pending->transaction_id; ?></span><br>
                                        
										
										<?php
										}
										else
										{
											echo "Unknown";

										}
									?>	
                                </td>
                                <td>
                                    <div class="tr-time-status">

									<?php
										if($status == "Processing"){
										?>
											<span class="tr-number"><b>Date :</b> <?=date("M j, Y g:i a", strtotime($pending->date));?></span><br>
											<span class="tr-cost">BL Type: <?php echo $pending->balance_type; ?></span><br>
											<span class="badge badge-warning p-2"><?php echo "$status"; ?></span><br>
											<span class="tr-cost">ID No: <?php echo $pending->id; ?></span>											
										<?php
										}
										elseif($status == "Accepted")
										{
										?>
											<span class="tr-number"><b>Date :</b> <?=date("M j, Y g:i a", strtotime($pending->date));?></span><br>
											<span class="tr-cost">BL Type: <?php echo $pending->balance_type; ?></span><br>
											<span class="badge badge-success p-2"><?php echo "$status"; ?></span><br>
											<span class="tr-cost">ID No: <?php echo $pending->id; ?></span>
											<?php
										}
										elseif($status == "Canceled")
										{
										?>
											<span class="tr-number"><b>Date :</b> <?=date("M j, Y g:i a", strtotime($pending->date));?></span><br>
											<span class="tr-cost">BL Type: <?php echo $pending->balance_type; ?></span><br>
											<span class="badge badge-danger p-2"><?php echo "$status"; ?></span><br>
											<span class="tr-cost">Comment:<?php echo $pending->comment; ?></span><br>
											<span class="tr-cost">ID No: <?php echo $pending->id; ?></span>
                                        
										
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
</div>
</main>	
   <?php include "footer.php";?>