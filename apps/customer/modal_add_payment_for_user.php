<?php
//session_start();
include "login.check.php";
include "../function.php";
require_once 'autoload.php';

$username = Input::get('user');
$resellerId = Input::get('userid');
$logged_user = Session::get('us_id');
 
?>


<div class="modal-header">
	<h5 class="modal-title" id="payment_header">Add Payment for <?php echo username($resellerId);?></h5>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	<span aria-hidden="true">&times;</span>
	</button>
</div>
<div class="modal-body">
	<form action="" method="POST" name="form_add_payment">
	<input type="hidden" name="resId" value="<?php echo $resellerId;?>">
		<!--<div class="form-group row">-->
		<!--	<label for="staticEmail" class="col-sm-4 col-form-label">Client Type</label>-->
		<!--	<div class="col-sm-8">-->
		<!--	<input type="text" name="resType" id="resType" class="form-control" value="<?php echo Reseller::type($resellerId)?>" readonly>-->
		<!--	</div>-->
		<!--</div>-->
		<!--<div class="form-group row">-->
		<!--	<label for="staticEmail" class="col-sm-4 col-form-label">Parent</label>-->
		<!--	<div class="col-sm-8">-->
		<!--	<input type="text" name="parent" id="parent" class="form-control" value="<?php echo Reseller::parent(Reseller::info($resellerId, 'parent'))?>" readonly>-->
		<!--	</div>-->
		<!--</div>-->
		<div class="form-group row">
			<label for="staticEmail" class="col-sm-4 col-form-label">Recharge Balance</label>
			<div class="col-sm-8">
			<input type="text" name="oldphone" id="balance" class="form-control" value="<?php echo Reseller::info($resellerId, 'balance2')?>" readonly>
			</div>
		</div>
		<div class="form-group row">
			<label for="staticEmail" class="col-sm-4 col-form-label">M. Balance</label>
			<div class="col-sm-8">
			<input type="text" name="balance" id="balance" class="form-control" value="<?php echo Reseller::info($resellerId, 'bkash_balance')?>" readonly>
			</div>
		</div>
		
		<div class="form-group row">
			<label for="staticEmail" class="col-sm-4 col-form-label">Amount</label>
			<div class="col-sm-8">
			<input type="number" name="amount" id="amount" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "7" placeholder="Enter Amount" onKeyPress="return isNumberKey(event)" required>
			<p class="text-center text-secondary">Available- Re. Bal: ৳ <?php echo Reseller::info($logged_user, 'balance2');?> Re. Bal: ৳ <?php echo Reseller::info($logged_user, 'bkash_balance');?></p> 
			</div>
		</div>
		<div class="form-group row">
			<label for="inputPassword" class="col-sm-4 col-form-label">Type</label>
			<div class="col-sm-8">
				<select name="type" class="form-control">
				  <option selected="selected" value="Transfer">
						Recharge
				  </option>
				  <option value="TransferMb">
						Mobile Banking
				  </option>
				  <!--
				  <option value="Return">
						Mobile Banking
				  </option>
				  -->
			</select>
			</div>
		</div>
		<div class="form-group row">
			<label for="staticEmail" class="col-sm-4 col-form-label">Payment Note</label>
			<div class="col-sm-8">
			<input type="text" name="des" class="form-control" placeholder="Enter Payment Notes" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "40">
			</div>
		</div>
		<div class="form-group row">
			<label for="staticEmail" class="col-sm-4 col-form-label">PIN</label>
			<div class="col-sm-8">
			<input type="password" name="pincode" class="form-control" placeholder="Enter PIN" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
			</div>
		</div>
	
</div>
<div class="modal-footer">
	<!--<div class="form-group form-check mt-3">
		<input type="checkbox" class="form-check-input" id="exampleCheck1">
		<label class="form-check-label" for="exampleCheck1">Notify via SMS</label>
	</div>   -->         
	<button type="button" id="btn_add_payment" class="btn btn-success">Payment</button>
	<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
</div>
</form>