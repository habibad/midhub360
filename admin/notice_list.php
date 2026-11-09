<?php 
include "login.check.php";
include "header.php";
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
                                            <li class="breadcrumb-item active">Notice List</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Notice List</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       
                                        <div class="row" id="simple-dragula" data-plugin="dragula">
                                            
                                            <?php

                                                $query = "SELECT * FROM noticelist order by id desc ";        
                                                $result = mysqli_query($connect, $query);

                                                if(!$result){
                                                    die("Query Failed!" . mysqli_error($connect));      
                                                }        

                                                $reseller_data = mysqli_fetch_object($result); 

                                                while($data = mysqli_fetch_array($result))
                                                {
                                            ?>


                                            <div class="col-md-6">
                                                <div class="card mb-0 mt-3 border border-success">
                                                    <div class="card-body">
                                                    <span class="badge badge-soft-secondary float-end"><i class="mdi mdi-calendar"></i> <?php echo $data['submitted_time']; ?></span>
                                                        <h4 class="mt-0 text-success"><strong><?php echo $data['short']; ?></strong></h4><br>
                                                        
                                                        <?php echo $data['FullNotice']; ?>                                                         
                                                                                                              
                                                    </div> <!-- end card-body-->
                                                </div> <!-- end card-->
                                            </div> <!-- end col-->

                                            <?php
                                                }                                
                                            ?>


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