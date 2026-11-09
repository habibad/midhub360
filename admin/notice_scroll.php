<?php 
include "login.check.php";
include "header.php";


$query = "SELECT FullNotice FROM noticelist where id='2'";        
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
}        

$data = mysqli_fetch_object($result); 

$fullmsg = $data->FullNotice;

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
                                           
                                            <li class="breadcrumb-item active">Scroll Notice</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Scroll Notice</h4>
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
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                    <form action="route.php?action=addnotice" method="post" accept-charset="utf-8">

                                        <input type="hidden" name="nid" id="nid" value="2">                                       
                                        <div class="mb-3">                                            
                                            <textarea class="form-control" name="notice" id="notice" rows="5" placeholder="Write notice"> <?php echo $fullmsg; ?></textarea>
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                                        </div> 

                                    </form>

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col -->
                        </div>
                        <!-- end row -->  

                       
                        
                    </div> <!-- container -->

                </div> <!-- content -->
				

<?php 
include "footer.php";
?>
