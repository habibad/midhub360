<?php
include"../db.php";



$page_url=basename($_SERVER["PHP_SELF"]);

if($page_url=="send_recharge.php" || $page_url=="modal_send_request.php" || $page_url=="internet_package.php" || $page_url=="m_request.php")
{


    $phone_for_validation = $_SERVER['REQUEST_METHOD'] == 'number'; 
    
    

      echo "$phone_for_validation";
                
            //$user = $_POST["number"];

            //$phone_for_validation = $user;



         /*   

            if($phone_for_validation!="")
            {
                $extension=$phone_for_validation[0].$phone_for_validation[1].$phone_for_validation[2];

                //echo "$extension";  


                $query = "SELECT * from operator_status where extension='$extension'";

                        $result = mysqli_query($connect, $query);

                        while($r=mysqli_fetch_object($result ))
                        {
                            $extension_status=$r->status;

                            if($extension_status==0)
                            {
                                echo "<h2 align='center'>$r->msg<br/><a href='home.php'>Back</a></h2><br/>";
                                exit;
                            }
                        }

            }   



            //echo "$user";
    */        
        
}


?>


 <!--
        
        
        <div class="container h-100 text-white">
            <div class="row h-100">
                <div class="col-12 align-self-center mb-4">
                    <div class="row justify-content-center">
                        <div class="col-11 col-sm-7 col-md-6 col-lg-5 col-xl-4">         
            

            
            <form method="post" name="login" action=""> 

            <div class="container">
                <label for="username"><b>Number</b></label>
                <input type="number" placeholder="Enter Username" name="username" required>

                <button type="submit" name="submit" value="Login">Login</button>
                <label>
                <input type="checkbox" checked="checked" name="remember"> Remember me
                </label>

               
            
            </div>
              
        </form>

                        </div>                        
                    </div>                    
                </div>               
            </div>            
        </div>        
    </main>

-->