<?php 
include "login.check.php";
include "header.php";
?>
    
            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <div class="row justify-content-center">
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="error-text-box">
                                    <svg viewBox="0 0 600 200">
                                        <!-- Symbol-->
                                       
                                        <!-- Duplicate symbols-->
                                        <use class="text" xlink:href="#s-text"></use>
                                        <use class="text" xlink:href="#s-text"></use>
                                        <use class="text" xlink:href="#s-text"></use>
                                        <use class="text" xlink:href="#s-text"></use>
                                        <use class="text" xlink:href="#s-text"></use>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    


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

                                    <a href="home.php" class="btn btn-success waves-effect waves-light">Back to Dashboard</a>
                                </div>
                                <!-- end row -->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>
    