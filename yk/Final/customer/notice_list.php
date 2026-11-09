<?php 
include "login.check.php";
include "header.php";

$notice_result = Notice::ReadNotice("1");

$row = mysqli_fetch_object($notice_result);

$FullNotice   = $row->FullNotice;
$datetime     = $row->submitted_time;

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
                                            <li class="breadcrumb-item active">Latest Notice</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Latest Notice</h4>
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
                                                       
                                                        <?php echo $FullNotice; ?> 
                                                                                                      
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