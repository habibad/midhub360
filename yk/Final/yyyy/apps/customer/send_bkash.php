<?php 
    include "login.check.php";    
    include "header.php";    
?>
        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="mbank_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">manage_search</span> </div>
                    <p class="small text-white">History</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="add_fund.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">account_balance_wallet</span> </div>
                    <p class="small text-white">Add Fund</p>
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
            <div class="container mb-4">

            <div class="title-box title-msg">
                    Send bKash
                </div>
                <br>

        <form action="" role="form"  method="post" accept-charset="utf-8">

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

                <input type="hidden" name="bank_name" id="bank_name" value="bKash">

                <p class="text-left text-secondary mb-1 title-text">Enter Account Number</p>
                <div class="form-group mb-1">
                    <input type="number" name="m_number" id="m_number" class="form-control large-gift-card" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" placeholder="01XXXXXXXXX">
                </div>

                <p class="text-left text-secondary mb-1 title-text">Enter Amount</p>
                <div class="form-group mb-1">
               <!--  <input type="number" name="amount" id="amount" class="form-control large-gift-card" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" placeholder="Amount">    -->                
                
                <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" oninput="validateAmount(this)" maxlength="5" required>

<script>
    function validateAmount(input) {
        var maxAmount = 25000;
        if (input.value > maxAmount) {
            alert("Amount cannot be more than " + maxAmount);
            input.value = maxAmount; // Set the input value to the maximum amount
        }
    }
</script>

                </div>
                <p class="text-center text-secondary">Available M. Banking Balance: ৳ <?php echo $mb_balance;?> </p>              
                
                <p class="text-left text-secondary mb-1 title-text">Select Type</p>
                <div class="form-group">                               
                    <select class="form-control" name="m_type" id="m_type">
                        <option value="CashIn">Cash In</option>
                        <option value="CashOut">Cash Out</option>
						<option value="SendMoney">Send Money</option>
						<option value="Payment">Payment</option>                      
                    </select>
                </div>
            </div>

            <div class="container text-center">
            <button type="button" class="btn btn-primary btn-block mt-4" data-remote="false" data-toggle="modal" data-target="#confirmation-modal-mbank">Send Now</button>
            </div>
       </form>
        </div>
    </main>


    <!-- Send bKash Modal -->
    <div class="modal fade" id="confirmation-modal-mbank" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-center navbar">
                    <h5 class="modal-title" id="exampleModalCenterTitle">bKash Send Confirmation</h5>
                </div>
				<form action="route.php?action=requestMBSend" role="form" class="inform well" style="width:100%;" method="post" accept-charset="utf-8">
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