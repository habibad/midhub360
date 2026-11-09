<?php 
include "login.check.php";
include "header.php";

$sim_id   = Xss::xssClean(Input::get('sim_id'));


$query = "SELECT * FROM veri_number where id='$sim_id'";        
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
}        

$data      = mysqli_fetch_object($result); 

$username         = $data->user_name;
$customerid       = $data->user_id;
$mobnumber        = $data->user_mobile_no;
$cspin            = $data->pin;
$status           = $data->active;

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
                                            <li class="breadcrumb-item active">Sim Edit</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Sim Edit</h4>
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

                                    <form action="route.php?action=SimUpdate" method="post" accept-charset="utf-8">

                                        <input type="hidden" name="simid" id="simid" value="<?php echo $sim_id;?>">
                                                     
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Username<span class="text-danger">*</span></label>
                                            <input type="text" name="username" id="username" class="form-control" value="<?php echo $username;?>" placeholder="Enter Username">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">User ID<span class="text-danger">*</span></label>
                                            <input type="number" name="userid" id="userid" class="form-control" value="<?php echo $customerid;?>" placeholder="Enter User ID">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" id="mobile" class="form-control" id="product-price" value="<?php echo $mobnumber;?>" placeholder="+8801712345678">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Pin Code <span class="text-danger">*</span></label>
                                            <input type="number" name="pincode" id="pincode" class="form-control" id="product-price" value="<?php echo $cspin;?>" placeholder="Enter Pin Code">
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

                                                  
                                        <?php                                  
                                            if ($status == 1 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?> 

                                          <?php                                  
                                            if ($status == 0 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?>                           

                                       
                                       
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