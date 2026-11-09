<?php
session_start();
//include "android.php";
include "security.php";
include"db.php";
require_once 'autoload.php';
include "mixedfunction.php";
$apps_name  = branddata("apps_name");
$web_link         = branddata("web_link");
$logo_name        = branddata("logo_url");  
$company_name     = CompanyData("name");  
$phone            = CompanyData("phone");  


$error_msg = "";

$login_failedip   = Security::totalloginfailed();
$login_faileduser = Security::totalloginfaileduser();

$expFormat = mktime(date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y"));
$expDate = date("Y-m-d H:i:s",$expFormat);


if($_SERVER['REQUEST_METHOD'] == 'POST'){   
        
    $username = $_POST["username"];    
      
    /* Function for prevent of sql injection */
    $username = stripcslashes($username);
    
    $username = mysqli_real_escape_string($connect, $username);

    /* End of Function for prevent of sql injection */
    
       
    $query  = "SELECT * FROM user where user='$username' ";    
    
    $result = mysqli_query($connect, $query); 

    $reseller_data= mysqli_fetch_object($result);
    $email = $reseller_data->email ?? 0;
    $fullname = $reseller_data->fullname ?? 0;
    $user = $reseller_data->user ?? 0;
    
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
                
            $token = bin2hex(random_bytes(10));

            $query  = "INSERT INTO pass_reset (id,username,email,token,expDate,status)";
            $query .= "VALUES(0,'$user','$email','$token','$expDate','0')"; //Used concatenation (.) add two string
    
            $result = mysqli_query($connect, $query);    
            if(!$result)
            {
                
                die("Query Failed!" . mysqli_error($connect));
            }

       
            $to = $email;
            $subject = "Password Reset";
            
            $message = "Dear " . $fullname . ",<br><br>";
            $message .= "To reset the password to your " .$apps_name. " account, copy the code: (পাসওয়ার্ড রিসেট করার জন্য নিচের কোডটি কপি করুন)।<br><br>";
            $message .= "<h2>".$token. "</h2> <br><br>";
            $message .= "For your information:<br><br>Username: " .$user. "<br> IP Address: ".$_SERVER["REMOTE_ADDR"] ."<br><br>";
            //$message .= "If the above link is not clickable, please copy the whole link and paste it in your browser.<br><br>"; 
            $message .= "Should you have any questions or concerns, please contact us. Phone Number: " .$phone. "<br><br>"; 
            
            $header = "From:noreply@".$web_link. "\r\n";
            //$header .= "Cc:afgh@somedomain.com \r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";
            
            $retval = mail ($to,$subject,$message,$header);
            
            if( $retval == true ) 
            {
                /*
                $error_msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'> 
                আপনার একাউন্টে দেওয়া ইমেইলে পাসওয়ার্ড রিসেট এর লিংক পাঠানো হয়েছে। দয়া করে ইমেইল এর ইনবক্স চেক করুন। ইনবক্সে না পেলে স্পাম ফোল্ডার চেক করুন।
                Please check the registered email inbox or spam folder.</div>";
                */
                header("location: password_reset.php");
            }else {
                $error_msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Invalid </div>";
            }

            //header("location: customer/home.php");

               
        }
     }
     else 
     {
       
        
        $error_msg = "<div class='errormsg'> <b>" .$username.  "</b> নামে কোন ইউজার পাওয়া যায়নি। (User not found!)<br><br></div>";
            
        
        
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

                      
                <button type="submit" name="submit" value="Login">Reset Password</button>
                
            
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