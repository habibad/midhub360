<?php
include "login.check.php"; 
include "class/Reseller.php";
include "class/DB.php";
include "class/Config.php";
include "header.php";

?>
		<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="add_customer.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">person_add</span> </div>
                    <p class="small text-white">Add Customer</p>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">payments</span> </div>
                    <p class="small text-white">Give Payment</p>
                </div>
                <div class="col-auto">
                <a href="payment_given.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">receipt</span> </div>
                    <p class="small text-white">Payment Hist.</p>
                </a>
                </div>                
            </div>
        </div>


    <div class="main-container">

		<div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>

                    <span class="title-text">Customer List</span>
            </div>


        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-resellers-list" role="tabpanel" aria-labelledby="nav-resellers-list-tab"> 
            
            <!--
            
            <button class="btn btn-primary mt-3 btn-block position-fixed add-new-btn" data-toggle="modal" data-target="#add-new-reseller"><i class="fas fa-plus"></i> Add New</button>
			
            -->
				
				<?php if (Session::exists('error')){
						?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
						   <?=Session::Flush('error');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						</div>
						<?php
				  }?>
				<?php if (Session::exists('success')){
					?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
						  <strong>Well Done!</strong> <?=Session::Flush('success');?>
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						</div>
						<?php
				}?>
		
                <div class="resellers-list-table table-responsive">
                                    <table id="cslist" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                   
                                                    <th>Username</th>                                                   
                                                    <th>Re. Balance</th>
                                                    <th>M. Balance</th>
                                                    <th>Status</th>
                                                    <th>Level</th>
                                                    <th>ID</th>
                                                    <th>Add Balance</th>
                                                    <th>Payment Hist.</th>
                                                </tr>
                                            </thead>                                        
                                        
                                            <tbody>

                                            </tbody>
                                        </table>

                </div>
            </div>
			
			

        </div>
    

    <!--Add New Reseller Modal-->
    <div class="modal fade" id="add-new-reseller" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New DGM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <div class="container">
            <form action="route.php?action=addNewReseller" method="post" accept-charset="utf-8">
					<?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>On Snap!</strong> <?=Session::Flush('error');?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							</div>
							<?php
					}?>
					<?php if (Session::exists('success')){
						?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
								<strong>Well Done!</strong> <?=Session::Flush('success');?>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								</div>
								<?php
						}?>
                            <div class="row">
                                <div class="col">
                                    <div class="add-reseller-left">
                                    <p class="text-info"><b>Login Information</b></p>
                                    <hr>
                                            <input type="hidden" name="token" value="<?php echo $token->token;?>">
                                            <input type="hidden" name="p_id" value="<?php echo $sessionid; ?>">
                                            <div class="form-group">
                                                <input type="text" name="username" id="username" class="custom-form-control" placeholder="Enter Username" value="" minlength="4" maxlength="20" required>
                                                <p class="help-block form_error"></p>
                                            </div>
                                            <div class="form-group">
                                                <input type="password" name="password" id="password" class="custom-form-control" placeholder="Enter Password" value="" minlength="6" maxlength="12" required>
                                                <p>e.g: hhdsdhA85%43@</p>
                                                <p class="help-block form_error"></p>
                                            </div>
                                            <div class="form-group">
                                                <input type="password" minlength="4" maxlength="8" name="pin" id="pin" class="custom-form-control" placeholder="Enter PIN" required="" onkeypress="return onlyNumbers();"> 
                                                <p class="help-block form_error"></p>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="name" id="name" class="custom-form-control" placeholder="Enter Full Name">
                                                <p class="help-block form_error"></p>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="mobile" id="mobile" class="custom-form-control" placeholder="Enter Mobile Number">
                                                <p class="help-block form_error"></p>
                                            </div>
                                            <div class="form-group">
                                                <input type="email" name="email" id="email" class="custom-form-control" placeholder="Enter Email Address">
                                                <p class="help-block form_error"></p>
                                            </div>
											<div class="form-group form-check">
                                                <input type="checkbox" class="form-check-input" name="active" value="1" checked>
                                                <label class="form-check-label" for="exampleCheck1">Active</label>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-info btn-block">Add DGM</button>
                            </div>
                        </form>
                        </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
            </div>
        </div>
    </div>
    <!--Payment History Modal-->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="load_payment_history_data">
            
            </div>
        </div>
        

    </div> 
	
	<!--Add Payment Modal-->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="load_add_payment_data">
            
            </div>
        </div>
    </div>

</div>
	</main>	


<?php
     include "footer.php";
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

<script>
$(document).ready(function() {
    var table = $('#cslist').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true,
        "pagingType": "numbers",
        "processing": true,
        "serverSide": true,
        "ajax": "pagination/all_customer.php"

    } );
} );
</script>
