<?php 
include "login.check.php";
include "header.php";

 $web_link         = branddata("web_link");
 $apps_name        = branddata("apps_name");
 $logo_name        = branddata("logo_url");                                               

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
                                            <li class="breadcrumb-item active">Branding</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Branding</h4>
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

                                        <form action="route.php?action=UpdateBrand" method="post" accept-charset="utf-8" enctype="multipart/form-data"> 

                                   <!--  <form action="upload.php" method="post" enctype="multipart/form-data"> -->                                      

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Web Link<span class="text-danger">*</span></label>
                                            <input type="text" name="weblink" id="weblink" class="form-control" value="<?php echo $web_link;?>" placeholder="Enter Web Link">
                                        </div>
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Apps Name<span class="text-danger">*</span></label>
                                            <input type="text" name="appname" id="appname" class="form-control" value="<?php echo $apps_name;?>" placeholder="Enter Apps Name">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="example-fileinput" class="form-label">Upload Logo</label>
                                            <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                                        </div>

                                        <img class="d-flex me-2 rounded-circle" src="../uploads/logo/<?php echo $logo_name;?>" alt="" height="96">
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->


                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" name="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                        </div> 

                </form>

                        <br>

                        <!-- end row -->



                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>