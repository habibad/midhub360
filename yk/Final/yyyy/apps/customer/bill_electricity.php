<?php
    include "login.check.php";
    include "header.php"; 
?>
        
        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="billpay_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">manage_search</span> </div>
                    <p class="small text-white">Bill History</p>
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
        
        
        <!-- page content start -->

<div class="main-container">

            <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>
            </div>
            
    <div class="container">
                <!--
                <div class="alert alert-success">
                    <b>Congratulations!</b> Your address has been saved to your adresss list successfully.
                </div>
                -->

        <div class="card">
                    <div class="card-header">
                        <h6 class="subtitle mb-0">
                            <div class="avatar avatar-40 bg-primary-light text-primary rounded mr-2"><span class="material-icons vm">wb_incandescent</span></div>
                            Electricity Bill Pay
                        </h6>
                    </div>
            <div class="card-body">

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

                    <input type="hidden" name="type" id="type" value="Electricity">

                        <div class="form-group float-label active">
                           <select name="company" id="company" class="form-control">
                               <option value="BPDB" selected>BPDB</option>
                               <option value="Palli Bidyut">Palli Bidyut</option>
                               <option value="Desco">Desco</option>
                               <option value="Nesco">Nesco</option>
                               <option value="West Zone">West Zone</option>  
                            </select>
                            <label class="form-control-label">Select Company<span class="field-mando">*</span></label>
                        </div>
                        <div class="form-group float-label active">
                           <select name="company_type" id="company_type" class="form-control">                               
                               <option value="Postpaid" selected>Postpaid</option>
                               <option value="Prepaid">Prepaid</option>                               
                            </select>
                            <label class="form-control-label">Select Type<span class="field-mando">*</span></label>
                        </div>
                        <div class="form-group float-label">
                            <input type="number" name="number" id="number" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                            <label class="form-control-label">Meter No/Customer/Consumer No<span class="field-mando">*</span></label>
                        </div>

                        <div class="form-group float-label">
                            <input type="text" name="cs_name" id="cs_name" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                            <label class="form-control-label">Customer Name<span class="field-mando">*</span></label>
                        </div> 

                        <div class="form-group float-label">
                            <input type="number" name="contact_number" id="contact_number" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                            <label class="form-control-label">Mobile Number<span class="field-mando">*</span></label>
                        </div> 

                        <div class="form-group float-label">
                            <input type="number" name="amount" id="amount" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "7" required>
                            <label class="form-control-label">Amount<span class="field-mando">*</span></label>
                        </div> 

                        <div class="form-group float-label">
                        <p class="text-secondary">Billing Month<span class="field-mando">*</span></p>
                            <input type="month" name="month" id="month" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>
                           
                        </div> 

                        
                    </div>                    
                    

                    <div class="container text-center">
                        <button type="button" class="btn btn-primary btn-block mt-4" data-remote="false" data-toggle="modal" data-target="#confirmation-modal-billpay">Pay Bill</button>
                    </div>
                </form>    
                        <br>

            </div>
    </div>
</div>
    </main>


    <!-- Send Billpay Modal -->
    <div class="modal fade" id="confirmation-modal-billpay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-center navbar">
                    <h5 class="modal-title" id="confirmation-modal-billpay">Electricity Bill Pay</h5>
                </div>
				<form action="route.php?action=requestBillPaySend" role="form" class="inform well" style="width:100%;" method="post" accept-charset="utf-8">
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