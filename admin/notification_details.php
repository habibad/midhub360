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


            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>                                            
                                            <li class="breadcrumb-item active">Notification Details</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Notification Details</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       
                                        <div class="row" id="simple-dragula" data-plugin="dragula">                                            
                                       
                                            <div class="col-md-12">
                                                <div class="card mb-0 mt-3 border border-success">
                                                    <div class="card-body">
                                                    <span class="badge badge-soft-secondary float-end"><i class="mdi mdi-calendar"></i> <?php echo $datetime; ?></span>
                                                        <h4 class="mt-0 text-success"><strong><?php echo $message_from; ?></strong></h4><br>
                                                        
                                                        <?php echo $message; ?>                                                         
                                                                                                              
                                                    </div> <!-- end card-body-->
                                                </div> <!-- end card-->
                                            </div> <!-- end col-->


                                        </div>
                                        <!-- end row-->

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> <!-- end row -->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php
include "footer.php";
?>