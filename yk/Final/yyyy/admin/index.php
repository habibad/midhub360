<?php
include "security.php";
include "../db.php";
require_once 'autoload.php';
include "../mixedfunction.php";
session_start();

$error_msg = "";

$login_failedip   = Security::totalloginfailed();
$login_faileduser = Security::totalloginfaileduser();


if($_SERVER['REQUEST_METHOD'] == 'POST'){   
        
    $user = $_POST["username"];
    $pass = $_POST["password"];      
      
    /* Function for prevent of sql injection */
    $user = stripcslashes($user);
    $pass = stripcslashes($pass);
    $user = mysqli_real_escape_string($connect, $user);
    $pass = mysqli_real_escape_string($connect, $pass);
    
    /* End of Function for prevent of sql injection */
    
       
    $query  = "SELECT *from user where user='$user' and pass=md5('$pass')  and active='1' and level='5' and id='1'";    
    
    $result = mysqli_query($connect, $query); 

    $reseller_data= mysqli_fetch_object($result);
    
    $count = mysqli_num_rows($result);  // If result matched $user and $pass, table row must be 1 row


    if($count == 1) 
    {  
               
        //check block/unblock
        $query1  = "SELECT * FROM user_blocklist where username='$user'"; 
        $result1 = mysqli_query($connect, $query1); 
        $count1 = mysqli_num_rows($result1);

        if($count1 > 0)
        {
            $error_msg = "<div class='errormsg'> Your account is blocked!<br><br></div>";
        }
        else
        {
                $us_id     =$reseller_data->id;
                $user_name =$reseller_data->user;
                $level     =$reseller_data->level;
            
                $_SESSION['user_name']         = $user;
                $_SESSION['us_id']             = $us_id;        
                $_SESSION['level']             = $level;         
                $_SESSION['userip']            = $_SERVER["REMOTE_ADDR"]; 
                $_SESSION['sess_username']     = $user; 
                $_SESSION['sess_pass']    	   = $pass; 
                $_SESSION['sess_uservalid']    = $sess_uservalid;

                $login_logs = Log::loginlogs("Success");

                header("location: home.php");

                /*
                if($level==3){  header("location: ./customer/home.php");}
                elseif($level==2){header("location: ./dgm/home.php");}
                elseif($level==1){header("location: ./dealer/home.php");}
                elseif($level==0){header("location: ./retailer/home.php");} */
        }
     }
     else 
     {
        $login_logs = Log::loginlogs("Failed");  
        
        if($login_failedip >= 5)
        {
            Security::IPBlock();
            header("location: forbidden.php");
        }
        if($login_faileduser >= 3)
        {
            Security::UserBlock();
            $error_msg = "<div class='errormsg'> Your account is blocked!<br><br></div>";
        }
        else
        {
            $error_msg = "<div class='errormsg'> Invalid Username or Password!<br><br></div>";
        }      
        
        
     }
     
}


?>


<?php 
include "login_header.php";
    
$apps_name        = branddata("apps_name");
$logo_name        = branddata("logo_url");  
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
                                                <img src="../uploads/logo/<?php echo $logo_name;?>" alt="" height="96">
                                            </span>                                        
                                    </div>
                                   
                                </div> <br>

                                <!-- -->

                                <?php echo $error_msg ?>

                                <form method="post" name="login" action=""> 

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input class="form-control" type="text" name="username" id="username" required="" placeholder="Enter your Username">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password">
                                            <div class="input-group-text" data-password="false">
                                                <span class="password-eye"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                            <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                        </div>
                                    </div>

                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary" type="submit" name="submit" value="Login"> Log In </button>
                                    </div>

                                </form>


                                <div class="text-center">                                   
                                    <div class="col-12 text-center mt-3">
                                        <p> <a href="forgot.php" class="ms-1"><b>Forgot Password</b></a></p>                                       
                                    </div>                                   
                                </div>


                                <!--
                                <div class="text-center">
                                    <h5 class="mt-3 text-muted">Sign in with</h5>
                                    <ul class="social-list list-inline mt-3 mb-0">
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-primary text-primary"><i class="mdi mdi-facebook"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-info text-info"><i class="mdi mdi-twitter"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-secondary text-secondary"><i class="mdi mdi-github"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            -->


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
