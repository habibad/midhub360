<?php 
include "login.check.php";
include "header.php";

    //$reseller_id=$_GET["reseller_id"];

    $reseller_id   = Xss::xssClean(Input::get('reseller_id'));


    if(!isset($reseller_id) || empty($reseller_id)){
        session_destroy();
        header("location:../index.php");
        die();
    }


    
    /* Check Created User*/

    $query  = "SELECT * from user where id='$reseller_id' and created_by='$us_id'";    
    
    $result = mysqli_query($connect, $query); 

    $reseller_data= mysqli_fetch_object($result);
    
    $count = mysqli_num_rows($result);

    if($count < 1) {  

        session_destroy();
	    header("location:../index.php");
	    die();
    }

    /* Check Created User*/

    $full_name       = fullname($reseller_id);      
    $limit_balance   = limit_balance($reseller_id);
    $mb_balance      = mbanking_balance($reseller_id);
    $re_balance      = recharge_balance($reseller_id);
    $username        = username($reseller_id);
    $created_by      = created_by($reseller_id);     
    $fullname        = fullname($reseller_id); 
    $address         = address($reseller_id);
    $email_address   = email_address($reseller_id);
    $phone_number    = phone_number($reseller_id);
    $regi_date       = joining_date($reseller_id); 
    
    $title_level     = 0;

    $userlevel       = Mixedfn::CustomerLevel($reseller_id);    

    $subadmin_name   = Mixedfn::levelnamechange("1","subadmin");
    $level4_name     = Mixedfn::levelnamechange("1","level4");
    $level3_name     = Mixedfn::levelnamechange("1","level3");
    $level2_name     = Mixedfn::levelnamechange("1","level2");
    $level1_name     = Mixedfn::levelnamechange("1","level1");


    if ($userlevel == "4")
    {
        $title_level = $subadmin_name;
    }
    if ($userlevel == "3")
    {
        $title_level = $level4_name;
    }
    if ($userlevel == "2")
    {
        $title_level = $level3_name;
    }
    if ($userlevel == "1")
    {
        $title_level = $level2_name;
    }
    if ($userlevel == "0")
    {
        $title_level = $level1_name;
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
                                            <li class="breadcrumb-item"><a href="customer_list.php">Customer List</a></li>
                                            <li class="breadcrumb-item active">Customer Profile</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title"><?php echo $username;?>'s Profile</h4>

                                    <!-- Error Code Start-->

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

                     <!-- Error Code End-->

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-4 col-xl-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <img src="../assets/images/users/user-3.jpg" class="rounded-circle avatar-lg img-thumbnail"
                                        alt="profile-image">

                                        <h4 class="mb-0"><?php echo "$full_name"; ?></h4>
                                        <p class="text-muted"><?php echo "Username: $username ($title_level)"; ?></p>

                                        <button type="button" class="btn btn-success btn-xs waves-effect mb-2 waves-light" data-bs-toggle="modal" data-bs-target="#addPaymentModal" data-user="<?php echo "$username"; ?>" data-userid="<?php echo "$reseller_id"; ?>">Add Balance</button>
                                        
                                        <div class="text-start mt-3">
                                            <h4 class="font-13 text-uppercase">About Me :</h4>
                                           
                                            <p class="text-muted mb-2 font-13"><strong>Full Name :</strong> <span class="ms-2"><?php echo "$full_name"; ?></span></p>
                                        
                                            <p class="text-muted mb-2 font-13"><strong>Mobile :</strong><span class="ms-2"><?php echo "$phone_number"; ?></span></p>
                                        
                                            <p class="text-muted mb-2 font-13"><strong>Email :</strong> <span class="ms-2"><?php echo "$email_address"; ?></span></p>
                                        
                                            <p class="text-muted mb-2 font-13"><strong>Address :</strong> <span class="ms-2"><?php echo "$address"; ?></span></p>
                                            
                                            <p class="text-muted mb-2 font-13"><strong>Joining Date:</strong> <span class="ms-2"><?php echo "$regi_date"; ?></span></p>

                                            <p class="text-muted mb-2 font-13"><strong>Recharge Balance:</strong> <span class="ms-2"><?php echo "$re_balance"; ?></span></p>

                                            <p class="text-muted mb-2 font-13"><strong>M. Bank Balance:</strong> <span class="ms-2"><?php echo "$mb_balance"; ?></span></p>

                                            <p class="text-muted mb-2 font-13"><strong>Limit Balance:</strong> <span class="ms-2"><?php echo "$limit_balance "; ?></span></p>

                                        
                                        
                                        </div>                                    

                                       
                                    </div>                                 
                                </div> <!-- end card -->


                            </div> <!-- end col-->

                            <div class="col-lg-8 col-xl-8">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-pills nav-fill navtab-bg">
                                            <li class="nav-item">
                                                <a href="#aboutme" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Reports
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Recharge Payment History
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    M. Bank Payment Hist.
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane" id="aboutme">
    
                                                No Data


    
                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->
    
                                            <div class="tab-pane show active" id="timeline">
    
                                             
                                                <!-- Start Payment History Table-->


                                                <table id="contact-detail" class="table table-striped dt-responsive w-100">
                                   
                                        
                                            <thead>
                                                    <tr>
                                                        
                                                        <th>Date</th>
                                                        <th>Loaded By</th>
                                                        <th>Amount</th> 
                                                        <th>Old Balance</th>
                                                        <th>New Balance</th>
                                                        <th>Type</th>                                                
                                                        <th>Note</th>                                                        
                                                                                                    
                                                    </tr>
                                                </thead>

                                            <tbody>
                 
                                       </tbody>
                                   </table>

                                       

                                            <!-- End Payment History Table-->

    
                                            </div>
                                            <!-- end timeline content-->
    
                                            <div class="tab-pane" id="settings">


                                                 <!-- Start Mobile Banking Payment History Table-->


                                                 <table id="mb-payment" class="table table-striped dt-responsive w-100">
                                   
                                        
                                   <thead>
                                           <tr>
                                               
                                               <th>Date</th>
                                               <th>Loaded By</th>
                                               <th>Amount</th> 
                                               <th>Old Balance</th>
                                               <th>New Balance</th>                                                
                                               <th>Type</th>                                                
                                               <th>Note</th>
                                                                                             
                                           </tr>
                                       </thead>

                                   <tbody>
                                                                
          
                              </tbody>
                          </table>

                    <!-- End Mobile Banking Payment History Table-->



                                            </div>
                                            <!-- end settings content-->
    
                                        </div> <!-- end tab-content -->
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                         <!-- History Start-->

                        <div class="row">
                            <div class="col-lg-4 col-xl-12">
                                <div class="card text-center">
                                    <div class="card-body">
                                    <ul class="nav nav-pills nav-fill navtab-bg">
                                            <li class="nav-item">
                                                <a href="#drive" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Drive History
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#recharge" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Recharge History
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#mbank" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    M. Bank History
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <div class="tab-pane" id="drive">
    
                                    <!-- Start Recharge History Table-->

                                    <table id="drivehist" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                                        <thead>
                                                                <tr>
                                                                    
                                                                <th>ID</th>
                                                                <th>Date</th>                                                                                                     
                                                                <th>Number</th>
                                                                <th>Operator</th>
                                                                <th>Type</th>
                                                                <th>Amount</th>
                                                                <th>Cost</th>
                                                                <th>Status</th>                                                                                                        
                                                                                                                
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
                                                            
                                                        </tr>
                                                    </tfoot>               

                                                </table>                              

                                            <!-- End Payment History Table-->


                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->
    
                                            <div class="tab-pane show active" id="recharge">
    
                                             
                                    <!-- Start Recharge History Table-->

                                    <table id="rechargehist" class="table table-striped dt-responsive w-100">
                                   
                                        
                                            <thead>
                                                    <tr>
                                                        
                                                    <th>ID</th>
                                                    <th>Date</th>                                                                                                     
                                                    <th>Number</th>
                                                    <th>Operator</th>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>                                                                                                        
                                                                                                    
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
                                                <th>Total:</th>
                                                <th></th>
                                                <th></th>                                                
                                                
                                            </tr>
                                        </tfoot>               

                                   </table>

                                       

                             <!-- End Payment History Table-->

    
                                            </div>
                                            <!-- end timeline content-->
    
                                            <div class="tab-pane" id="mbank">


                                                 <!-- Start Mobile Banking Payment History Table-->


                                <table id="mbhistory" class="table table-striped dt-responsive w-100">
                                   
                                        
                                                    <thead>
                                                                <tr>                                                                    
                                                                <th>ID</th>
                                                                <th>Date</th>                                                                                                 
                                                                <th>Company</th>
                                                                <th>Ac Number</th>
                                                                <th>Type</th>
                                                                <th>Amount</th>
                                                                <th>Cost</th>
                                                                <th>Status</th>                                                                                                        
                                                                                                                
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
                                                            
                                                        </tr>
                                                    </tfoot>               

                                                </table>                              

                                            <!-- End Payment History Table-->


                                            </div>
                                            <!-- end mbank history content-->
    
                                        </div> <!-- end tab-content -->

                                    </div> <!-- end card-->

                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div> <!-- end row-->

                        <!-- History End-->

                    </div> <!-- container -->

                </div> <!-- content -->
    

<!-- Add Balance modal start-->
<div id="addPaymentModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Add Balance Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
              
            <div class="modal-body" id="load_add_payment_data">
                                                       
            </div>                

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Add Balance modal End-->


<!-- Return Balance modal start-->
<div id="ReturnPaymentModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Return Balance Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
              
            <div class="modal-body" id="load_return_payment_data">
                                                       
            </div>                

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Return Balance modal End-->




<?php    
include "footer-data.php";
?>



<script type="text/javascript">
	$('body').on('click','#btn_add_payment',function(e){
		e.preventDefault();
		swal({
            title: "Are you sure?",
            text: "Are you sure you want to add payment to this user? Once submitted, inputed amount will add into this user's account. Thank you!",
            icon: "warning",
            buttons: [
                'No',
                'Yes'
            ],
            dangerMode: true,
        }).then(function (isConfirm) {
            if (isConfirm) {
                $('form[name=form_add_payment]').attr('action','route.php?action=paymentTransfer');
				$('form[name=form_add_payment]').submit();
            } else {
                // swal("Cancelled", "Your have cancelled the process :)", "error");
            }
        });
	});
	</script>


<script type="text/javascript">
	$('body').on('click','#btn_return_payment',function(e){
		e.preventDefault();
		swal({
            title: "Are you sure?",
            text: "Are you sure you want to retrun payment to this user? Once submitted, inputed amount will return into this user's account. Thank you!",
            icon: "warning",
            buttons: [
                'No',
                'Yes'
            ],
            dangerMode: true,
        }).then(function (isConfirm) {
            if (isConfirm) {
                $('form[name=form_add_payment]').attr('action','route.php?action=returnpayment');
				$('form[name=form_add_payment]').submit();
            } else {
                // swal("Cancelled", "Your have cancelled the process :)", "error");
            }
        });
	});
	</script>



    
<script>
    $(document).ready(function() {
    $('#contact-detail').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 6 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,       
        "ajax": {
                    "type": 'GET',
                    "url": 'pagination/cs_payment.php',
                    "data": 
                    {
                        CustomerID: '<?php echo $reseller_id ?>',
                    }
                }           

                   /* */
       

        
       /* */

    } );
} );

</script>


<script>
    $(document).ready(function() {
    $('#mb-payment').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 6 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,       
        "ajax": {
                    "type": 'GET',
                    "url": 'pagination/cs_paymentmb.php',
                    "data": 
                    {
                        CustomerID: '<?php echo $reseller_id ?>',
                    }
                }           

                   /* */
       

        
       /* */

    } );
} );

</script>

<script>
    $(document).ready(function() {
    $('#rechargehist').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 5 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,       
        "ajax": {
                    "type": 'GET',
                    "url": 'pagination/cs_history_recharge.php',
                    "data": 
                    {
                        CustomerID: '<?php echo $reseller_id ?>',
                    }
                }           

                   /* */

    } );
} );

</script>


<script>
    $(document).ready(function() {
    $('#drivehist').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 5 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,       
        "ajax": {
                    "type": 'GET',
                    "url": 'pagination/cs_history_drive.php',
                    "data": 
                    {
                        CustomerID: '<?php echo $reseller_id ?>',
                    }
                }           

                   /* */

    } );
} );

</script>


<script>
    $(document).ready(function() {
    $('#mbhistory').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 5 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,       
        "ajax": {
                    "type": 'GET',
                    "url": 'pagination/cs_history_mbank.php',
                    "data": 
                    {
                        CustomerID: '<?php echo $reseller_id ?>',
                    }
                }           

                   /* */

    } );
} );

</script>

