<?php 
include "login.check.php";
include "header.php";

$total_amount = Mixedfn::totalpendingbank();

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
                                            <li class="breadcrumb-item active">Banking Pending History</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Banking Pending History</h4>
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
                                        <div class="total-balance">
                                            <span>Total Pending Amount: </span><span class="text-danger"><strong><?php echo $total_amount; ?></strong></span>
                                        </div> <br>
                                                                              
                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date</th>
                                                    <th>Username</th>                                                   
                                                    <th>Bank Name</th>
                                                    <th>Account Name</th>
                                                    <th>Account Number</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>                                                
                                                                                                   
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


                
         <!-- Recharge Success modal start-->
         <div id="suceessbank" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Success Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=banksuccess" method="POST">
            <div class="modal-body" id="load_request_data">
                                                       
            </div>                

                <div class="px-3">             
                    <div class="mb-3">
                        <label for="username" class="form-label">Transaction ID/DC No</label>
                            <input class="form-control" type="text" name="txnid" id="txnid" required="" placeholder="Transaction ID" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25">
                    </div>  
                    
                                       
                </div>
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-success">Complete</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Success modal End-->


<!-- Recharge Cancel modal start-->
<div id="cancelbank" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Cancel Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                       
                <form action="route.php?action=bankcancel" method="POST">
            <div class="modal-body" id="load_request_cldata">
                                                       
            </div>                

                <div class="px-3">             
                                       
                    <div class="mb-3">
                        <label for="username" class="form-label">Reason</label>
                            <input class="form-control" type="text" name="remsg" id="remsg" required="" placeholder="Message">
                    </div>  
                    
                </div>
                    <div class="modal-footer">
					    <button type="submit" class="btn btn-danger">Cancel</button>
					    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				    </div>
                </form>
                                  

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Cancel modal End-->



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
        "ajax": "pagination/pending_banking.php"
       /* */

    } );
} );

</script>
