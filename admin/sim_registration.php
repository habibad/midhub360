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
                                            <li class="breadcrumb-item active">Sim Registration</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Sim Registration</h4>
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

                                    <form action="route.php?action=SimRegi" method="post" accept-charset="utf-8">

                                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">General</h5>

                                       
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Username<span class="text-danger">*</span></label>
                                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter Username">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">User ID<span class="text-danger">*</span></label>
                                            <input type="number" name="userid" id="userid" class="form-control" placeholder="Enter User ID">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" id="mobile" class="form-control" id="product-price" placeholder="+8801712345678">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Pin Code <span class="text-danger">*</span></label>
                                            <input type="number" name="pincode" id="pincode" class="form-control" id="product-price" placeholder="Enter Pin Code">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                            <select name="level" class="form-control select2" id="level">
                                                    <option value="0">Retailer</option>
                                                    <option value="1">Dealer</option>
                                                    <option value="2">DGM</option>
                                                    <option value="3">Distributor House</option> 
                                                    <option value="4">Subadmin</option>                                               
                                           
                                            </select>
                                        </div>



                                        <div class="mb-3">
                                            <label class="mb-2">Status <span class="text-danger">*</span></label>
                                            <br/>
                                            <div class="radio form-check-inline">
                                                <input type="radio" name="active" id="active1" value="1" checked="">
                                                <label for="inlineRadio1"> Active </label>
                                            </div>
                                            <div class="radio form-check-inline">
                                                <input type="radio" name="active" id="active1" value="0">
                                                <label for="inlineRadio2"> Inactive </label>
                                            </div>
                                            
                                        </div>

                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Registration</button>                                                    
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