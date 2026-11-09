<?php
//include "../android.php";
//include "security.php";
include "class/Session.php";
include "../db.php";
include "../function.php";
require_once 'autoload.php';

$notifica_count  = Notice::NoticeCount($us_id);
$notifica_result = Notice::notification($us_id);


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

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Hosting4bd Limited">
    <meta name="generator" content="">
    <title>Aanonda - Mobile Recharge System</title>

    <!-- manifest meta -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="../manifest.json" />

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="../img/favicon180.png" sizes="180x180">
    <link rel="icon" href="../img/favicon32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="../img/favicon16.png" sizes="16x16" type="image/png">

     <!-- Data Table -->
     <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.2.8/css/rowReorder.dataTables.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

    <!-- Flaticon -->
    <link rel="stylesheet" type="text/css" href="../font/flaticon.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">

    <!-- Material icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- swiper CSS -->
    <link href="../vendor/swiper/css/swiper.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/style-dark-purple.css" rel="stylesheet" id="style">  
    <link href="../css/custom.css" rel="stylesheet" id="style">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MDBCGC5');</script>
    <!-- End Google Tag Manager -->

</head>

<body class="body-scroll d-flex flex-column h-100 menu-overlay" data-page="homepage">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MDBCGC5"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- screen loader -->
    <div class="container-fluid h-100 loader-display">
        <div class="row h-100">
            <div class="align-self-center col">
                <div class="logo-loading">
                    <div class="icon icon-100 mb-4 rounded-circle">
                        <img src="../img/favicon144.png" alt="" class="w-100">
                    </div>
                    <h4 class="text-default">Aanonda</h4>
                    <p class="text-secondary">Mobile Recharge System</p>
                    <div class="loader-ellipsis">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- menu main -->
    <div class="main-menu">
        <div class="row mb-4 no-gutters">
            <div class="col-auto"><button class="btn btn-link btn-40 btn-close text-white"><span class="material-icons">chevron_left</span></button></div>
            <div class="col-auto">
                <div class="avatar avatar-40 rounded-circle position-relative">
                    <figure class="background">
                        <img src="../img/user3.png" alt="">
                    </figure>
                </div>
            </div>
            <div class="col pl-3 text-left align-self-center">
                <h6 class="mb-1"><?php echo $fullname;?> (<?php echo $title_level;?>)</h6>
                <p class="small text-default-secondary">Username: <?php echo $user_name;?></p>           
            </div>
        </div>
        <div class="menu-container">
            <!--
            <div class="row mb-4">
                <div class="col">
                    <h4 class="mb-1 font-weight-normal">$ 1548.00</h4>
                    <p class="text-default-secondary">My Balance</p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-default btn-40 rounded-circle" data-toggle="modal" data-target="#addmoney"><i class="material-icons">add</i></button>
                </div>
            </div>
            -->

            <ul class="nav nav-pills flex-column ">
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <div>
                            <span class="material-icons icon">home</span>
                            Home
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>


                <?php                                  
                    if ($level != 0 ){
                ?>
             
                <li class="nav-item">
                    <a class="nav-link" href="customer_list.php">
                        <div>
                            <span class="material-icons icon">groups</span>
                            Customer List
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_customer.php">
                        <div>
                            <span class="material-icons icon">person_add</span>
                            Add Customer
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>

                <?php
                    }                                
                ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="recharge_history.php">
                        <div>
                            <span class="material-icons icon">history</span>
                            Recharge History
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mbank_history.php">
                        <div>
                            <span class="material-icons icon">manage_search</span>
                            M. Banking History
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div>
                            <span class="material-icons icon">manage_search</span>
                            Others History
                        </div>                        
                    </a>
                    <div class="dropdown-menu nv-dropdown" aria-labelledby="dropdownMenuButton">                   
                        <a class="nv-dropdown-item" href="drive_history.php">Drive History</a>
                        <a class="nv-dropdown-item" href="billpay_history.php">Bill Pay History</a>
                        <a class="nv-dropdown-item" href="bank_history.php">Banking History</a>                                               
                    </div>
                </li> 
                
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div>
                            <span class="material-icons icon">receipt</span>
                            Payments History
                        </div>                        
                    </a>
                    <div class="dropdown-menu nv-dropdown" aria-labelledby="dropdownMenuButton">                   
                        <a class="nv-dropdown-item" href="payment_received.php">Recharge (Received)</a>
                    <?php                                  
                    if ($level != 0 )
                    {
                    ?>
                        <a class="nv-dropdown-item" href="payment_given.php">Recharge (Given)</a>
                    <?php
                    }                                
                    ?>
                        <a class="nv-dropdown-item" href="payment_received_mb.php">M. Bank (Received)</a>
                    <?php                                  
                    if ($level != 0 )
                    {
                    ?>
                        <a class="nv-dropdown-item" href="payment_given_mb.php">M. Bank (Given)</a> 
                    <?php
                    }                                
                    ?>                       
                    </div>
                </li> 
                
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div>
                            <span class="material-icons icon">description</span>
                            Reports
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li> 
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <div>
                            <span class="material-icons icon">settings</span>
                            Settings
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="complain.php">
                        <div>
                            <span class="material-icons icon">comment</span>
                            Complain
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="help.php">
                        <div>
                            <span class="material-icons icon">support_agent</span>
                            Support
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div>
                            <span class="material-icons icon">info</span>
                            About Us
                        </div>
                        <span class="arrow material-icons">chevron_right</span>
                    </a>
                </li>
                               
            </ul>
            <div class="text-center">
                <a href="logout.php" class="btn btn-outline-danger text-white rounded my-3 mx-auto">Sign out</a>
            </div>
        </div>
    </div>
    <div class="backdrop"></div>
    

    <!-- Begin page content -->
    <main class="flex-shrink-0 main has-footer">
        <!-- Fixed navbar -->
        <header class="header">
            <div class="row">
                <div class="col-auto px-0">
                    <button class="menu-btn btn btn-40 btn-link" type="button">
                        <span class="material-icons">menu</span>
                    </button>
                </div>
                <div class="text-left col align-self-center">
                    <a class="navbar-brand" href="home.php">
                        <h5 class="mb-0">Aanonda</h5>
                    </a>
                </div>
                <div class="ml-auto col-auto pl-0">
                    <button type="button" class="btn btn-link btn-40 colorsettings">
                        <span class="material-icons">color_lens</span>
                    </button>

                    <a href="notification.php" class="btn btn-40 btn-link">
                        <span class="material-icons">notifications_none</span>

                        <?php
                        if($notifica_count > 0)
                        {                        
                        ?>
                        <span class="counter"></span>
                        <?php
                            }                                
                        ?>
                    </a>
                    <a href="profile.php" class="avatar avatar-30 shadow-sm rounded-circle ml-2">
                        <figure class="m-0 background">
                            <img src="../img/user3.png" alt="">
                        </figure>
                    </a>
                </div>
            </div>
        </header>

        <!-- Header End -->
