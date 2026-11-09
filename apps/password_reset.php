<?php
session_start();
//include "android.php";
include "security.php";
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";
$apps_name        = branddata("apps_name");
$web_link         = branddata("web_link");
$logo_name        = branddata("logo_url");  
$company_name     = CompanyData("name");  
$phone            = CompanyData("phone");  



?>



<?php 
include "header.php"; 
?>
        
        
        <div class="container h-100 text-white">
            <div class="row h-100">
                <div class="col-12 align-self-center mb-4">
                    <div class="row justify-content-center">
                        <div class="col-11 col-sm-7 col-md-6 col-lg-5 col-xl-4">         
            
            <div class="imgcontainer">
                <img src="../uploads/logo/<?php echo $logo_name;?>" alt="Avatar" class="avatar">
            </div>

            <!-- Error Code Start-->

            <?php if (Session::exists('error')){
						?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
						   <?=Session::Flush('error');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">							
						  </button>
						</div>
						<?php
				  }?>
				<?php if (Session::exists('success')){
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
						  <strong>Well Done!</strong> <?=Session::Flush('success');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">							
						  </button>
						</div>
						<?php
				}?>

                     <!-- Error Code End-->

            <div class='alert alert-success alert-dismissible fade show' role='alert'> 
                আপনার একাউন্টে দেওয়া ইমেইলে পাসওয়ার্ড রিসেট এর কোড পাঠানো হয়েছে। দয়া করে ইমেইল এর ইনবক্স চেক করুন। ইনবক্সে না পেলে স্পাম ফোল্ডার চেক করুন।
                Please check the registered email inbox or spam folder.</div>
            
                <form action="route.php?action=ForgotPassword" method="POST"> 

            <div class="container">
                <label for="username"><b>Reset Code</b></label>
                <input type="text" placeholder="Enter Reset Code" name="token" id="token" required>

                <label for="username"><b>New Password</b></label>
                <input type="password" placeholder="Enter New Password" name="npassword" id="npassword" required>

                <label for="username"><b>Confirm Password</b></label>
                <input type="password" placeholder="Enter Confirm Password" name="cpassword" id="cpassword" required>
                                     
                <button type="submit" name="submit" value="Login">Change Password</button>
                
            
            </div>
              
        </form>

                        </div>                        
                    </div>                    
                </div>               
            </div>            
        </div>        
    </main>

<?php 
    include "footer.php"; 
?>