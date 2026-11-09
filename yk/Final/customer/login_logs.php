<?php 
include "login.check.php";
include "header.php";

$blockip = Xss::xssClean(Input::get('ip'));
$msg = "";

if(isset($blockip) and !empty($blockip))
{      
                
    $query  = "INSERT INTO ip_block (id,block_ip)";
    $query .= "VALUES(0,'$blockip')"; 
                 
    $result = mysqli_query($connect, $query);    
    if(!$result)
    {
                
    die("Query Failed!" . mysqli_error($connect));
    }

    $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    Successfully Blocked this " . $blockip. " IP
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'>
    </button>
    </div>";
    /* */

    
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
                                            <li class="breadcrumb-item active">Login Logs</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Login Logs</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                       
                        <?php echo $msg ?>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                               
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
                                                    <th>Date</th>
                                                    <th>Username</th>                                                   
                                                    <th>Trying Password</th>
                                                    <th>IP</th>
                                                    <th>Browser</th>
                                                    <th>OS</th>
                                                    <th>Status</th>
                                                    <th>IP Block</th>                                                
                                                                                                   
                                                </tr>
                                            </thead>
                                        <tbody>                                                                              
                                           
                                        </tbody>
                                
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>Total:</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                
                                            </tr>
                                        </tfoot>                                         

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
    $('#contact-detail').DataTable( {
               
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,
        "ajax": "pagination/login_logs.php"
       /* */

    } );
} );

</script>
