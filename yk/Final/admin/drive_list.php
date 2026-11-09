<?php 
include "login.check.php";
include "header.php";

$drive_id   = Xss::xssClean(Input::get('drive_id'));


if(isset($drive_id))
{      
    $query = "delete from drive where id='$drive_id'";    
    $result = mysqli_query($connect, $query); 
    //Session::Flush('success', 'Successfully Deleted!');
    //Redirect::to('sim_sms_reply.php');  
}


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
                                            <li class="breadcrumb-item active">Drive List</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Drive List</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="drive_add.php" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle me-2"></i> Add New Drive</a>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>



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

                                                                              
                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                        <th>ID</th>
                                                        <th>Operator</th>
                                                        <th>Drive Name</th> 
                                                        <th>Amount</th>
                                                        <th>Commission</th>                                                
                                                        <th>Status</th>
                                                        <th>Edit</th>                                              
                                                        <th>Delete</th>                                              
                                                                                                        
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
        "ajax": "pagination/drive_list.php"
    } );
} );
</script>
