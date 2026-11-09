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
        <div class="main-container h-100">
            <div class="container h-100">
                <div class="row h-100">
                    <div class="col-12 col-md-6 col-lg-4 align-self-center text-center my-3 mx-auto">
                        
         
                <div class="alert alert-success">
                                    <h5>আপনার একাউন্টটি সফল ভাবে রেজিস্ট্রেশন হয়েছে। একাউন্টটি বর্তমানে ইনএকটিভ ভাবে রয়েছে। 
                                                একাউন্টটি একটিভ করতে নিচের নাম্বারে কল করুন। </h5> 

                                     <h5>Your account is successfully registered. Your account currently is inactive. 
                                      Contact us for active this account. Thanks. <br><br>
                                      
                                      Contact Number: <?php echo $phone; ?>
                                     </h5>  
                                     
                                     <div>
                            <a href="index.php" class="btn btn-info" role="button">Login</a>                   
                    </div>

							</div>
                    </div>
                </div>


                
               

            </div>

            

        </div>



    </main>

    <?php include "footer.php";?>