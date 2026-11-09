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
                                            <li class="breadcrumb-item active">All Customer List</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">All Customer List</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


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


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="add_customer.php" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle me-2"></i> Add Customer</a>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                                                                              
                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Username</th>                                                   
                                                    <th>Re. Balance</th>
                                                    <th>M. Balance</th>
                                                    <th>Status</th> 
                                                    <th>View</th>
                                                    <th>Edit</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>

                        

                                                
                                            </tbody>
                                        </table>

                                

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->
                
                <a href="pagination/server.php"></a>

<?php 
include "footer-data.php";
?>

<script>
$(document).ready(function() {
    $('#contact-detail').dataTable({
		"scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,
        "ajax": "pagination/all_customer.php"
    } );
} );
</script>
