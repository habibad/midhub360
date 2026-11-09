<?php
//session_start();
include "login.check.php";
include "../function.php";
require_once 'autoload.php';
require_once '../db.php';

$username = Input::get('user');
$resellerId = Input::get('userid');

?>

<div class="modal-header">
	<h6 class="modal-title" id="exampleModalCenterTitle">Payment History of <b> <?php echo username($resellerId);?> </b></h6>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	<span aria-hidden="true">&times;</span>
	</button>
</div>

	
<div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-recharge-history" role="tabpanel" aria-labelledby="nav-recharge-history-tab">
                <div class="">

<table class="table mb-0">
					
    <tbody>

		<?php

				$reseller_id=$resellerId;
				$max_results=25;
				//$from = (($page * $max_results) - $max_results);

				$query="SELECT *from reseller_balance where user_id='$reseller_id' order by id desc";

				global $connect;
				$result = mysqli_query($connect, $query);

					//$reseller_data= mysqli_fetch_object($result);

					?>

					<?php 
						
					while($row=mysqli_fetch_object($result))
					{
					$payment_date    =  $row->updated;
					$payment_amount  =  $row->amount;
					$payment_note    =  $row->note;
					$payment_return  =  $row->return_payment;
					$user_id         =  $row->user_id;
					$old_bl     	   =  $row->old_balance ;
					$new_bl          =  $row->new_balance;
					$load_by         =  $row->load_by;		


						$r_query          = "select *from user where id='$load_by'";
					
						$result2 = mysqli_query($connect, $r_query);

						if(!$result2){
						die("Query Failed!" . mysqli_error($connect));      
						} 

					$reseller_data= mysqli_fetch_object($result2);
					
					$load_by_name    = $reseller_data->user;
						
					$u_query          = "select *from user where id='$user_id'";

					$result3 = mysqli_query($connect, $u_query);

						if(!$result3){
						die("Query Failed!" . mysqli_error($connect));      
						} 

					$reseller_data= mysqli_fetch_object($result3);
					
					$reseller_name    = $reseller_data->user;


					$date = date("M j, Y g:i a", strtotime($payment_date));
					

	echo "
	<tr>
    		
			<td>
			     <span><b>By: </b></span><span class='transaction-id'> $load_by_name</span></br>
				 <span class='tr-number'><b>Date :</b> $date</span><br>				 
                 <span class='tr-number'><b>Note :</b> $payment_note</span>
                                     
            </td>

		<td>

			<div class='tr-time-status'> ";
				
					if($payment_return == 0)
					{
									
						echo "
							<span><b>Added :</b></span><span class='credit'><b> ৳ $payment_amount</b></span><br>
							<span class='tr-number'>New Balance : ৳</b>$new_bl</span><br>
							<span class='tr-number'>Old Balance : ৳</b>$old_bl</span> ";										
					}
					elseif($payment_return == 1)
					{										
						echo "
							<span><b>Return :</b></span> <span class='debit'><b> ৳ $payment_amount</b></span><br>
							<span class='tr-number'>New Balance : ৳</b>$new_bl</span><br>
							<span class='tr-number'>Old Balance : ৳</b>$old_bl</span>";
					}
					else
					{
						echo "Unknown";

					}
										
						echo "							
		   </div>
		</td>


	</tr> ";


	}

	?>

</tbody>
</table>

				
			</div>
		</div>
	</div>

<div class="modal-footer">
	<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
</div>