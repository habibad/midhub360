<?php 
include "login.check.php";
include "header.php";

$admin_name      = Mixedfn::levelnamechange("1","admin");
$subadmin_name   = Mixedfn::levelnamechange("1","subadmin");
$level4_name     = Mixedfn::levelnamechange("1","level4");
$level3_name     = Mixedfn::levelnamechange("1","level3");
$level2_name     = Mixedfn::levelnamechange("1","level2");
$level1_name     = Mixedfn::levelnamechange("1","level1");

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
                                            <li class="breadcrumb-item active">Change Level Name</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Change Level Name</h4>
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

                                     <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Change Level Name</h5>

                                        <!--<h4><span class="text-danger">কোন লেভেল এর একাউন্ট ফি কত তা এখানে সেট করবেন।</span></h4> <br> -->

                                    <form action="route.php?action=levelchange" method="post" accept-charset="utf-8">
                                       
                                        <input type="hidden" name="id" id="id" value="1">

                                       
                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="admin" id="admin" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30" value="<?php echo $admin_name;?>">
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Sub Admin<span class="text-danger">*</span></label>
                                            <input type="text" name="subadmin" id="subadmin" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30" value="<?php echo $subadmin_name;?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Level4<span class="text-danger">*</span></label>
                                            <input type="text" name="level4" id="level4" class="form-control" value="<?php echo $level4_name;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30">
                                        </div> 
                                        <div class="mb-3">
                                            <label for="product-price">Level3<span class="text-danger">*</span></label>
                                            <input type="text" name="level3" id="level3" class="form-control" value="<?php echo $level3_name;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30">
                                        </div> 
                                        <div class="mb-3">
                                            <label for="product-price">Level2<span class="text-danger">*</span></label>
                                            <input type="text" name="level2" id="level2" class="form-control" value="<?php echo $level2_name;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30">
                                        </div>
                                        <div class="mb-3">
                                            <label for="product-price">Level1<span class="text-danger">*</span></label>
                                            <input type="text" name="level1" id="level1" class="form-control" value="<?php echo $level1_name;?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30">
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