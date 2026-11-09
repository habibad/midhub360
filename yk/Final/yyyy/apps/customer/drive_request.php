<?php
include "login.check.php"; 
include "header.php";
?>
    
    <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
					<a href="drive_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">history</span> </div>
                    <p class="small text-white">Drive History</p>
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
    
    
    <section>
        <div class="container">
			<div class="mb-4 mt-2">
				<h5><b>Send Drive</b></h5>
			 </div>
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
                

                <input type="hidden" id="pkg_id" value="<?php echo isset($_POST['pkg_id']) ? $_POST['pkg_id'] : '' ?>" />

				<div class="form-group row">
                                <label class="col-sm-3 col-form-label">Operator</label>
                                <div class="col-sm-9">
                                    <input type="text" value="<?php echo isset($_POST['op_name']) ? $_POST['op_name'] : '' ?>" readonly class="form-control" />
                                </div>
                            </div>
				
				<div class="form-group row">
                                <label class="col-sm-3 col-form-label">Package</label>
                                <div class="col-sm-9">
                                    <input type="text" value="<?php echo isset($_POST['pkg_amount']) ? $_POST['pkg_amount'] : '' ?>  <?php echo isset($_POST['pkg_name']) ? $_POST['pkg_name'] : ''  ?>" readonly class="form-control" />
                                </div>
                            </div>
				
				<div class="form-group row">
                                <label class="col-sm-3 col-form-label">Amount</label>
                                <div class="col-sm-9">
                                    <input type="number" name="amount" id="amount" class="form-control" value="<?php echo isset($_POST['pkg_amount']) ? $_POST['pkg_amount'] : '' ?>" readonly="">
								</div>
                            </div>
                <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Cost</label>
                                <div class="col-sm-9">
                                    <input type="number" name="cost" id="cost" value="<?php echo isset($_POST['pkg_balance']) ? $_POST['pkg_balance'] : ''  ?>" readonly class="form-control" />
								</div>
                            </div>
				
				
				<div class="form-group row"> 
				<label class="col-sm-3 col-form-label">Mobile Number</label>				
                <div class="form-group mb-1" >                   
		<input type="number" name="number" id="number" class="form-control large-gift-card for-space input-number-size" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" placeholder="Enter Mobile Number" required>				
						<p style="margin-left: 20px;padding: 0px;" class="help-block form_note">[Ex. 01712345678]</p>
                       </div>
                    </div>
				
                
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
                
                <button type="button" class="btn btn-primary btn-block mt-4" data-remote="false" data-toggle="modal" data-target="#confirmation-modal-drive">Send Now</button>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmation-modal-drive" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-center navbar">
                    <h5 class="modal-title" id="confirmation-modal-drive">Drive Confirmation</h5>
                </div>
				<form action="route.php?action=requestDrive" role="form" class="inform well" style="width:100%;" method="post" accept-charset="utf-8">
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
</div>
	
<?php include "footer.php"; ?>	
