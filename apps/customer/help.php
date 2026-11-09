<?php 
include "login.check.php";
include "header.php"; 
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


        <div class="main-container">
            <div class="container">       
                <div class="card border-0 mb-3">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-auto pr-0">
                                <div class="avatar avatar-40 border-0 bg-danger-light rounded-circle text-danger">
                                    <i class="material-icons vm text-template">support_agent</i>
                                </div>
                            </div>
                            <div class="col align-self-center pr-0">
                                <h6 class="mb-0">Help Line</h6>
                                <p class="text-secondary">24/7 Support</p>
                            </div>                           
                        </div>
                    </div>					
					
                    <div class="card-body">
                        <h2 class="text-center"><?php echo $company_phone;?></h2>
                    </div>
                    <div class="card-footer">
                        
                    </div>
                </div>    

            </div>

            <br>

            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Help Desk</h6>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="list-group list-group-flush border-top border-color">
                            <a href="complain.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">comment</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Complain of Transaction</h6>                                       
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">description</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Documantation</h6>                                        
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">smart_display</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Video Tutorial</h6>                                       
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">info</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">About Us</h6>                                       
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
