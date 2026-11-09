<?php 
include "login.check.php";
include "header.php";

$gp_balance         = operator_balance("gp");
$robi_balance       = operator_balance("robi");
$blink_balance      = operator_balance("blink");
$airtel_balance     = operator_balance("airtel");
$teletalk_balance   = operator_balance("teletalk"); 


$notice_result = Notice::ReadNotice("2");
$row = mysqli_fetch_object($notice_result);
$FullNotice = $row->FullNotice;

$billpay_row = Mixedfn::ac_balance("balance",$us_id);
$billpay_balance = $billpay_row->balance;

$recharge_row = Mixedfn::ac_balance("balance2",$us_id);
$recharge_balance = $recharge_row->balance2;

$mbank_row = Mixedfn::ac_balance("bkash_balance",$us_id);
$mbank_balance = $mbank_row->bkash_balance;


$title_level     = 0;

$userlevel       = Mixedfn::CustomerLevel($us_id);    

$subadmin_name   = Mixedfn::levelnamechange("1","subadmin");
$level4_name     = Mixedfn::levelnamechange("1","level4");
$level3_name     = Mixedfn::levelnamechange("1","level3");
$level2_name     = Mixedfn::levelnamechange("1","level2");
$level1_name     = Mixedfn::levelnamechange("1","level1");


if ($userlevel == "4")
{
    $title_level = $subadmin_name;
}
if ($userlevel == "3")
{
    $title_level = $level4_name;
}
if ($userlevel == "2")
{
    $title_level = $level3_name;
}
if ($userlevel == "1")
{
    $title_level = $level2_name;
}
if ($userlevel == "0")
{
    $title_level = $level1_name;
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

                                <br>  <marquee class="text-danger" behavior = 'scroll' direction = 'left' onmouseout=this.start(); onmouseover=this.stop(); scrollamount='2' scrolldelay='40' truespeed='truespeed'>
                                            <?php echo $FullNotice; ?>
                                      </marquee>
                                    <div class="page-title-right">
                                        <span class="d-flex align-items-center mb-3">
                                            <span class="btn btn-blue btn-m ms-2 for-custom-space">
                                                <i class="mdi mdi-wallet"></i>
                                            </span>
                                            <div class="balance-card">
                                                <span class="for-custom-space">Recharge: Tk.</span><span class="balance-font"><?php echo $recharge_balance;?> </span> 
                                                <span class="for-custom-space"> | M. Bank:</span><span class="balance-font"><?php echo $mbank_balance;?> Tk.</span> 
                                                <!--<span class="for-custom-space"> | Billpay:</span><span class="balance-font"><?php //echo $billpay_balance;?> Tk.</span> -->
                                            </div>
                                            <a href="send_add_balance.php" class="btn btn-success btn-m ms-2">
                                                <i class="mdi mdi-plus"></i>
                                            </a>                                           
                                        </span>
                                    </div>
                                    <span class="title-txt">Welcome <strong><?php echo $fullname;?></strong>  (<?php echo $title_level;?>)!</span>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                         <!-- Error Code Start-->

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

                     <!-- Error Code End-->


                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">                               
                                <div class="page-title-box">
                                    <div class="page-title-right">                                        

                                    </div>
                                    <h6 class="page-title">Dashboard</h6>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 



                         <div class="row">

                       
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_recharge.php">
                                    <div class="card-body">
                                        <div class="row">                                            
                                            <div class="col-6">                                           
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                    
                                                    <img src="../assets/images/operators/recharge.png" class="dash-img" alt="">
                                                </div>                                              
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Recharge</h4>                                               
                                            </div>

                                            
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_drive.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                    <i class="ti-view-grid font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Drive</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_package.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                    <i class="mdi mdi-cube-outline font-22 avatar-title text-info"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Package</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->


                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_mnp.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                    <i class="mdi mdi-cube-outline font-22 avatar-title text-info"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">MNP</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                        </div>
                        <!-- end row-->
                                   

                         <div class="row"> 

                         <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_bkash.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                <img src="../assets/images/operators/bKash.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">bKash</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_rocket.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                <img src="../assets/images/operators/rocket.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Rocket</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_nagad.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                <img src="../assets/images/operators/nagad.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Nagad</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                  <a href="send_upay.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                <img src="../assets/images/operators/upay.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Upay</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                  </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <a href="send_bill.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                    <i class="mdi mdi-lightbulb-on-outline font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Bill Pay</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->


                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                  <a href="send_bank.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                    <i class="mdi mdi-bank font-22 avatar-title text-info"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Banking</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                <a href="send_add_balance.php">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                    <i class="mdi mdi-wallet-plus font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">                                                
                                                    <h4 class="text-dark mt-3">Add Fund</h4>                                               
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                    </a>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->



                        </div>
                        <!-- end row-->




                    <div class="row">

                        <h4 class="page-title">Today's Sales</h4>
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                    <i class="ti-mobile font-22 avatar-title text-info"></i>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">0</span></h3>
                                                    <p class="text-muted mb-1 text-truncate">Recharge</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                    <i class="ti-view-grid font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">0</span></h3>
                                                    <p class="text-muted mb-1 text-truncate">Drive</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                    <i class="mdi mdi-bank font-22 avatar-title text-info"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">0</span></h3>
                                                    <p class="text-muted mb-1 text-truncate">Mobile Banking</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->

                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                                    <i class="mdi mdi-lightbulb-on-outline font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup">0</span></h3>
                                                    <p class="text-muted mb-1 text-truncate">Bill Pay</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row-->






                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-end">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Edit Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                            </div>
                                        </div>
    
                                        <h4 class="header-title mb-3">Last 5 Recharge Payments</h4>
    
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-hover table-nowrap table-centered m-0">
    
                                                <thead class="table-light">
                                                    <tr>
                                                        <th colspan="2">Username</th>
                                                        <th>Loaded By</th>
                                                        <th>Amount</th>
                                                        <th>Old Balance</th>
                                                        <th>New Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="width: 36px;">
                                                            <img src="../assets/images/users/user-2.jpg" alt="contact-img" title="contact-img" class="rounded-circle avatar-sm" />
                                                        </td>
        
                                                        <td>
                                                            <h5 class="m-0 fw-normal font-13">Test</h5>
                                                            <p class="mb-0 text-muted"><small>Test 1</small></p>
                                                        </td>
        
                                                        <td>
                                                            <i class="mdi mdi-currency-btc text-primary"></i> Test 2
                                                        </td>
        
                                                        <td>
                                                           Test 3
                                                        </td>
        
                                                        <td>
                                                            Test 4
                                                        </td>
        
                                                        <td>
                                                            Test 5
                                                        </td>
                                                    </tr>
        
                                                    
    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end col -->

                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-end">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Edit Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                            </div>
                                        </div>
    
                                        <h4 class="header-title mb-3">Last 5 Mobile Banking Payment</h4>
    
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-nowrap table-hover table-centered m-0">
    
                                                <thead class="table-light">
                                                    <tr>
                                                    <th colspan="2">Username</th>
                                                        <th>Loaded By</th>
                                                        <th>Amount</th>
                                                        <th>Old Balance</th>
                                                        <th>New Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h5 class="m-0 fw-normal font-13">Test 1</h5>
                                                        </td>
        
                                                        <td>
                                                            Test 2
                                                        </td>
        
                                                        <td>
                                                           Test 3
                                                        </td>
        
                                                        <td>
                                                            Test 4
                                                        </td>
        
                                                        <td>
                                                            Test 5
                                                        </td>
                                                    </tr>
    
    
                                                </tbody>
                                            </table>
                                        </div> <!-- end .table-responsive-->
                                    </div>
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->
                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>