<?php 
include "login.check.php";
include "header.php";

$query = "WHERE user_id='$us_id' and status='1'";


$recharge   = "send_query";
$drive      = "drive_request";
$mbank      = "bkash_send";
$billpay    = "billpay_request";

$recharge_sale  = Mixedfn::salesreport("balance",$recharge,$query);
$drive_sale     = Mixedfn::salesreport("cost",$drive,$query);
$mbank_sale     = Mixedfn::salesreport("cost",$mbank,$query);
$bill_sale      = Mixedfn::salesreport("cost",$billpay,$query);

$total_resale = $recharge_sale + $drive_sale;


$rechargebl = "reseller_balance";
$mbbl       = "bkash_balance";

$received = "WHERE user_id='$us_id' and return_payment='0'";
$return   = "WHERE user_id='$us_id' and return_payment='1'";
$given    = "WHERE load_by='$us_id'";

$recharge_rece    = Mixedfn::totalbl($rechargebl,$received);
$recharge_return  = Mixedfn::totalbl($rechargebl,$return);
$recharge_given   = Mixedfn::totalbl($rechargebl,$given);

$total_rt  = $recharge_return + $recharge_given;
$total_abl = $recharge_rece - $total_rt;

$actual_bl = $total_abl - $total_resale;


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

                                    <h3>Balance</h3>  

                                    <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                   <thead>
                                           <tr>
                                                   <th>Type</th>                                                                                                        
                                                   <th>Amount</th>                                                                                                   
                                                                                                   
                                           </tr>
                                       </thead>

                               <tbody>
                                                                         
                                       <tr>
                                           <td>Received</td>
                                           <td><?php echo $recharge_rece; ?></td>                                       
                                       </tr>
                                       <tr>
                                           <td>Return</td>
                                           <td><span class="text-danger"><?php echo $recharge_return; ?></span></td>                                          
                                       </tr>
                                       <tr>
                                           <td>Given</td>
                                           <td><span class="text-danger"><?php echo $recharge_given; ?></span></td>                                          
                                       </tr>
                           
                                       </tbody>

                                       <tfoot>
                                            <tr>
                                         
                                                <th>Total:</th>
                                                <th><?php echo $total_abl; ?></th>
                                         
                                            </tr>
                                        </tfoot>        
                                   </table>              
        
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">

                                    <h3>Sales</h3>
                                    
                                    
                                    <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                   <thead>
                                           <tr>
                                                   <th>Type</th>                                                                                                        
                                                   <th>Amount</th>                                                                                                   
                                                                                                   
                                           </tr>
                                       </thead>

                               <tbody>
                                                                         
                                       <tr>
                                           <td>Recharge</td>
                                           <td><?php echo $recharge_sale; ?></td>                                           
                                       </tr>
                                       <tr>
                                           <td>Drive</td>
                                           <td><?php echo $drive_sale; ?></td>                                           
                                       </tr>
                                       </tbody>
                                       <tfoot>
                                            <tr>                                         
                                                <th>Total Sales:</th>
                                                <th><?php echo $total_resale; ?></th>                                                
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
                                                        <th>Type</th>                                                                                                        
                                                        <th>Balance</th>                                                                                                   
                                                                                                        
                                                </tr>
                                            </thead>

                                    <tbody>
                                                                              
                                            <tr>
                                                <td>Total Balance</td>
                                                <td><?php echo $total_abl; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Total Sales</td>
                                                <td><?php echo $total_resale; ?></td>
                                            </tr>

                                            </tbody>

                                            <tfoot>
                                            <tr>
                                         
                                                <th>Actual Balance:</th>
                                                <th><?php echo $actual_bl; ?></th>
                                         
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