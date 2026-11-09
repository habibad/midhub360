<?php 
include "login.check.php";
include "header.php";
?>



    <?php 
    
                $query = "SELECT * FROM settings where id='1'";        
                $result = mysqli_query($connect, $query);

                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));      
                }        
                
                $reseller_data      = mysqli_fetch_object($result); 

                $recharge_low       = $reseller_data->recharge_low;
                $mbank_low          = $reseller_data->mbank_low;
                $pin_expire         = $reseller_data->pin_expire;
                $pass_expire        = $reseller_data->pass_expire;
                $recharge_min       = $reseller_data->recharge_min;
                $recharge_max       = $reseller_data->recharge_max;
                $mbank_min          = $reseller_data->mbank_min;
                $mbank_max          = $reseller_data->mbank_max;
               
               
    
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
                                            <li class="breadcrumb-item active">Settings</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Settings</h4>
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
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">

                                    <form action="route.php?action=UpdateSettings" method="post" accept-charset="utf-8">

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Recharge Low Balance Limit<span class="text-danger">*</span></label>
                                            <input type="number" name="relimit" id="relimit" class="form-control" value="<?php echo $recharge_low;?>" placeholder="Enter Username">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">M. Bank Low Balance Limit<span class="text-danger">*</span></label>
                                            <input type="number" name="mlimit" id="mlimit" class="form-control" value="<?php echo $mbank_low;?>" placeholder="Enter Password">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Pin Expire (Days) <span class="text-danger">*</span></label>
                                            <input type="number" name="pinexpire" id="pinexpire" class="form-control" value="<?php echo $pin_expire;?>" placeholder="Enter Pincode">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Password Expire (Days) <span class="text-danger">*</span></label>
                                            <input type="number" name="passexpire" id="passexpire" class="form-control" value="<?php echo $pass_expire;?>" placeholder="Enter Fullname">
                                        </div>
                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">                              
                               
                                <div class="card">
                                    <div class="card-body">                                       
                                      
                                        <div class="mb-3">
                                            <label for="product-price">Recharge Minimum Amount<span class="text-danger">*</span></label>
                                            <input type="number" name="remin" id="remin" class="form-control" id="product-price" value="<?php echo $recharge_min;?>" placeholder="Enter Customer Limit Balance">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Recharge Maximum Amount<span class="text-danger">*</span></label>
                                            <input type="number" name="remax" id="remax" class="form-control" id="product-price" value="<?php echo $recharge_max;?>" placeholder="Enter Customer Limit Balance">
                                        </div>


                                        <div class="mb-3">
                                            <label for="product-price">M. Bank Minimum Amount<span class="text-danger">*</span></label>
                                            <input type="number" name="mmin" id="mmin" class="form-control" id="product-price" value="<?php echo $mbank_min;?>" placeholder="Enter Customer Limit Balance">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">M. Bank Maximum Amount<span class="text-danger">*</span></label>
                                            <input type="number" name="mmax" id="mmax" class="form-control" id="product-price" value="<?php echo $mbank_max;?>" placeholder="Enter Customer Limit Balance">
                                        </div> 
                                        
                                        <div class="form-check mb-2 form-check-primary">                                           
                                            <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                            <label class="form-check-label" for="customckeck1">Country Block</label>                                          
                                        </div>

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
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