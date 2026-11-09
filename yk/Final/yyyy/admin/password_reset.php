<?php
session_start();
require_once '../security.php';
include"../db.php";
require_once '../autoload.php';
include "../mixedfunction.php";

$apps_name        = branddata("apps_name");
$logo_name        = branddata("logo_url");  
$company_name     = CompanyData("name"); 

$error_msg = "";

/*
if(isset($_GET['token']))
{
$token= $_GET['token'];
}

*/

$token = Xss::xssClean(Input::get('token'));

/*
if(!isset($token) || empty($token)){
    session_destroy();
    header("location:../index.php");
    die();
}
*/
//echo "Token" . $token;

?>



<?php 
include "login_header.php";
 
?>
      
     
        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-4">
                        <div class="card bg-pattern">

                            <div class="card-body p-4">

                            <div class="text-center w-75 m-auto">
                                    <div class="auth-logo">                                        
                                            <span class="logo-lg">
                                                <img src="uploads/logo/<?php echo $logo_name;?>" alt="" height="96">
                                            </span>                                        
                                    </div>
                                   
                                </div> <br>

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

                    
                                <form action="route_extra.php?action=ForgotPassword" method="POST">

                                <input type="hidden" name="token" id="token" value="<?php echo $token;?>">

                                    <div class="mb-3">
                                        <label for="username" class="form-label">New Password</label>
                                        <input class="form-control" type="password" name="npassword" id="npassword" required="" placeholder="Enter new password">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Enter confirm password">
                                            <div class="input-group-text" data-password="false">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary" type="submit" name="submit" value="Login"> Change Password </button>
                                    </div>

                                </form>



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
include "login_footer.php";
?>
