<?php
session_start();
//include "android.php";
//include "security.php";
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";
$apps_name        = branddata("apps_name");
$web_link         = branddata("web_link");
$logo_name        = branddata("logo_url");  
$company_name     = CompanyData("name");  
$phone            = CompanyData("phone");  


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
    
       
    $query  = "SELECT *from user where user='$user' and pass=md5('$pass')  and active='1' and level!='5'";    
    
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

                header("location: customer/home.php");

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

            <?php echo $error_msg ?>
            
            <form method="post" name="login" action=""> 

            <div class="container">
                <label for="username"><b>Username</b></label>
                <input type="text" placeholder="Enter Username" name="username" required>

                <label for="password"><b>Password</b></label>
                <input type="password" placeholder="Enter Password" name="password" required>
        
                <button type="submit" name="submit" value="Login">Login</button>
                <label>
                <input type="checkbox" checked="checked" name="remember"> Remember me
                </label>

                <p class="text-right"><a href="forgot_password.php" class="text-white">Forgot Password?</a></p>                
               
                <div><a class="btn btn-success btn-block" href="registration.php">Sign Up</a></div>
            
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