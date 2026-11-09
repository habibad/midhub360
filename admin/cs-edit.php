<?php 
include "login.check.php";
include "header.php";

//$reseller_id=$_GET["reseller_id"];

$reseller_id   = Xss::xssClean(Input::get('reseller_id'));


if(!isset($reseller_id) || empty($reseller_id)){
    session_destroy();
    header("location:../index.php");
    die();
}

$full_name       = fullname($reseller_id);      
$limit_balance   = limit_balance($reseller_id);
$mb_balance      = mbanking_balance($reseller_id);
$re_balance      = recharge_balance($reseller_id);
$username        = username($reseller_id);
$created_by      = created_by($reseller_id);
$userlevel       = cs_level($reseller_id); 
$fullname        = fullname($reseller_id); 
$address         = address($reseller_id);
$email_address   = email_address($reseller_id);
$phone_number    = phone_number($reseller_id);
$regi_date       = joining_date($reseller_id);   
$title_level     = level_title($reseller_id);

?>


            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>                                           
                                            <li class="breadcrumb-item active">Edit Customer</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Edit Customer</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        

                        <?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>							 
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">   
								
							  </button>
							</div>
							<?php
					  }?>
					<?php if (Session::exists('success')){
						?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
							  <strong>Well Done!</strong> <?=Session::Flush('success');?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">							 
								
							  </button>
							</div>
							<?php
					}?>


                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">

                                    <form action="route.php?action=csupdate" method="post" accept-charset="utf-8">

                                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">General</h5>

                                        <input type="hidden" name="userid" id="user_id" value="<?php echo $reseller_id;?>">

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Username<span class="text-danger">*</span></label>
                                            <input type="text" name="username" id="username" class="form-control" value="<?php echo $username;?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" id="password" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Pincode <span class="text-danger">*</span></label>
                                            <input type="password" name="pin" id="pin" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo $fullname;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number <span class="text-danger">*</span></label>
                                            <input type="number" name="mobile" id="mobile" class="form-control" value="<?php echo $phone_number;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $email_address;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Address <span class="text-danger">*</span></label>
                                            <input type="text" name="address" id="address" class="form-control" id="product-price" value="<?php echo $address;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100">
                                        </div>


                                        <div class="mb-3">
                                            <label for="product-price">Balance Limit <span class="text-danger">*</span></label>
                                            <input type="number" name="bllimit" id="bllimit" class="form-control" value="<?php echo $limit_balance;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6">
                                        </div>

                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">
                                
                               

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Service and Security</h5>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Recharge</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Drive</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Package</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Bill Pay</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">bKash</label>
                                        </div>
                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Rocket</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Nagad</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Upay</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">MNP</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Online Banking</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Global Recharge</label>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">OTP Type <span class="text-danger">*</span></label>
                                            <select name="otptype" class="form-control select2" id="otptype">                                                                                                 
                                                    <option value="1">Pin Code</option>
                                                    <option value="2">SMS</option>
                                                    <option value="3">Email</option>                                                                                               
             
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Device Permission<span class="text-danger">*</span></label>
                                            <select name="deviceno" class="form-control select2" id="deviceno">                                                                                                 
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option> 
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>                                                                                                
             
                                            </select>
                                        </div>                                     
                                       
                                        <div class="form-check mb-2 form-check-primary">                                           
                                            <input name="api_allow" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                            <label class="form-check-label" for="customckeck1">Allow API</label>                                          
                                        </div>

                                        <div class="form-check mb-3 form-check-primary">                                           
                                            <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                            <label class="form-check-label" for="customckeck1">Active</label>                                          
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Self Pin <span class="text-danger">*</span></label>
                                            <input type="password" name="selfpin" id="selfpin" class="form-control" placeholder="Enter Your Pincode" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                                        </div>

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update Customer</button>                                                    
                        </div> 

                        </form>

                        <br>

                        <!-- end row -->


                       
                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>