<?php 
include "login.check.php";
include "header.php";

$subadmin_comm = Mixedfn::findcommission("1","subadmin");
$level4_comm   = Mixedfn::findcommission("1","level4");
$level3_comm   = Mixedfn::findcommission("1","level3");
$level2_comm   = Mixedfn::findcommission("1","level2");
$level1_comm   = Mixedfn::findcommission("1","level1");

$subadmin_upcomm = Mixedfn::findcommission("2","subadmin");
$level4_upcomm   = Mixedfn::findcommission("2","level4");
$level3_upcomm   = Mixedfn::findcommission("2","level3");
$level2_upcomm   = Mixedfn::findcommission("2","level2");
$level1_upcomm   = Mixedfn::findcommission("2","level1");

$subadmin_mbcomm = Mixedfn::findcommission("3","subadmin");
$level4_mbcomm   = Mixedfn::findcommission("3","level4");
$level3_mbcomm   = Mixedfn::findcommission("3","level3");
$level2_mbcomm   = Mixedfn::findcommission("3","level2");
$level1_mbcomm   = Mixedfn::findcommission("3","level1");

$subadmin_mbupcomm = Mixedfn::findcommission("4","subadmin");
$level4_mbupcomm   = Mixedfn::findcommission("4","level4");
$level3_mbupcomm   = Mixedfn::findcommission("4","level3");
$level2_mbupcomm   = Mixedfn::findcommission("4","level2");
$level1_mbupcomm   = Mixedfn::findcommission("4","level1");


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
                                            <li class="breadcrumb-item active">Commission Set</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Commission Set</h4>
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

                        <h4 class="page-title">Recharge Balance Commission Set</h4>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">

                                     <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Direct Commission</h5>

                                        <h4><span class="text-danger">রিচার্জ ব্যালেন্সে কোন লেভেল ১০০ টাকাতে কত কমিশন পাবে তা এখানে সেট করবেন।</span></h4> <br>

                                    <form action="route.php?action=commissionup" method="post" accept-charset="utf-8">
                                       
                                        <input type="hidden" name="comid" id="comid" value="1">

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Sub Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="l5com" id="l5com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $subadmin_comm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Distributor House<span class="text-danger">*</span></label>
                                            <input type="text" name="l4com" id="l4com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level4_comm;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">DGM<span class="text-danger">*</span></label>
                                            <input type="text" name="l3com" id="l3com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level3_comm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Dealer<span class="text-danger">*</span></label>
                                            <input type="text" name="l2com" id="l2com" class="form-control" value="<?php echo $level2_comm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Retailer<span class="text-danger">*</span></label>
                                            <input type="text" name="l1com" id="l1com" class="form-control" value="<?php echo $level1_comm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                                        </div> 

                                        </form>

                  
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">
                                
                               

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Upper Level Commission</h5>
                                        <h4><span class="text-danger">নিচের লেভেলকে রিচার্জ ব্যালেন্স দিলে তার উপরের লেভেল গুলো ১০০ টাকাতে কত কমিশন পাবে তা এখানে সেট করবেন।</span></h4>
                                        <br>

                                        <form action="route.php?action=commissionup" method="post" accept-charset="utf-8">
                                            <input type="hidden" name="comid" id="comid" value="2">
                                       
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Dealer<span class="text-danger">*</span></label>
                                            <input type="text" name="l2com" id="l2com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level2_upcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">DGM<span class="text-danger">*</span></label>
                                            <input type="text" name="l3com" id="l3com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level3_upcomm;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Distributor House<span class="text-danger">*</span></label>
                                            <input type="text" name="l4com" id="l4com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level4_upcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Sub Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="l5com" id="l5com" class="form-control" value="<?php echo $subadmin_upcomm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                                        </div> 

                                        </form>

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                        <br>

                       
                            <h4 class="page-title">M. Bank Balance Commission Set</h4>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">

                                   
                                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Direct Commission</h5>

                                        <h4><span class="text-danger">মোবাইল ব্যাংকিং ব্যালেন্সে কোন লেভেল ১০০ টাকাতে কত কমিশন পাবে তা এখানে সেট করবেন।</span></h4> <br>

                                    <form action="route.php?action=commissionup" method="post" accept-charset="utf-8">
                                        <input type="hidden" name="comid" id="comid" value="3">                                       

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Sub Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="l5com" id="l5com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $subadmin_mbcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Distributor House<span class="text-danger">*</span></label>
                                            <input type="text" name="l4com" id="l4com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level4_mbcomm;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">DGM<span class="text-danger">*</span></label>
                                            <input type="text" name="l3com" id="l3com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level3_mbcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Dealer<span class="text-danger">*</span></label>
                                            <input type="text" name="l2com" id="l2com" class="form-control" value="<?php echo $level2_mbcomm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Retailer<span class="text-danger">*</span></label>
                                            <input type="text" name="l1com" id="l1com" class="form-control" value="<?php echo $level1_mbcomm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                                        </div> 

                                        </form>

                  
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">
                                
                               

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Upper Level Commission</h5>
                                        <h4><span class="text-danger">নিচের লেভেলকে মোবাইল ব্যাংকিং ব্যালেন্স দিলে তার উপরের লেভেল গুলো ১০০ টাকাতে কত কমিশন পাবে তা এখানে সেট করবেন।</span></h4>
                                        <br>

                                    <form action="route.php?action=commissionup" method="post" accept-charset="utf-8">
                                        <input type="hidden" name="comid" id="comid" value="4">
                                       
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Dealer<span class="text-danger">*</span></label>
                                            <input type="text" name="l2com" id="l2com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level2_mbupcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">DGM<span class="text-danger">*</span></label>
                                            <input type="text" name="l3com" id="l3com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level3_mbupcomm;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Distributor House<span class="text-danger">*</span></label>
                                            <input type="text" name="l4com" id="l4com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4" value="<?php echo $level4_mbupcomm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Sub Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="l5com" id="l5com" class="form-control" value="<?php echo $subadmin_mbupcomm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "4">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                                        </div> 

                                        </form>

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                       

                       
                        <br>

                        <!-- end row -->


                       
                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>