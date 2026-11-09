<?php
    include "login.check.php";
    include "header.php";     
?>

<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
					<a href="recharge_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">history</span> </div>
                    <p class="small text-white">Package History</p>
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
                        
                        <?php if (Session::exists('error')){
						?>
                         <div class="icon icon-120 bg-success-light text-success rounded-circle mb-3">
                            <i class="material-icons display-4 big-icon-color">warning</i>
                        </div>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
						  <strong></strong> <?=Session::Flush('error');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button><br>

                    <div>
                            <a href="home.php" class="btn btn-info" role="button">Back</a>                   
                    </div>

						</div>
						<?php
				  }?>
				<?php if (Session::exists('success')){
					?>
                       <div class="icon icon-120 bg-success-light text-success rounded-circle mb-3">
                            <i class="material-icons display-4">check</i>
                        </div>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
						  <strong>Well Done!</strong> <?=Session::Flush('success');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						</div>

                        <div>
                            <a href="home.php" class="btn btn-info" role="button">Back</a>                   
                    </div>
						<?php
				}?>
                    </div>
                </div>

               

            </div>

            

        </div>



    </main>

    <?php include "footer.php";?>