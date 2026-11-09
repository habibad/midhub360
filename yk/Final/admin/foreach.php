<?php
require_once '../db.php';
require_once 'autoload.php';
require_once 'api_function.php';
		
       
        $sql1 = "SELECT * from send_query where status='4' ORDER BY id ASC";
		$count = DB::getInstance()->get($sql1)->Count();

		if($count > 0)
        {
			$retrieve  = DB::getInstance()->get($sql1)->result();
           
			foreach ($retrieve as $flexi){
				$number = $flexi->phone;
				$type   = $flexi->load_type;
				$amount = $flexi->balance;
				$flexiId = $flexi->id;

                //echo "Number: " . $number;


              //$status = apihistorystatus("$number","$amount");  //Check API History
                $status = apihistorystatus("$flexiId");


                echo "Status: " . $status . "<br><br>";

                echo "Number: " . $amount . "<br><br>";

                echo "Amount: " . $number . "<br><br>";

                echo "ID: " . $flexiId . "<br><br>";

				
                if($status == "SUCCESS")
                {					

                     /*Update History Table Start*/

                     $query = "UPDATE send_query SET status='1' WHERE id ='$flexiId'";
                     global $connect; 
                     $result = mysqli_query($connect, $query);    
                     if(!$result)
                     {                      
                         die("Query Failed!" . mysqli_error($connect));
                     }

                      /*Update History Table End*/
				}
                else
                {
					continue;
				}

                
			}

		}else{
			echo json_encode
            (
				array
                (
					'status'  => 'error',
					'message' => 'Recently has no new request'
				)
			);
		}
	
