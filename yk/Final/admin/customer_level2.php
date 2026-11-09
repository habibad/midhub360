<?php 
include "login.check.php";
include "header.php";

$level2_name = Mixedfn::levelnamechange("1","level2");
$cs_id = Xss::xssClean(Input::get('id'));

if(isset($cs_id) && $cs_id !=0 && !empty($cs_id))
{      
    /*  
    $query= "SELECT * from user where id='$cs_id'";
    global $connect;
    $result = mysqli_query($connect, $query);
    
    if(!$result){
        die("Query Failed!" . mysqli_error($connect));      
    } 
    
    $getdata= mysqli_fetch_object($result);

    $id              = $getdata->id;
    $user_id         = $getdata->user_id;
    $amount          = $getdata->amount;
    $submitted_date  = $getdata->submitted_date;
    $load_by         = $getdata->load_by;
    $updated         = $getdata->updated;
    $note            = $getdata->note;
    $ip              = $getdata->ip;
    $return_payment	 = $getdata->return_payment;
    $old_balance     = $getdata->old_balance;
    $new_balance     = $getdata->new_balance;
    

    $queryin  = "INSERT INTO  trush_user (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
    $queryin .= "VALUES('$id','$user_id','$amount','$submitted_date','$load_by','$updated','$note','$ip','$return_payment','$old_balance','$new_balance')"; //Used concatenation (.) add two string

    $resultin = mysqli_query($connect, $queryin);    
    if(!$resultin)
    {
        
        die("Query Failed!" . mysqli_error($connect));
    }

   */

    $querydl = "delete from user where id='$cs_id'";    
    $resultdl = mysqli_query($connect, $querydl);  
    
    
    
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
                                            <li class="breadcrumb-item active"><?php echo $level2_name; ?> List</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title"><?php echo $level2_name; ?> List</h4>
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
        "ajax": "pagination/cs_level2.php"
    } );
} );
</script>
