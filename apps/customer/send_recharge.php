<?php
    include "login.check.php";
    include "header.php";     
?>
        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="recharge_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">account_circle</span> </div>
                    <p class="small text-white">History</p>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">qr_code_scanner</span> </div>
                    <p class="small text-white">Drive</p>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">account_balance</span> </div>
                    <p class="small text-white">Package</p>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">account_balance_wallet</span> </div>
                    <p class="small text-white">Add Fund</p>
                </div>
            </div>
        </div>
        
        <div class="main-container">            
            <div class="container mb-4">

            <div class="title-box title-msg">
                    Send Recharge
                </div>
                <br>

                <form action="" role="form"  method="post" accept-charset="utf-8">
                    
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
               

                <p class="text-left text-secondary mb-1 title-text">Enter Mobile Number</p>
                <div class="form-group mb-1">
                    <input type="number" name="number" id="number" class="form-control large-gift-card" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" placeholder="01XXXXXXXXX" required>
                </div>

                <p class="text-left text-secondary mb-1 title-text">Enter Amount</p>
                <div class="form-group mb-1">
                    <input type="number" name="amount" id="amount" class="form-control large-gift-card" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" placeholder="Amount" required>                    
                </div>
                <p class="text-center text-secondary">Available: ৳ <?php echo $re_balance;?></p>                
                    

                <div class="form-row">
					<div class="col">
                        <input class="form-check-input" type="radio" name="type" id="type" value="1" checked>
                        <label class="form-check-label">
                                Prepaid
                            </label>
                    </div>
                    <div class="col">
                        <input class="form-check-input" type="radio" name="type" id="type2" value="2">
                        <label class="form-check-label">
                                Postpaid
                            </label>
                    </div>
					
					<div class="col">
                        <input class="form-check-input" type="radio" name="type" id="type3" value="3">
                        <label class="form-check-label">
                                Skitto/Others
                            </label>
                    </div>
					
                </div>                

            </div>

         
            <div class="container text-center">
            <button type="button" class="btn btn-primary btn-block mt-4" data-remote="false" data-toggle="modal" data-target="#confirmation-modal">Send Now</button>               
            </div>

            </form>
        </div>
    </main>

     <!-- Recharge Confirmation Modal -->
     <div class="modal fade" id="confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-center navbar">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                </div>
				<form action="route.php?action=requestSend" role="form" class="inform well" style="width:100%;" method="post" accept-charset="utf-8">
                <div class="modal-body" id="load_request_data">
					
								
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Next</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				</div>
				</form>
            </div>
        </div>
    </div>

    <?php 
    include "footer.php"; 
?>