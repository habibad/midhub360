<?php
include "login.check.php";
include "header.php"; 

$nid = Xss::xssClean(Input::get('id'));

$notifica_result = Notice::ReadNotification($nid,$us_id);

$row = mysqli_fetch_object($notifica_result);

$message      = $row->message;
$datetime     = $row->submitted_time;
$message_from = $row->MsgFrom;
$seen_status  = $row->seen;

if($seen_status == 0)
{
    $query = "UPDATE notification SET seen='1' WHERE id='$nid'";    
    $result = mysqli_query($connect, $query);    
    if(!$result)
    {                      
        die("Query Failed!" . mysqli_error($connect));
    }
}

?>
        <!-- page content start -->

        <div class="main-container">
            <div class="container">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="row">
                            <div class="col-auto">
                                <div class="icon icon-40 rounded bg-default-light"><i class="material-icons text-default">local_mall</i></div>
                            </div>
                            <div class="col pl-0 align-self-center">
                                <h6 class="mb-1"><?php echo $message_from; ?></h6>
                                <p class="text-secondary "><small class="float-right text-secondary"><?php echo $datetime; ?></small></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        
                        <p><?php echo $message; ?></p>                        
                    </div>
                </div>
            </div>
        </div>
    </main>

  
<?php 
include "footer.php"; 
?>