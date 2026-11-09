<?php 
//include "android.php";
session_start();
include "security.php";
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";
$apps_name        = branddata("apps_name");
$web_link         = branddata("web_link");
$logo_name        = branddata("logo_url");  
$company_name     = CompanyData("name");  
$phone            = CompanyData("phone");  
include "header.php"; 
?>
 
        
        <!-- page content start -->

        <div class="main-container">

       
            
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

                <div class="card">
                    <div class="card-header">
                        <h6 class="subtitle mb-0">                            
                            New Account Registration
                        </h6>
                    </div>
                    <div class="card-body">

                    <form action="route.php?action=csregistration" method="post" accept-charset="utf-8">

                       
                        <div class="form-group float-label">
                            <input type="text" name="username" id="username" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Username<span class="field-mando">*</span></label>
                        </div>
                        <div class="form-group float-label">
                            <input type="password" name="password" id="password" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                            <label class="form-control-label">Password<span class="field-mando">*</span></label>                            
                        </div>                         
                        <div class="form-group float-label">
                            <input type="password" name="pin" id="pin" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                            <label class="form-control-label">Pin Code<span class="field-mando">*</span></label>                            
                        </div>                         

                        <div class="form-group float-label">
                            <input type="text" name="full_name" id="full_name" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                            <label class="form-control-label">Full Name<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="form-group float-label">
                            <input type="number" name="mobile" id="mobile" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                            <label class="form-control-label">Phone Number<span class="field-mando">*</span></label>                            
                        </div>
                        <div class="form-group float-label">
                            <input type="email" name="email" id="email" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>
                            <label class="form-control-label">Email Address<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="form-group float-label">
                            <input type="text" name="address" id="address" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>
                            <label class="form-control-label">Address<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="input-group-text">
                            <input type="checkbox" name="active" value="1" checked> <span style="padding-left: 5px;">I accept Terms and Conditions<span>
                            
                        </div>   
                         

                    </div>
                    
                    <div class="card-footer">                    
                        <button type="submit" class="btn btn-block btn-default rounded">Sign Up</button>
                    </div>
                    <div style="padding-left: 10px;"><span style="padding-left: 5px;">Already have account? <a href="index.php">Sign In</a></span></div><br>
                </div>
            </div>
        </div>
    </main>


<?php 
    include "footer.php"; 
?>