<?php 
include "login.check.php";
include "header.php";


$cslevel5  = Mixedfn::totalcs("4");
$cslevel4  = Mixedfn::totalcs("3");
$cslevel3  = Mixedfn::totalcs("2");
$cslevel2  = Mixedfn::totalcs("1");
$cslevel1  = Mixedfn::totalcs("0");

$total_cs = $cslevel5 + $cslevel4 + $cslevel3 + $cslevel2 +$cslevel1;

$cslevel5_bl    = Mixedfn::cstotalbl("balance2","4");
$cslevel4_bl    = Mixedfn::cstotalbl("balance2","3");
$cslevel3_bl    = Mixedfn::cstotalbl("balance2","2");
$cslevel2_bl    = Mixedfn::cstotalbl("balance2","1");
$cslevel1_bl    = Mixedfn::cstotalbl("balance2","0");

$total_cs_bl = $cslevel5_bl + $cslevel4_bl + $cslevel3_bl + $cslevel2_bl + $cslevel1_bl;

$cslevel5_mb   =  Mixedfn::cstotalbl("bkash_balance","4");
$cslevel4_mb   =  Mixedfn::cstotalbl("bkash_balance","3");
$cslevel3_mb   =  Mixedfn::cstotalbl("bkash_balance","2");
$cslevel2_mb   =  Mixedfn::cstotalbl("bkash_balance","1");
$cslevel1_mb   =  Mixedfn::cstotalbl("bkash_balance","0");

$total_cs_mb = $cslevel5_mb + $cslevel4_mb + $cslevel3_mb + $cslevel2_mb + $cslevel1_mb;



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
                                            <li class="breadcrumb-item active">Customer Remaining Balance</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Customer Remaining Balance</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                               
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                                                                                                                     
                                        <table class="table table-striped dt-responsive nowrap w-100">
                                   
                                            
                                        <thead>
                                                <tr>                                                    
                                                    <th>Level</th>
                                                    <th>Total Customer</th>                                                   
                                                    <th>Rcharge Balance</th>                                                                                                
                                                    <th>MB. Balance</th>                                                                                                
                                                                                                   
                                                </tr>
                                            </thead>
                                        <tbody>
                                            
                                        
                                                    <tr>
                                                        <td>Subadmin</td>
                                                        <td><?php echo $cslevel5; ?></td>                                                       
                                                        <td><?php echo $cslevel5_bl; ?></td>
                                                        <td><?php echo $cslevel5_mb; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Distributor</td>
                                                        <td><?php echo $cslevel4; ?></td>                                                        
                                                        <td><?php echo $cslevel4_bl; ?></td>
                                                        <td><?php echo $cslevel4_mb; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>DGM</td>
                                                        <td><?php echo $cslevel3; ?></td>                                                        
                                                        <td><?php echo $cslevel3_bl; ?></td>
                                                        <td><?php echo $cslevel3_mb; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Dealer</td>
                                                        <td><?php echo $cslevel2; ?></td>                                                       
                                                        <td><?php echo $cslevel2_bl; ?></td>
                                                        <td><?php $cslevel2_mb; ?></td>
                                                    </tr>
                                                    <tr>                                                       
                                                        <td>Retailer</td>
                                                        <td><?php echo $cslevel1; ?></td>
                                                        <td><?php echo $cslevel1_bl; ?></td>
                                                        <td><?php $cslevel1_mb; ?></td>
                                                    </tr>


                                           
                                        </tbody>
                                
                                        <tfoot>
                                            <tr>
                                               
                                                                                            
                                                <th>Total:</th>
                                                <th><?php echo $total_cs; ?></th>
                                                <th>Tk. <?php echo $total_cs_bl; ?></th>
                                                <th>Tk. <?php echo $total_cs_mb; ?></th>                                               
                                                
                                            </tr>
                                        </tfoot>                                         

                                    </table>

                                  

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer-data.php";
?>
