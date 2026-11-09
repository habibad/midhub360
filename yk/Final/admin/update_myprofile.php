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
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>                                           
                                            <li class="breadcrumb-item active">Update My Information</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Update My Information</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        

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


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">

                                    <form action="route.php?action=UpdateMyProfile" method="post" accept-charset="utf-8">

                                       

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Full Name<span class="text-danger">*</span></label>
                                            <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo $fullname;?>" placeholder="Enter Your Full Name">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Email Address<span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $email_address;?>" placeholder="Enter Email Address">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number<span class="text-danger">*</span></label>
                                            <input type="text" name="phonenumber" id="phonenumber" class="form-control" value="<?php echo $phone_number;?>" placeholder="+8801712345678">
                                        </div>


                                        <div class="mb-3">
                                            <label for="product-price">Pincode<span class="text-danger">*</span></label>
                                            <input type="password" name="selfpin" id="selfpin" class="form-control" placeholder="Enter your pincode">
                                        </div>


                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                        </div> 

                        <br>

                        <!-- end row -->


                        <!-- file preview template -->
                        <div class="d-none" id="uploadPreviewTemplate">
                            <div class="card mt-1 mb-0 shadow-none border">
                                <div class="p-2">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                        </div>
                                        <div class="col ps-0">
                                            <a href="javascript:void(0);" class="text-muted fw-bold" data-dz-name></a>
                                            <p class="mb-0" data-dz-size></p>
                                        </div>
                                        <div class="col-auto">
                                            <!-- Button -->
                                            <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                                <i class="dripicons-cross"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>