<?php 
include "login.check.php";
include "header.php";

$notice_result = Notice::ReadNotice("2");
$row = mysqli_fetch_object($notice_result);
$FullNotice   = $row->FullNotice;


$billpay_row = Mixedfn::ac_balance("balance",$us_id);
$billpay_balance = $billpay_row->balance;

$recharge_row = Mixedfn::ac_balance("balance2",$us_id);
$recharge_balance = $recharge_row->balance2;

$mbank_row = Mixedfn::ac_balance("bkash_balance",$us_id);
$mbank_balance = $mbank_row->bkash_balance;

?>

<!--

<div class="container mt-3 mb-4 text-center">
            <h2 class="text-white">Tk. 1,050.00</h2>
            <p class="text-white mb-4">Total Balance</p>
        </div>
-->
        <div class="main-container">

        <div class="container mb-4">
        <div class="notice-home">
            <marquee class="text-info" behavior = 'scroll' direction = 'left' onmouseout=this.start(); onmouseover=this.stop(); scrollamount='2' scrolldelay='40' truespeed='truespeed'>
                <?php echo $FullNotice; ?>
            </marquee>
        </div>
        </div>

        <div class="container mb-4">
            <div class="balance">               
                <div class="col">
                        <h6 class="subtitle mb-3">Balance (BDT)</h6>
                    </div>
                <div class="row align-items-center">
                        <div class="col-auto pr-0">
                            <!-- -->
                            <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                <i class="material-icons vm text-template">account_balance_wallet</i>
                            </div> 
                                        
                        </div>
                        <div class="col-4 align-self-center">
                            <p class="mb-1">Recharge</p>                                    
                            <h6 class="mb-1"><?php echo $recharge_balance;?></h6>
                        </div>
                        <div class="col-auto align-self-center border-left">
                            <p class="mb-1">Banking</p>
                                <h6 class="mb-1"><?php echo $mbank_balance;?></h6>
                        </div>
                        <!--
                        <div class="col-auto align-self-center border-left">
                            <p class="mb-1">Billpay</p>
                                <h6 class="mb-1"><?php //echo $billpay_balance;?></h6>
                        </div>
                        -->
                </div>               

            </div>
        </div>

       
            <!-- page content start -->

            <div class="container mb-4 text-center">
                <div class="card bg-default-secondary shadow-default">
                    <div class="card-body">
                        <!-- Swiper -->
                        <div class="swiper-container addsendcarousel text-center">
                            <div class="swiper-wrapper mb-4">
                                <a href="send_recharge.php" class="swiper-slide text-white">
                                <img src="../img/icon/recharge.png" class="home-icon" alt="">
                                    <p><small>Recharge</small></p>
                                </a>
                                <a href="recharge_history.php" class="swiper-slide text-white">
                                    <div class="icon icon-50 rounded-circle mb-2 bg-white-light"><span class="material-icons">history</span></div>
                                    <p><small>History</small></p>
                                </a>
                                <?php 
                                 
                                 if ($level != 0 ){
                                    ?>
                                    <a href="customer_list.php" class="swiper-slide text-white">
                                    <div class="icon icon-50 rounded-circle mb-2 bg-white-light"><span class="material-icons">groups</span></div>
                                    <p><small>Customer List</small></p>
                                </a>
                                    <?php
                                 }
                                
                                ?>
                                
                                <a href="add_fund.php" class="swiper-slide text-white">
                                    <div class="icon icon-50 rounded-circle mb-2 bg-white-light"><span class="material-icons">add</span></div>
                                    <p><small>Add Balance</small></p>
                                </a>
                                <a href="#" class="swiper-slide text-white">
                                    <div class="icon icon-50 rounded-circle mb-2 bg-white-light"><span class="material-icons">card_giftcard</span></div>
                                    <p><small>Drive</small></p>
                                </a>
                                <a href="#" class="swiper-slide text-white">
                                    <div class="icon icon-50 rounded-circle mb-2 bg-white-light"><span class="material-icons">card_giftcard</span></div>
                                    <p><small>Package</small></p>
                                </a>
                            </div>
                            <!-- Add Pagination -->
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>

            
            <!-- Swiper Start-->
             <!-- Swiper 
            <div class="container mb-4">
                <div class="swiper-container swiper-users text-center ">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="card ">
                                <div class="card-body p-2">
                                    <a href="send_money.html" class="avatar avatar-60 rounded mb-1 bg-default-light">
                                        <span class="material-icons">add</span>
                                    </a>
                                    <p class="text-secondary"><small>Send</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user1.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Errica</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user2.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Alisia</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user3.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Maxsmith</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user4.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Jenelia</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user1.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Errica</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user1.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Errica</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user2.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Alisia</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user3.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Maxsmith</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user4.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Jenelia</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="avatar avatar-60 rounded mb-1">
                                        <div class="background"><img src="img/user1.png" alt=""></div>
                                    </div>
                                    <p class="text-secondary"><small>Errica</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
             <!-- Swiper End-->
             <!-- Swiper End-->

            <div class="container mb-4">
                <div class="card">
                    <div class="card-body text-center ">
                        <div class="row justify-content-equal no-gutters">
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#" data-toggle="modal" data-target="#drivemodal">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">app_settings_alt</span></div>
                                <p class="text-secondary"><small>Drive</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#" data-toggle="modal" data-target="#packagemodal">
                            <img src="../img/icon/package.png" class="home-icon" alt="">                                 
                                <p class="text-secondary"><small>Package</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_bkash.php">                                
                                <img src="../img/icon/bKash.png" class="home-icon" alt="">                                
                                <p class="text-secondary"><small>bKash</small></p>                                
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_rocket.php">                                
                                <img src="../img/icon/rocket.png" class="home-icon" alt="">
                                <p class="text-secondary"><small>Rocket</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_nagad.php">
                            <img src="../img/icon/nagad.png" class="home-icon" alt="">                                
                                <p class="text-secondary"><small>Nagad</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#" data-toggle="modal" data-target="#exampleModal">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">wb_incandescent</span></div>
                                <p class="text-secondary"><small>Bill Pay</small></p>
                            </a>
                            </div>

                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_mnp.php">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">phone_iphone</span></div>
                                <p class="text-secondary"><small>MNP</small></p>
                            </a>
                            </div>

                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_upay.php">
                            <img src="../img/icon/upay.png" class="home-icon" alt="">
                                <p class="text-secondary"><small>Upay</small></p>
                            </a>
                            </div>

                            <div class="col-4 col-md-2 mb-3">
                            <a href="send_bank.php">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">account_balance</span></div>
                                <p class="text-secondary"><small>Banking</small></p>
                            </a>
                            </div>
                            
                        </div>
                        <button class="btn btn-sm btn-outline-secondary rounded" id="more-expand-btn">Show more <span class="ml-2 small material-icons">expand_more</span></button>
                        <div class="row justify-content-equal no-gutters" id="more-expand">
                            <div class="col-4 col-md-2">
                            <a href="#">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">language</span></div>
                                <p class="text-secondary"><small>Global</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2">
                            <a href="#">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">credit_card</span></div>
                                <p class="text-secondary"><small>Buy Card</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2">
                            <a href="#">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">textsms</span></div>
                                <p class="text-secondary"><small>SMS</small></p>
                            </a>    
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container mb-4">
                <div class="row">
                    <div class="col">
                        <h6 class="subtitle mb-3">Today's Sales</h6>
                    </div>
                    <div class="col-auto"><a href="#" class="text-default">View all</a></div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card border-0 mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">Recharge</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6>                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">Others</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container mb-4">
                <div class="row">
                    <div class="col">
                        <h6 class="subtitle mb-3">Last Received Payment</h6>
                    </div>
                    <div class="col-auto"><a href="#" class="text-default">View all</a></div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card border-0 mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">Recharge</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6> 
                                        <p class="small text-secondary">By: Admin</p>                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">M. Banking</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6>
                                        <p class="small text-secondary">By: Admin</p>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container mb-4">
                <div class="row">
                    <div class="col">
                        <h6 class="subtitle mb-3">Last Given Payment</h6>
                    </div>
                    <div class="col-auto"><a href="#" class="text-default">View all</a></div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card border-0 mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">Recharge</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6> 
                                        <p class="small text-secondary">By: Admin</p>                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto pr-0">
                                        <div class="avatar avatar-50 border-0 bg-default-light rounded-circle text-default">
                                            <i class="material-icons vm text-template">local_atm</i>
                                        </div>
                                    </div>
                                    <div class="col-4 align-self-center">
                                        <p class="mb-1">M. Banking</p>                                       
                                    </div>
                                    <div class="col-auto align-self-center border-left">
                                        <h6 class="mb-1">৳ N/A</h6>
                                        <p class="small text-secondary">By: Admin</p>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </main>

   

<!-- Bill Pay Modals  Start-->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Choose Service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">                   
                    <div class="container mb-4">
                <div class="card">
                    <div class="card-body text-center ">
                        <div class="row justify-content-equal no-gutters">
                            <div class="col-4 col-md-2 mb-3">
                            <a href="bill_electricity.php">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">wb_incandescent</span></div>
                                <p class="text-secondary"><small>Electricity</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="bill_gas.php">
                            <img src="../img/icon/gas.png" class="home-icon" alt="">                                 
                                <p class="text-secondary"><small>Gas</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="bill_water.php">                                
                                <img src="../img/icon/watertap.png" class="home-icon" alt="">                                
                                <p class="text-secondary"><small>Water</small></p>                                
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#">                                
                            <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">language</span></div>
                                <p class="text-secondary"><small>Internet</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#">
                            <img src="../img/icon/telephone.png" class="home-icon" alt="">                                
                                <p class="text-secondary"><small>Telephone</small></p>
                            </a>
                            </div>
                            <div class="col-4 col-md-2 mb-3">
                            <a href="#">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">tv</span></div>
                                <p class="text-secondary"><small>TV</small></p>
                            </a>
                            </div>

                            <div class="col-4 col-md-2 mb-3">
                            <a href="#">
                                <div class="icon icon-50 rounded-circle mb-1 bg-default-light text-default"><span class="material-icons">menu_book</span></div>
                                <p class="text-secondary"><small>Education</small></p>
                            </a>
                            </div>

                            <div class="col-4 col-md-2 mb-3">
                            <a href="#">
                            <img src="../img/icon/bdflag.png" class="home-icon" alt="">
                                <p class="text-secondary"><small>Govt. Fees</small></p>
                            </a>
                            </div>
                                                        
                        </div>
                       
                    </div>
                </div>
            </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                   
                </div>

            </div>
        </div>
    </div>


<!-- Bill Pay Modals End-->

 
<!-- Drive Modals  Start-->
<div class="modal fade" id="drivemodal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="drivemodal">Choose Operator</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">                   
                    <div class="container mb-4">
                <div class="card">
                    <div class="card-body text-center ">
                        <div class="row justify-content-equal no-gutters">
                        <?php foreach(Package::oparetor() as $op):?>
                            <div class="col-4 col-md-2">                           
                                    <div class="card mb-4">
                                        <a href="drive.php?op_id=<?php echo $op->id ?>&op_name=<?php echo $op->operator_name ?>">
                                          <img src="../img/operators/<?php echo $op->operator_name; ?>.png" class="home-icon for-space-drive">
                                          <p class="text-secondary"><small><?php echo $op->operator_name; ?></small></p>
                                        </a>                                         
                                    </div>                                    
                            </div>

                            <?php endforeach; ?>
                                                        
                        </div>
                       
                    </div>
                </div>
            </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                   
                </div>

            </div>
        </div>
    </div>


<!-- Drive Modals End-->



<!-- Package Modals  Start-->
<div class="modal fade" id="packagemodal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packagemodal">Internet Package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">                   
                    <div class="container mb-4">
                <div class="card">
                    <div class="card-body text-center ">
                        <div class="row justify-content-equal no-gutters">
                        <?php foreach(Package::oparetor() as $op):?>
                            <div class="col-4 col-md-2">                           
                                    <div class="card mb-4">
                                        <a href="package.php?op_id=<?php echo $op->id ?>&op_name=<?php echo $op->operator_name ?>">
                                          <img src="../img/operators/<?php echo $op->operator_name; ?>.png" class="home-icon for-space-drive">
                                          <p class="text-secondary"><small><?php echo $op->operator_name; ?></small></p>
                                        </a>                                         
                                    </div>                                    
                            </div>

                            <?php endforeach; ?>
                                                        
                        </div>
                       
                    </div>
                </div>

                <br>
                <h5 class="modal-title" id="packagemodal">Minute Package</h5>    
                <div class="card">
                    <div class="card-body text-center ">
                        <div class="row justify-content-equal no-gutters">
                        <?php foreach(Package::oparetor() as $op):?>
                            <div class="col-4 col-md-2">                           
                                    <div class="card mb-4">
                                        <a href="package_minute.php?op_id=<?php echo $op->id ?>&op_name=<?php echo $op->operator_name ?>">
                                          <img src="../img/operators/<?php echo $op->operator_name; ?>.png" class="home-icon for-space-drive">
                                          <p class="text-secondary"><small><?php echo $op->operator_name; ?></small></p>
                                        </a>                                         
                                    </div>                                    
                            </div>

                            <?php endforeach; ?>
                                                        
                        </div>
                       
                    </div>
                </div>


                
            </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                   
                </div>

            </div>
        </div>
    </div>


<!-- Package Modals End-->


 
    
<?php 
include "footer.php"; 
?>