<?php 
session_start();
include "header.php";
require_once 'security.php';
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";

$phone  = CompanyData("phone");

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

                            
                            <div class="alert alert-success">
                                    <h4>আপনার একাউন্টটি সফল ভাবে রেজিস্ট্রেশন হয়েছে। একাউন্টটি বর্তমানে ইনএকটিভ ভাবে রয়েছে। 
                                                একাউন্টটি একটিভ করতে নিচের নাম্বারে কল করুন। </h4> 

                                     <h4>Your account is successfully registered. Your account currently is inactive. 
                                      Contact us for active this account. Thanks. <br><br>
                                      
                                      Contact Number: <?php echo $phone; ?>
                                     </h4>           

							</div>
                               

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
