<?php 
include "login.check.php";
include "header.php";

$subadmin_comm = Mixedfn::findacfee("1","subadmin");
$level4_comm   = Mixedfn::findacfee("1","level4");
$level3_comm   = Mixedfn::findacfee("1","level3");
$level2_comm   = Mixedfn::findacfee("1","level2");
$level1_comm   = Mixedfn::findacfee("1","level1");


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
                                            <li class="breadcrumb-item active">Set Account Fee</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Set Account Fee</h4>
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

                                     <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Account Fee</h5>

                                        <h4><span class="text-danger">কোন লেভেল এর একাউন্ট ফি কত তা এখানে সেট করবেন।</span></h4> <br>

                                    <form action="route.php?action=acfeeup" method="post" accept-charset="utf-8">
                                       
                                        <input type="hidden" name="comid" id="comid" value="1">

                                       
                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Distributor House<span class="text-danger">*</span></label>
                                            <input type="text" name="l4com" id="l4com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "10" value="<?php echo $level4_comm;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">DGM<span class="text-danger">*</span></label>
                                            <input type="text" name="l3com" id="l3com" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "10" value="<?php echo $level3_comm;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Dealer<span class="text-danger">*</span></label>
                                            <input type="text" name="l2com" id="l2com" class="form-control" value="<?php echo $level2_comm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "10">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Retailer<span class="text-danger">*</span></label>
                                            <input type="text" name="l1com" id="l1com" class="form-control" value="<?php echo $level1_comm;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "10">
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
                                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Help</h5>
                                                       

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                        <br>

                       
                           

                       

                       
                        <br>

                        <!-- end row -->


                       
                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>