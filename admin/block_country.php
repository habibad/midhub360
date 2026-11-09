<?php 
include "login.check.php";
include "header.php";
    
$country_id = Xss::xssClean(Input::get('country_id'));

if(isset($country_id) and !empty($country_id))
{      
    $query = "delete from country_allow where id='$country_id'";    
    $result = mysqli_query($connect, $query);    
}              
               
 
/* Fetch country list */
$query1  = "SELECT * FROM countries ORDER BY id ASC";                
$result1 = mysqli_query($connect, $query1); 
/* Fetch country list End */

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
                                            <li class="breadcrumb-item active">Allow Country</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Allow Country</h4>
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

                                    <form action="route.php?action=CountryAllow" method="post" accept-charset="utf-8">


                                    <div class="mb-3">
                                            <label for="product-category" class="form-label">Select Country<span class="text-danger">*</span></label>
                                           
                                            <select name="country" class="form-control select2" id="country">

                                                <option value="">Select Country</option>
                                                <?php

                                                    while($data = mysqli_fetch_array($result1))
                                                    {
                                                        echo "<option value='". $data['country_code'] ."'>" .$data['country_name'] ."</option>";
                                                    }

                                                ?>
                                               
                                            </select>

                                    </div>
 
                                       
                                        <div class="button-list pe-xl-4 d-grid">
                                             <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Allow</button>                                                    
                                        </div> 
                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">                              
                               
                                <div class="card">
                                    <div class="card-body">                                       
                                      
                                       <h3>Allow Country List</h3> 

                                        <br>

                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                        <th>ID</th>
                                                        <th>Country Name</th>                                                                                                        
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
        "ajax": "pagination/block_country.php"
    } );
} );
</script>
