<?php
include "login.check.php";
include "header.php"; 

/* Fetch bank list */
$query  = "SELECT * FROM banklist WHERE status='1' ORDER BY id ASC";                
$result = mysqli_query($connect, $query); 
/* Fetch bank list End */
?>

        
<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="bank_history.php">
                        <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">search</span> </div>
                        <p class="small text-white">History</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="payment_received.php">
                        <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">payments</span> </div>
                        <p class="small text-white">Payment Hist.</p>
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

					<span class="title-text">Bank Payment Request</span>
        </div>    <br>
        
            
            <div class="container">


            <?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>
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

            <form action="" role="form"  method="post" accept-charset="utf-8">

            <div class="container mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            
                            <div class="col">
                                <h6 class="subtitle mb-0">Account Information</h6>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">            
                        <div class="form-group">
                            <label class="form-control-label">Bank Name</label>
                            <select name="bankname" class="form-control" id="bankname">
                                <option value="">Choose Bank</option>
                                        <?php

                                                    while($data = mysqli_fetch_array($result))
                                                    {
                                                        echo "<option value='". $data['BankName'] ."'>" .$data['BankName'] ."</option>";
                                                    }

                                        ?>                                                     
                            </select>
                        </div>

                        <div class="form-group float-label">
                            <input type="text" name="branch" id="branch" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Branch Name</label>
                        </div> <br>

                        <div class="form-group">
                            <label class="form-control-label">Account Type</label>
                            <select name="ac_type" class="form-control" id="ac_type">
                                <option value="Saving" selected>Saving</option>
                                <option value="Current">Current</option>                        
                            </select>
                        </div>
                       
                        <div class="form-group float-label">
                            <input type="text" name="acname" id="acname" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Account Name</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="number" name="acnumber" id="acnumber" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Account Number</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="number" name="amount" id="amount" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Amount</label>
                            <p>Current M. Bank Balance: Tk. <strong><?php echo $mb_balance;?></strong> </p>
                        </div>
                        <div class="form-group float-label">
                            <input type="number" name="contact_number" id="contact_number" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Contact Number (Optional)</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="text" name="note" id="note" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                            <label class="form-control-label">Note (Optional)</label>
                        </div>
                       
                    </div>

                  
                    <div class="card-footer">                    
                        <button type="button" class="btn btn-block btn-default rounded" data-remote="false" data-toggle="modal" data-target="#confirmation-modal-bank">Send Now</button>
                    </div>
                    </form>
                </div>
            </div>
            
        </div>
    </main>

   

<!-- Recharge Request modal start-->
<div id="confirmation-modal-bank" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Bank Payment Request Confirmation</h4>
                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>   
                    
        <form action="route.php?action=BankSend" role="form" method="post" accept-charset="utf-8">
              
            <div class="modal-body" id="bank_request_data">
                                                       
            </div>    
            
            
                <div class="modal-footer">
					<button type="submit" class="btn btn-success">Next</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				</div>
        </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Request modal End-->


<?php 
include "footer.php"; 
?>