<?php 
include "login.check.php";
include "header.php";
    
$gp_balance         = operator_balance("gp");
$robi_balance       = operator_balance("robi");
$blink_balance      = operator_balance("blink");
$airtel_balance     = operator_balance("airtel");
$teletalk_balance   = operator_balance("teletalk");

$re_sale     = Mixedfn::retodaysale();
$drive_sale  = Mixedfn::drivetodaysale();
$mb_sale     = Mixedfn::mbanktodaysale();
$bill_sale   = Mixedfn::billktodaysale();

$apicount = Paywell::apistatus("1","Paywell","Recharge");

    
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
                                        <form class="d-flex align-items-center mb-3">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control border" id="dash-daterange">
                                                <span class="input-group-text bg-blue border-blue text-white">
                                                    <i class="mdi mdi-calendar-range"></i>
                                                </span>
                                            </div>
                                            <a href="javascript: void(0);" class="btn btn-blue btn-sm ms-2">
                                                <i class="mdi mdi-autorenew"></i>
                                            </a>
                                            <a href="javascript: void(0);" class="btn btn-blue btn-sm ms-1">
                                                <i class="mdi mdi-filter-variant"></i>
                                            </a>
                                        </form>
                                    </div>
                                    <h4 class="page-title">Dashboard</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                      
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
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $re_sale; ?></span></h3>
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
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $drive_sale; ?></span></h3>
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
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $mb_sale; ?></span></h3>
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
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo $bill_sale; ?></span></h3>
                                                    <p class="text-muted mb-1 text-truncate">Bill Pay</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                    </div>
                        <!-- end row-->

                        <?php
                                    
                            if ($apicount == 1 )
                        {
                        ?>        

                     <div class="row">
                        <h4 class="page-title">API Balance</h4>
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
                                                    <h3 class="text-dark mt-1"><?php echo Paywell::recharge_balance(); ?></h3>
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
                                                    <i class="mdi mdi-bank font-22 avatar-title text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><span data-plugin="counterup"><?php echo Api::apibalance_mb(); ?></span></h3>
                                                    <p class="text-muted mb-1 text-truncate">M. Bank</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->                                   
                    </div>
                        <!-- end row-->

                    <?php
                    }                                
                    ?>

                    <?php
                                    
                        if ($apicount != 1 )
                    {
                    ?>    
                    <div class="row">

                        <h4 class="page-title">Operator Balance</h4>
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">                                                    
                                                    <img src="../assets/images/operators/GP.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><?php echo $gp_balance;?></h3>
                                                    <p class="text-muted mb-1 text-truncate">GP</p>
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
                                                <img src="../assets/images/operators/Robi.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><?php echo $robi_balance;?></h3>
                                                    <p class="text-muted mb-1 text-truncate">Robi</p>
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
                                                <img src="../assets/images/operators/Banglalink.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><?php echo $blink_balance;?></h3>
                                                    <p class="text-muted mb-1 text-truncate">Banglalink</p>
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
                                                <img src="../assets/images/operators/Airtel.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><?php echo $airtel_balance;?></h3>
                                                    <p class="text-muted mb-1 text-truncate">Airtel</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row-->
                             
                         <div class="row">                       
                            <div class="col-md-6 col-xl-3">
                                <div class="widget-rounded-circle card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                                <img src="../assets/images/operators/Teletalk.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1"><?php echo $teletalk_balance;?></h3>
                                                    <p class="text-muted mb-1 text-truncate">Teletalk</p>
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
                                                <img src="../assets/images/operators/bKash.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1">0</h3>
                                                    <p class="text-muted mb-1 text-truncate">bKash</p>
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
                                                <img src="../assets/images/operators/rocket.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1">0</h3>
                                                    <p class="text-muted mb-1 text-truncate">Rocket</p>
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
                                                <img src="../assets/images/operators/nagad.png" class="dash-img" alt="">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-end">
                                                    <h3 class="text-dark mt-1">0</h3>
                                                    <p class="text-muted mb-1 text-truncate">Nagad</p>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                    </div>
                                </div> <!-- end widget-rounded-circle-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row-->

                    <?php
                    }                                
                    ?>          


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
                                                <tbody> <?php
                        // Include fetch_data.php and display data
                        // include 'fetch_data.php';

                        // if (!empty($data)) {
                        //     foreach ($data as $row) {
                        //         echo "<tr>
                        //             <td>{$row['username']}</td>
                        //             <td>{$row['loaded_by']}</td>
                        //             <td>{$row['amount']}</td>
                        //             <td>{$row['old_balance']}</td>
                        //             <td>{$row['new_balance']}</td>
                        //         </tr>";
                        //     }
                        // } else {
                        //     echo "<tr><td colspan='5'>No data available</td></tr>";
                        // }
                        ?>
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
                                                    <?php
                        // Include fetch_data.php and display data
                        // include 'fetch_data.php';

                        // if (!empty($data)) {
                        //     foreach ($data as $row) {
                        //         echo "<tr>
                        //             <td>{$row['user']}</td>
                        //             <td>{$row['amount']}</td>
                        //             <td>{$row['balance']}</td>
                        //             </tr>";
                        //     }
                        // } else {
                        //     echo "<tr><td colspan='5'>No data available</td></tr>";
                        // }
                        ?>
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


<!-- used for page reload -->
<script>

  window.setTimeout(function () 
  {
  window.location.reload();
}, 30000);

</script>

<!-- used for page reload end-->