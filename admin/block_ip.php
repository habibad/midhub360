<?php 
include "login.check.php";
include "header.php";
    
$ip_id = Xss::xssClean(Input::get('ip_id'));

if(isset($ip_id) and !empty($ip_id))
{      
    $query = "delete from ip_block where id='$ip_id'";    
    $result = mysqli_query($connect, $query);    
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
                                            <li class="breadcrumb-item active">Block IP</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Block IP</h4>
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

                                    <form action="route.php?action=ipblock" method="post" accept-charset="utf-8">
 
                                        <div class="mb-3">
                                            <label for="product-price">IP Address <span class="text-danger">*</span></label>
                                            <input type="text" name="blockip" id="blockip" class="form-control" placeholder="Enter IP Address">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                             <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Block</button>                                                    
                                        </div> 
                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">                              
                               
                                <div class="card">
                                    <div class="card-body">                                       
                                      
                                       <h3>IP Block List</h3> 

                                        <br>

                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                        <th>Serial</th>
                                                        <th>IP</th>                                                                                                        
                                                        <th>Delete</th>                                                                                                   
                                                                                                        
                                                </tr>
                                            </thead>

                                            <tbody>
                                                                              
                                            </tbody>
                                        </table>              

                                       
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
include "footer-data.php";
?>

<script>
$(document).ready(function() {
    $('#contact-detail').dataTable({
		"scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,
        "ajax": "pagination/block_ip.php"
    } );
} );
</script>
