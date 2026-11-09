<?php 
include "login.check.php";
include "header.php";

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


    
$gp_balance         = operator_balance("gp");
$robi_balance       = operator_balance("robi");
$blink_balance      = operator_balance("blink");
$airtel_balance     = operator_balance("airtel");
$teletalk_balance   = operator_balance("teletalk");

$total_opbl = $gp_balance + $robi_balance + $blink_balance + $airtel_balance + $teletalk_balance;

$bl_status = $total_opbl - $total_cs_bl;

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
                                            <li class="breadcrumb-item active">Balance Sheet (Recharge)</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Balance Sheet (Recharge)</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        

                        <?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>							 
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">   
								
							  </button>
							</div>
							<?php
					  }?>
					<?php if (Session::exists('success')){
						?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
							  <strong>Well Done!</strong> <?=Session::Flush('success');?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">							 
								
							  </button>
							</div>
							<?php
					}?>


                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">

                                    <h3>Operator Balance</h3>  

                                    <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                   <thead>
                                           <tr>
                                                   <th>Operator Name</th>                                                                                                        
                                                   <th>Balance</th>                                                                                                   
                                                                                                   
                                           </tr>
                                       </thead>

                               <tbody>
                                                                         
                                        <tr>
                                           <td>Grameenphone</td>
                                           <td><?php echo $gp_balance; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Robi</td>
                                           <td><?php echo $robi_balance; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Banglalink</td>
                                           <td><?php echo $blink_balance; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Airtel</td>
                                           <td><?php echo $airtel_balance; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Teletalk</td>
                                           <td><?php echo $teletalk_balance; ?></td>
                                       </tr>
                           
                                       </tbody>

                                       <tfoot>
                                            <tr>
                                         
                                                <th>Total:</th>
                                                <th><?php echo $total_opbl; ?></th>
                                         
                                            </tr>
                                        </tfoot>        
                                   </table>              
        
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">

                                    <h3>Customer Balance</h3>
                                    
                                    
                                    <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                   <thead>
                                           <tr>
                                                   <th>Customer Type</th>                                                                                                        
                                                   <th>Balance</th>                                                                                                   
                                                                                                   
                                           </tr>
                                       </thead>

                               <tbody>
                                                                         
                                       <tr>
                                           <td>Sub Admin</td>
                                           <td><?php echo $cslevel5_bl; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Distributor</td>
                                           <td><?php echo $cslevel4_bl; ?></td>
                                       </tr>
                                       <tr>
                                           <td>DGM</td>
                                           <td><?php echo $cslevel3_bl; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Dealer</td>
                                           <td><?php echo $cslevel2_bl; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Retailer</td>
                                           <td><?php echo $cslevel1_bl; ?></td>
                                       </tr>
                                      
                                       </tbody>
                                       <tfoot>
                                            <tr>
                                         
                                                <th>Total:</th>
                                                <th><?php echo $total_cs_bl; ?></th>
                                         
                                            </tr>
                                        </tfoot>        
                                   </table>              

                              
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-4">                              
                               
                                <div class="card">
                                    <div class="card-body">                                       
                                      
                                       <h3>Balance Status</h3>   



                                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                        <th>Name</th>                                                                                                        
                                                        <th>Balance</th>                                                                                                   
                                                                                                        
                                                </tr>
                                            </thead>

                                    <tbody>
                                                                              
                                            <tr>
                                                <td>Customer Balance</td>
                                                <td><?php echo $total_cs_bl; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Operator Balance</td>
                                                <td><?php echo $total_opbl; ?></td>
                                            </tr>
                                           

                                            </tbody>

                                            <tfoot>
                                            <tr>
                                         
                                                <th>Balance Status:</th>

                                            <?php 
                                                 if ($bl_status < 0 )
                                                {
                                            ?>

                                                <th><span class="text-danger"><?php echo $bl_status; ?></span></th>

                                            <?php
                                                }                                
                                            ?>

                                            <?php 
                                                 if ($bl_status > 0 )
                                                {
                                            ?>

                                                <th><span class="text-success"><?php echo $bl_status; ?></span></th>

                                            <?php
                                                }                                
                                            ?>
                                         
                                            </tr>
                                        </tfoot>            
                                        </table>              

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->



                        </div>
                        <!-- end row -->                       

                        

                        <br>

                        <!-- end row -->


                        <!-- file preview template -->
                        <div class="d-none" id="uploadPreviewTemplate">
                            <div class="card mt-1 mb-0 shadow-none border">
                                <div class="p-2">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                        </div>
                                        <div class="col ps-0">
                                            <a href="javascript:void(0);" class="text-muted fw-bold" data-dz-name></a>
                                            <p class="mb-0" data-dz-size></p>
                                        </div>
                                        <div class="col-auto">
                                            <!-- Button -->
                                            <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                                <i class="dripicons-cross"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>