<?php
include "login.check.php";
include "header.php"; 

$notifica_count  = Notice::NoticeCount($us_id);
$notifica_result = Notice::notification($us_id);
?>

        <!-- page content start -->
       
        <div class="main-container">
            <div class="container">
                <div class="card">
                    <div class="card-body px-0">
                        <div class="list-group list-group-flush">

                        <?php

                            while($row = mysqli_fetch_array($notifica_result))
                            {

                                $message      = $row['message'];
                                $message_from = $row['MsgFrom'];
                                $datetime     = $row['submitted_time'];
                                $seen_status  = $row['seen'];
                                $notifica_id  = $row['id'];

                                if(strlen($message)>60)
                                {
                                    $message=substr($message,0,60).' ....';
                                }
                        ?>

                        <?php

                        if($seen_status == "0")
                        {                        
                        ?>

                            <a class="list-group-item bg-default-light" href="notification_details.php?id=<?= $notifica_id ?>">
                                <div class="row">
                                    <div class="col-auto align-self-center">
                                        <i class="material-icons text-default">local_mall</i>
                                    </div>
                                    <div class="col pl-0">
                                        <div class="row mb-1">
                                            <div class="col">
                                                <p class="mb-0"><?php echo $message_from; ?></p>
                                            </div>
                                            <div class="col-auto pl-0">
                                                <p class="small text-secondary"><?php echo $datetime; ?></p>
                                            </div>
                                        </div>
                                        <p class="small text-secondary"><?php echo $message; ?></p>
                                    </div>

                                </div>
                            </a>

                        <?php
                            }                                
                        ?>


                        <?php

                        if($seen_status == "1")
                        {                        
                        ?>

                            <a class="list-group-item" href="notification_details.php?id=<?= $notifica_id ?>">
                                <div class="row">
                                    <div class="col-auto align-self-center">
                                        <i class="material-icons text-default">account_balance_wallet</i>
                                    </div>
                                    <div class="col pl-0">
                                        <div class="row mb-1">
                                            <div class="col">
                                                <p class="mb-0"><?php echo $message_from; ?></p>
                                            </div>
                                            <div class="col-auto pl-0">
                                                <p class="small text-secondary"><?php echo $datetime; ?></p>
                                            </div>
                                        </div>
                                        <p class="small text-secondary"><?php echo $message; ?></p>
                                    </div>
                                </div>
                            </a>

                        <?php
                            }                                
                        ?>


                        <?php
                            }                                
                        ?>

                       

                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </main>

  
<?php 
    include "footer.php"; 
?>