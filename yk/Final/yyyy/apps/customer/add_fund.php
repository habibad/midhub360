<?php 
include "login.check.php";
include "header.php"; 

$status = Addfund::AddbalanceStatus();

?>

        <!-- page content start -->
        <!--
        <div class="container-fluid px-0">
            <div class="card overflow-hidden">
                <div class="card-body p-0 h-150">
                    <div class="background">
                        <img src="../img/image10.jpg" alt="" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid top-70 text-center mb-4">
            <div class="avatar avatar-140 rounded-circle mx-auto shadow">
                <div class="background">
                    <img src="../img/user1.png" alt="">
                </div>
            </div>
        </div>
        <div class="container mb-4 text-center text-white">
            <h6 class="mb-1">Errica Johnson</h6>
            <p>London, UK</p>
        </div>
        -->


        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="add_fund_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">search</span> </div>
                    <p class="small text-white">Check Status</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="payment_received.php">
                        <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">payments</span> </div>
                        <p class="small text-white">Payment Hist.</p>
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

        <div class="main-container">  

        <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>

					<span class="title-text">Add Balance to Your Account</span>
            </div>    

            <div class="container">            
                <br>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Choose Method</h6>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="list-group list-group-flush border-top border-color">
                        <?php                                  
                            if ($status == 1 )
                            {
                        ?>
                            <a href="auto_add_balance.php" class="list-group-item list-group-item-action border-color">
                            
                            <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">send_to_mobile</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Mobile Banking</h6>                                       
                                    </div>
                                </div>
                            </a>

                            <?php
                            }                                
                            ?>

                        <?php                                  
                            if ($status == 2 )
                            {
                        ?>
                            <a href="add_fund_mbank.php" class="list-group-item list-group-item-action border-color">
                            
                            <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">send_to_mobile</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Mobile Banking</h6>                                       
                                    </div>
                                </div>
                            </a>

                            <?php
                            }                                
                            ?>

                            <a href="add_fund_bank.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">account_balance</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Bank Deposit/Transfer</h6>                                        
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">credit_card</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Online Payment</h6>                                       
                                    </div>
                                </div>
                            </a>

                            <a href="#" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">search</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Check Status</h6>                                       
                                    </div>
                                </div>
                            </a>
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php 
    include "footer.php"; 
?>
