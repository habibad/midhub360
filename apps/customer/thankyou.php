<?php
    include "login.check.php";
    include "header.php";   
?>

<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
					<a href="mbank_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">history</span> </div>
                    <p class="small text-white">M. Bank. Hist.</p>
					</a>
                </div>
                <div class="col-auto">
					<a href="send_recharge.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">phone_iphone</span> </div>
                    <p class="small text-white">Send Recharge</p>
					</a>
                </div>
                <div class="col-auto">
					<a href="help.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">support_agent</span> </div>
                    <p class="small text-white">Contact</p>
					</a>
                </div>                
            </div>
        </div>

        <!-- page content start -->
        <div class="main-container h-100">
            <div class="container h-100">
                <div class="row h-100">
                    <div class="col-12 col-md-6 col-lg-4 align-self-center text-center my-3 mx-auto">
                        <div class="icon icon-120 bg-success-light text-success rounded-circle mb-3">
                            <i class="material-icons display-4">redeem</i>
                        </div>
                        <h2>Congratulation!</h2>
                        <h6 class="text-secondary mb-3">Your order has been placed </h6>
                        <p class="text-secondary">Thank you for buying products your order will be delivered soon and we will notify you soon via your registered email address or phone number.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    
    <?php 
include "footer.php"; 
?>