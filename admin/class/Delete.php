<?php

class Delete 
{

    public static function DataDelete()
    {
        $deltype   = Input::get('deltype');
        $date      = Input::get('date');

        if($deltype == "recharge")
        {
            global $connect;
            $query = "DELETE FROM send_query WHERE s_date <'$date'";    
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            $query1 = "DELETE FROM reportserver WHERE date <'$date'";    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            Session::Flush('success', 'All recharge history prior to ' . $date . ' has been successfully deleted!');
            Redirect::to('delete_data.php');

        }
        elseif($deltype == "mbank")
        {
            global $connect;
            $query = "DELETE FROM bkash_send WHERE s_date <'$date'";    
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            $query1 = "DELETE FROM bkash_report WHERE date <'$date'";    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            Session::Flush('success', 'All M. Bank history prior to ' . $date . ' has been successfully deleted!');
            Redirect::to('delete_data.php');

        }
        elseif($deltype == "drive")
        {
            global $connect;
            $query = "DELETE FROM drive_request WHERE s_date <'$date'";    
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            $query1 = "DELETE FROM drive_msg WHERE date <'$date'";    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            Session::Flush('success', 'All Drive history prior to ' . $date . ' has been successfully deleted!');
            Redirect::to('delete_data.php');

        }
        elseif($deltype == "bill")
        {
            global $connect;
            $query = "DELETE FROM billpay_request WHERE s_date <'$date'";    
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            $query1 = "DELETE FROM billpay_msg WHERE date <'$date'";    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }

            Session::Flush('success', 'All Bill Pay history prior to ' . $date . ' has been successfully deleted!');
            Redirect::to('delete_data.php');

        }
        elseif($deltype == "sms")
        {
            global $connect;
            $query = "DELETE FROM sms_reply WHERE msg_date <'$date'";    
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {                      
            die("Query Failed!" . mysqli_error($connect));
            }          

            Session::Flush('success', 'All SMS Reply history prior to ' . $date . ' has been successfully deleted!');
            Redirect::to('delete_data.php');

        }
        else
        {
            Session::Flush('error', 'Invalid!');
            Redirect::to('delete_data.php');
        }
    }

}

?>