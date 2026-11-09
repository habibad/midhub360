<?php 
include "login.check.php";
include "header.php";


$query = "SELECT * FROM payment_account where id='1'";        
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
}        

$reseller_data      = mysqli_fetch_object($result); 

$bkash_agent        = $reseller_data->bkash_agent;
$bkash_personal     = $reseller_data->bkash_personal;
$rocket_agent       = $reseller_data->rocket_agent;
$rocket_personal    = $reseller_data->rocket_personal;
$nagad_agent        = $reseller_data->nagad_agent;
$nagad_personal     = $reseller_data->nagad_personal;
$bank_ac            = $reseller_data->bank_ac;

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
                                            <li class="breadcrumb-item active">Add Payemnt Account Information</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Payemnt Account Information</h4>
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

                                    <form action="route.php?action=UpPaymentInfo" method="post" accept-charset="utf-8">

                                      
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">bKash Agent Number<span class="text-danger">*</span></label>
                                            <input type="text" name="bkagent" id="bkagent" class="form-control" value="<?php echo $bkash_agent;?>" placeholder="Enter bKash Agent Number">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Rocket Agent Number<span class="text-danger">*</span></label>
                                            <input type="text" name="rcagent" id="rcagent" class="form-control" value="<?php echo $rocket_agent;?>" placeholder="Enter Rocket Agent Number">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Nagad Agent Number<span class="text-danger">*</span></label>
                                            <input type="text" name="ngagent" id="ngagent" class="form-control" value="<?php echo $nagad_agent;?>" placeholder="Enter Nagad Agent Number">
                                        </div>


                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">bKash Personal Number<span class="text-danger">*</span></label>
                                            <input type="text" name="bkpersonal" id="bkpersonal" class="form-control" value="<?php echo $bkash_personal;?>" placeholder="Enter bKash Agent Number">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Rocket Personal Number<span class="text-danger">*</span></label>
                                            <input type="text" name="rcpersonal" id="rcpersonal" class="form-control" value="<?php echo $rocket_personal;?>" placeholder="Enter Rocket Agent Number">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Nagad Personal Number<span class="text-danger">*</span></label>
                                            <input type="text" name="ngpersonal" id="ngpersonal" class="form-control" value="<?php echo $nagad_personal;?>" placeholder="Enter Nagad Agent Number">
                                        </div>

                                       
                                        <div class="mb-3">
                                            <label for="example-textarea" class="form-label">Bank Account</label>
                                            <textarea name="bankac" class="form-control" id="bankac" rows="5"><?php echo $bank_ac;?></textarea>
                                        </div>
                                       

                                       

                                       

                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                        </div> 

                    </form>

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