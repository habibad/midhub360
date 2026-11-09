<?php
require_once 'security.php';
include "class/Session.php";
include "../db.php";
include "../function.php";
require_once 'autoload.php';

$notifica_count  = Notice::NoticeCount($us_id);
$notifica_result = Notice::notification($us_id);

?>

<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8" />
        <title>BillsMama - Online recharge management system.</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="BillsMama is a automated recharge and mobile banking system." name="description" />
        <meta content="BillsMama" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="../assets/images/icon/favicon.ico">

        <!-- third party css -->
        <link href="../assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        
        <!-- Plugins css -->
        <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/libs/selectize/css/selectize.bootstrap3.css" rel="stylesheet" type="text/css" />
        <link href="../assets/libs/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
               
        <!-- App css -->
        <link href="../assets/css/config/purple/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
        <link href="../assets/css/config/purple/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

        <link href="../assets/css/config/purple/bootstrap-dark.min.css" rel="stylesheet" type="text/css" id="bs-dark-stylesheet" />
        <link href="../assets/css/config/purple/app-dark.min.css" rel="stylesheet" type="text/css" id="app-dark-stylesheet" />

        <!-- icons -->
        <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

         <!-- Custom css -->        
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css" />

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MDBCGC5');</script>
        <!-- End Google Tag Manager -->
        

    </head>

    <!-- body start -->
    <body class="loading" data-layout='{"mode": "light", "width": "fluid", "menuPosition": "fixed", "sidebar": { "color": "light", "size": "default", "showuser": false}, "topbar": {"color": "dark"}, "showRightSidebarOnPageLoad": true}'>

        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MDBCGC5"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    
    
    <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-end mb-0">

           
            <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="fullscreen" href="#">
                    <i class="fe-maximize noti-icon"></i>
                </a>
            </li>
    
            
            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-bell noti-icon"></i>
                    <?php

                        if($notifica_count > 0)
                        {                        
                        ?>
                            <span class="badge bg-danger rounded-circle noti-icon-badge"><?php echo $notifica_count; ?></span>
                    <?php
                      }                                
                    ?>
                     <?php

                        if($notifica_count <= 0)
                        {                        
                        ?>
                            <span class="noti-icon-badge"></span>
                    <?php
                      }                                
                    ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-lg">
    
                    <!-- item-->
                    <div class="dropdown-item noti-title">
                        <h5 class="m-0">
                            <span class="float-end">
                                <a href="" class="text-dark">
                                    <small>Clear All</small>
                                </a>
                            </span>Notification
                        </h5>
                    </div>
    
                    <div class="noti-scroll" data-simplebar>
    
                        
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
                                     
                        <!-- item-->
                        <a href="notification_details.php?id=<?= $notifica_id ?>" class="dropdown-item notify-item active">
                            <div class="notify-icon bg-primary">
                                <i class="mdi mdi-comment-account-outline"></i>
                            </div>
                            <p class="notify-details"><?php echo $message_from; ?></p>
                            <p class="text-muted mb-0 user-msg">
                                <small><?php echo $message; ?></small>
                            </p>
                        </a>

                        <?php
                            }                                
                        ?>


                        <?php

                        if($seen_status == "1")
                        {                        
                        ?>
                                     
                        <!-- item-->
                        <a href="notification_details.php?id=<?= $notifica_id ?>" class="dropdown-item notify-item">
                            <div class="notify-icon bg-secondary">
                                <i class="mdi mdi-comment-account-outline"></i>
                            </div>
                            <p class="notify-details"><?php echo $message_from; ?></p>
                            <p class="text-muted mb-0 user-msg">
                                <small><?php echo $message; ?></small>
                            </p>
                        </a>

                        <?php
                            }                                
                        ?>

                      
                        <?php
                            }                                
                        ?>

    
                       
                    </div>
    
                    <!-- All-->
                    <a href="notification_list.php" class="dropdown-item text-center text-primary notify-item notify-all">
                        View all
                        <i class="fe-arrow-right"></i>
                    </a>
    
                </div>
            </li>
    
            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="../assets/images/users/logo.png" alt="user-image" class="rounded-circle">
                    <span class="pro-user-name ms-1">
                    <?php echo $user_name;?> <i class="mdi mdi-chevron-down"></i> 
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                    <!-- item-->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome <?php echo $user_name;?>!</h6>
                    </div>
    
                    <!-- item-->
                    <a href="update_myprofile.php" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>My Account</span>
                    </a>
    
                    <!-- item-->
                    <a href="update_mysettings.php" class="dropdown-item notify-item">
                        <i class="fe-settings"></i>
                        <span>Settings</span>
                    </a>
    
                    <!-- item-->
                    <a href="#" class="dropdown-item notify-item">
                        <i class="fe-lock"></i>
                        <span>Lock Screen</span>
                    </a>
    
                    <div class="dropdown-divider"></div>
    
                    <!-- item-->
                    <a href="logout.php" class="dropdown-item notify-item">
                        <i class="fe-log-out"></i>
                        <span>Logout</span>
                    </a>
    
                </div>
            </li>
    
            <li class="dropdown notification-list">
                <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect waves-light">
                    <i class="fe-settings noti-icon"></i>
                </a>
            </li>
    
        </ul>
    
        <!-- LOGO -->
        <div class="logo-box">
            <a href="index.html" class="logo logo-dark text-center">
                <span class="logo-sm">
                    <img src="../assets/images/logo-sm.png" alt="" height="22">                    
                </span>
                <span class="logo-lg">
                    <img src="../assets/images/logo-dark.png" alt="" height="20">                    
                </span>
            </a>
    
            <a href="index.html" class="logo logo-light text-center">
                <span class="logo-sm">
                    <img src="../assets/images/logo-sm.png" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="../assets/images/logo-light.png" alt="" height="20">
                </span>
            </a>
        </div>
    
        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <li>
                <button class="button-menu-mobile waves-effect waves-light">
                    <i class="fe-menu"></i>
                </button>
            </li>

            <li>
                <!-- Mobile menu toggle (Horizontal Layout)-->
                <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>   


            <li class="dropdown dropdown-mega d-none d-xl-block">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    Mega Menu
                    <i class="mdi mdi-chevron-down"></i> 
                </a>
                <div class="dropdown-menu dropdown-megamenu">
                    <div class="row">
                        <div class="col-sm-8">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="text-dark mt-0">History</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="recharge_history.php">Rechage History</a>
                                        </li>
                                        <li>
                                            <a href="mbank_history.php">Mobile Banking History</a>
                                        </li>
                                        <li>
                                            <a href="drive_history.php">Drive History</a>
                                        </li>
                                        <li>
                                            <a href="billpay_history.php">Bill Pay History</a>
                                        </li>
                                        <li>
                                            <a href="bl_request_history.php">Balance Request History</a>
                                        </li>
                                        <li>
                                            <a href="#">Global Recharge History</a>
                                        </li>
                                        <li>
                                            <a href="banking_history.php">Banking History</a>
                                        </li>
                                    </ul>
                                </div>
    
                                <div class="col-md-4">
                                    <h5 class="text-dark mt-0">Tools</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="change_password.php">Change Password</a>
                                        </li>
                                        <li>
                                            <a href="change_pin.php">Change Pin Code</a>
                                        </li>
                                        <li>
                                            <a href="change_phone.php">Change Phone Number</a>
                                        </li>
                                        <li>
                                            <a href="change_email.php">Change Email Address</a>
                                        </li>
                                        <li>
                                            <a href="update_profile.php">Update Profile</a>
                                        </li>
                                                                               
                                    </ul>
                                </div>

                            <?php                                  
                                if ($level == 4 )
                            {
                            ?>
 
                                <div class="col-md-4">
                                    <h5 class="text-dark mt-0">Customers</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="add_customer.php">Add Customer</a>
                                        </li>                                       
                                        <li>
                                            <a href="customer_level4.php">Sole Distributor List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level3.php">Distributor List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Reseller List</a>
                                        </li>  
                                        <li>
                                            <a href="customer_level0.php">Retailer List</a>
                                        </li>                                       
                                 

                                    </ul>
                                </div>

                            <?php
                                }                                
                            ?>


                            <?php                                  
                                if ($level == 3 )
                            {
                            ?>
 
                                <div class="col-md-4">
                                    <h5 class="text-dark mt-0">Customers</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="add_customer.php">Add Customer</a>
                                        </li>                                       
                                       
                                        <li>
                                            <a href="customer_level3.php">Distributor List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Reseller List</a>
                                        </li>                                       
                                    </ul>
                                </div>

                            <?php
                                }                                
                            ?>

                            <?php                                  
                                if ($level == 2 )
                            {
                            ?>
 
                                <div class="col-md-4">
                                    <h5 class="text-dark mt-0">Customers</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="add_customer.php">Add Customer</a>
                                        </li>                                      
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Retailer List</a>
                                        </li>                                       
                                    </ul>
                                </div>

                            <?php
                                }                                
                            ?>




                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-center mt-3">
                                <h3 class="text-dark">Special Discount Sale!</h3>
                                <h4>Save up to 70% off.</h4>
                                <button class="btn btn-primary btn-rounded mt-3">Download Now</button>
                            </div>
                        </div>
                    </div>
    
                </div>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- end Topbar -->

            <!-- ========== Left Sidebar Start ========== -->
            <div class="left-side-menu">

                <div class="h-100" data-simplebar>

                    <!-- User box -->
                    <div class="user-box text-center">
                        <img src="../assets/images/users/user-1.jpg" alt="user-img" title="Mat Helme"
                            class="rounded-circle avatar-md">
                        <div class="dropdown">
                            <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                                data-bs-toggle="dropdown">Geneva Kennedy</a>
                            <div class="dropdown-menu user-pro-dropdown">

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-user me-1"></i>
                                    <span>My Account</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-settings me-1"></i>
                                    <span>Settings</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-lock me-1"></i>
                                    <span>Lock Screen</span>
                                </a>

                                <!-- item-->
                                <a href="logout.php" class="dropdown-item notify-item">
                                    <i class="fe-log-out me-1"></i>
                                    <span>Logout</span>
                                </a>

                            </div>
                        </div>
                        <p class="text-muted">Admin Head</p>
                    </div>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <ul id="side-menu">
                                                                    

                            <li>
                                <a href="home.php">
                                <i class="mdi mdi-view-dashboard-outline"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>
                                        

                            <li>
                                <a href="#sidebarEcommerce" data-bs-toggle="collapse">
                                <i class="mdi mdi-arrow-top-left"></i>
                                    <span> New Request</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEcommerce">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="send_recharge.php">Recharge</a>
                                        </li>
                                        <li>
                                            <a href="send_drive.php">Drive</a>
                                        </li>
                                        <li>
                                            <a href="send_package.php">Package</a>
                                        </li>
                                        <li>
                                            <a href="send_mnp.php">MNP</a>
                                        </li>
                                        <li>
                                            <a href="send_bkash.php">bKash</a>
                                        </li>
                                        <li>
                                            <a href="send_rocket.php">Rocket</a>
                                        </li>
                                        <li>
                                            <a href="send_nagad.php">Nagad</a>
                                        </li>
                                        <li>
                                            <a href="send_upay.php">Upay</a>
                                        </li>
                                        <li>
                                            <a href="send_others_mbank.php">Others M. Banking</a>
                                        </li>
                                        <li>
                                            <a href="send_bill.php">Bill Pay</a>
                                        </li>
                                        <li>
                                            <a href="send_add_balance.php">Add Balance</a>
                                        </li>

                                        <li>
                                            <a href="#">Global Recharge</a>
                                        </li>  

                                        <li>
                                            <a href="send_bank.php">Banking</a>
                                        </li>  

                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarCrm" data-bs-toggle="collapse">
                                <i class="fe-search"></i>
                                    <span> History </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarCrm">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="recharge_history.php">Recharge History</a>
                                        </li>
                                        <li>
                                            <a href="drive_history.php">Drive History</a>
                                        </li>
                                        <li>
                                            <a href="mbank_history.php">Mobile Banking History</a>
                                        </li>
                                        <li>
                                            <a href="billpay_history.php">Bill Pay History</a>
                                        </li>
                                        <li>
                                            <a href="bl_request_history.php">Balance Request History</a>
                                        </li>
                                        <li>
                                            <a href="#">Global Recharge History</a>
                                        </li>
                                        <li>
                                            <a href="banking_history.php">Banking History</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <?php                                  
                          if ($level == 4 )
                            {
                            ?>      
                            <li>
                                <a href="#sidebarcshist" data-bs-toggle="collapse">
                                <i class="fe-search"></i>
                                    <span> Customer's History </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarcshist">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="cs_recharge_history.php">Recharge History</a>
                                        </li>
                                        <li>
                                            <a href="cs_drive_history.php">Drive History</a>
                                        </li>
                                        <li>
                                            <a href="cs_mbank_history.php">Mobile Banking History</a>
                                        </li>
                                        <li>
                                            <a href="cs_billpay_history.php">Bill Pay History</a>
                                        </li>                                       
                                    </ul>
                                </div>
                            </li>

                            <?php
                                }                                
                            ?>
                            <!--

                            <li>
                                <a href="#sidebarEmail" data-bs-toggle="collapse">
                                    <i class="mdi mdi-email-multiple-outline"></i>
                                    <span> Operator Message </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEmail">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Recharge</a>
                                        </li>
                                        <li>
                                            <a href="#">Mobile Banking</a>
                                        </li>
                                                                              
                                    </ul>
                                </div>
                            </li>

                            -->
                         
                            <?php                                  
                          if ($level != 0 )
                            {
                            ?>
                            <li>
                                <a href="add_customer.php">
                                <i class="fe-user-plus"></i>
                                    <span> Add Customer </span>
                                </a>
                            </li>

                            <?php
                                }                                
                            ?>


                    <?php                                  
                          if ($level == 4 )
                            {
                            ?>

                            <li>
                                <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i class="fe-users"></i>
                                    <span> Customer List </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarProjects">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="customer_list.php">All Customers</a>
                                        </li>
                                        
                                        <li>
                                            <a href="customer_level3.php">Distributor List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Reseller List</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                             
                            <?php
                                }                                
                            ?>

                    <?php                                  
                          if ($level == 3 )
                            {
                            ?>

                            <li>
                                <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i class="fe-users"></i>
                                    <span> Customer List </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarProjects">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="customer_list.php">All Customers</a>
                                        </li>                                        
                                       
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Reseller List</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                             
                            <?php
                                }                                
                            ?>


                        <?php                                  
                          if ($level == 2 )
                            {
                            ?>

                            <li>
                                <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i class="fe-users"></i>
                                    <span> Customer List </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarProjects">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="customer_list.php">All Customers</a>
                                        </li>                                      
                                       
                                        <li>
                                            <a href="customer_level2.php">Dealer List</a>
                                        </li>
                                        <li>
                                            <a href="customer_level1.php">Retailer List</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                             
                            <?php
                                }                                
                            ?>


                        <?php                                  
                          if ($level == 1 )
                            {
                            ?>

                            <li>
                                <a href="customer_level1.php">
                                    <i class="fe-users"></i>
                                    <span>Retailer List</span>
                                </a>
                            </li>

                            <?php
                                }                                
                            ?>


                            <li>
                                <a href="#sidebarMaps" data-bs-toggle="collapse">
                                    <i class="mdi mdi-cash-multiple"></i>
                                    <span>Payment History </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarMaps">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="payment_receive_recharge.php">Received Recharge</a>
                                        </li>

                                        <li>
                                            <a href="payment_receive_mb.php">Received M. Bank</a>
                                        </li>

                                        <?php                                  
                                            if ($level != 0 )
                                            {
                                            ?>

                                            <li>
                                                <a href="payment_given_recharge.php">Given Recharge</a>
                                            </li>

                                        <?php
                                            }                                
                                         ?>

                                      
                                        <?php                                  
                                            if ($level != 0 )
                                            {
                                            ?>

                                        <li>
                                            <a href="payment_given_mb.php">Given M. Bank</a>
                                        </li>

                                        <?php
                                            }                                
                                         ?>
                                        
                                    </ul>
                                </div>
                            </li>

                            <!-- 
                            <li>
                                <a href="#">
                                    <i class="mdi mdi-cash-multiple"></i>
                                    <span> Payment History </span>
                                </a>
                            </li>

                            -->

                            <!-- 

                            <li>
                                <a href="#sidebarTasks" data-bs-toggle="collapse">
                                    <i class="mdi mdi-clipboard-multiple-outline"></i>
                                    <span> Offline Service </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTasks">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Sim Registration</a>
                                        </li>
                                        <li>
                                            <a href="#">Sim List</a>
                                        </li>
                                        <li>
                                            <a href="#">SMS Reply History</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            -->

                            <!-- 

                            <li>
                                <a href="#sidebarContacts" data-bs-toggle="collapse">
                                    <i class="fe-settings"></i>
                                    <span> Administration </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarContacts">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Add Drive</a>
                                        </li>
                                        <li>
                                            <a href="#">Drive List</a>
                                        </li>

                                        <li>
                                            <a href="#">Add Package</a>
                                        </li>

                                        <li>
                                            <a href="#">Package List</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                            -->

                            <!-- 
                            <li>
                                <a href="#sidebarTickets" data-bs-toggle="collapse">
                                    <i class="mdi mdi-wrench"></i>
                                    <span> Tools </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTickets">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Delete Data</a>
                                        </li>
                                        <li>
                                            <a href="#">Settings</a>
                                        </li>
                                        <li>
                                            <a href="#">Server On/Off</a>
                                        </li>
                                        <li>
                                            <a href="#">Operator On/Off</a>
                                        </li>                                        
                                        <li>
                                            <a href="#">Branding</a>
                                        </li>
                                        <li>
                                            <a href="#">Update Compaby Info</a>
                                        </li>
                                        <li>
                                            <a href="#">Payment Information</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            -->


                            <li>
                                <a href="#sidebarReports" data-bs-toggle="collapse">
                                    <i class="mdi mdi-poll"></i>
                                    <span> Reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarReports">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="sales_report.php">Sales Report</a>
                                        </li>
                                        <li>
                                            <a href="sales_operator.php">Operator Sales</a>
                                        </li> 
                                        <li>
                                            <a href="balance_sheet.php">Balance Sheet</a>
                                        </li> 
                                        <li>
                                            <a href="#">Statements</a>
                                        </li>                                                                       
                                    </ul>
                                </div>
                            </li>


                            <li>
                                <a href="#sidebarAPI" data-bs-toggle="collapse">
                                    <i class="mdi mdi-wrench-outline"></i>
                                    <span> API Tools </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAPI">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Documentation</a>
                                        </li>
                                                                                                            
                                    </ul>
                                </div>
                            </li>


                            <li>
                                <a href="notice_list.php">
                                    <i class="mdi mdi-lifebuoy"></i>
                                    <span>Notice</span>
                                </a>
                            </li>

                            <li>
                                <a href="#sidebarSecurity" data-bs-toggle="collapse">
                                    <i class="fe-settings"></i>
                                    <span> Settings </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarSecurity">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="change_password.php">Change Password</a>
                                        </li>
                                        <li>
                                            <a href="change_pin.php">Change Pin Code</a>
                                        </li>
                                        <li>
                                            <a href="change_phone.php">Change Phone Number</a>
                                        </li>
                                        <li>
                                            <a href="change_email.php">Change Email Address</a>
                                        </li>
                                        <li>
                                            <a href="update_profile.php">Update Profile</a>
                                        </li>                                                                     
                                    </ul>
                                </div>
                            </li>

                             <li>
                                <a href="#sidebarNotice" data-bs-toggle="collapse">
                                    <i class="mdi mdi-message-alert"></i>
                                    <span> Complain </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarNotice">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#">Write Complain</a>
                                        </li>
                                        <li>
                                            <a href="#">Complain History</a>
                                        </li>
                                                                     
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="login_logs.php">
                                    <i class="mdi mdi-login"></i>
                                    <span> Logs </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <i class="mdi mdi-help-circle"></i>
                                    <span> Help </span>
                                </a>
                            </li>

                            <li>
                                <a href="logout.php">
                                    <i class="ti-power-off"></i>
                                    <span> Logout </span>
                                </a>
                            </li>



                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->

           