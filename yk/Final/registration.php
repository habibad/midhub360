<?php 
session_start();
include "header.php";
require_once 'security.php';
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";

?>

    
        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-4">
                        <div class="card bg-pattern">

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">

                                <!-- 
                                    <div class="auth-logo">
                                        <a href="index.html" class="logo logo-dark text-center">
                                            <span class="logo-lg">
                                                <img src="../assets/images/logo-dark.png" alt="" height="22">
                                            </span>
                                        </a>
                    
                                        <a href="index.html" class="logo logo-light text-center">
                                            <span class="logo-lg">
                                                <img src="../assets/images/logo-light.png" alt="" height="22">
                                            </span>
                                        </a>
                                    </div>
                                -->

                                    <h3 class="text-muted mb-4 mt-3">New Account Registration</h3>
                                </div>


                                 <!-- Error Code Start-->

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

                     <!-- Error Code End-->


                                <form action="route.php?action=csregistration" method="post" accept-charset="utf-8">

                                    <div class="mb-3">
                                            <label for="product-name" class="form-label">Username<span class="text-danger">*</span></label>
                                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Pincode <span class="text-danger">*</span></label>
                                            <input type="password" name="pin" id="pin" class="form-control" id="product-price" placeholder="Enter Pincode" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" id="full_name" class="form-control" id="product-price" placeholder="Enter Fullname" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number <span class="text-danger">*</span></label>
                                            <input type="number" name="mobile" id="mobile" class="form-control" id="product-price" placeholder="Enter Phone Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" id="product-price" placeholder="Enter Email Address" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Address <span class="text-danger">*</span></label>
                                            <input type="text" name="address" id="address" class="form-control" id="product-price" placeholder="Enter Customer Address" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>
                                        </div>

                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkbox-signup">
                                            <label class="form-check-label" for="checkbox-signup">I accept <a href="javascript: void(0);" class="text-dark">Terms and Conditions</a></label>
                                        </div>
                                    </div>
                                    <div class="text-center d-grid">
                                        <button class="btn btn-success" type="submit"> Sign Up </button>
                                    </div>

                                </form>

                                <div class="text-center">
                                    <h5 class="mt-3 text-muted">Already have account? <a href="index.php"><b>Sign In</b></a></h5>
                                    
                                </div>

                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

       
<?php 
include "footer.php";
?>
