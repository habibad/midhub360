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
                                            <li class="breadcrumb-item active">Add New Package</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add New Package</h4>
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

                                    <form action="route.php?action=PackageAdd" method="post" accept-charset="utf-8">

                                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">General</h5>


                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Select Operator<span class="text-danger">*</span></label>
                                            <select name="operator" class="form-control select2" id="operator">                                                   
                                                    <option value="1">Grameenphone</option>
                                                    <option value="2">Robi</option>
                                                    <option value="3">Banglalink</option> 
                                                    <option value="4">Airtel</option>
                                                    <option value="5">Teletalk</option>                                               
                                           
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Pacakge Type<span class="text-danger">*</span></label>
                                            <select name="pkg_type" class="form-control select2" id="pkg_type">                                                   
                                                    <option value="1">Internet</option>
                                                    <option value="2">Minute</option>                                                                                                
                                            </select>
                                        </div>


                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Package Name<span class="text-danger">*</span></label>
                                            <input type="text" name="pkgname" id="pkgname" class="form-control" placeholder="Enter Package Name">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Commission <span class="text-danger">*</span></label>
                                            <input type="number" name="commission" id="commission" class="form-control" id="product-price" placeholder="Enter Commission">
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
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Add New Package</button>                                                    
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