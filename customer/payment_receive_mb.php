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
                                            <li class="breadcrumb-item active">Payment Received History (M. Bank)</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Payment Received History (M. Bank)</h4>
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
        "ajax": "pagination/payment_receivedmb.php"
       /* */

    } );
} );

</script>