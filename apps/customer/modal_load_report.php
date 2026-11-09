<?php
//session_start();
include "login.check.php";
require_once 'autoload.php';

?>
<div class="total-usage">
	<h6 class="bg-secondary text-white p-1"><?=Input::get('type');?> Usage History</h6>
	<table class="table table-borderless">
		<thead>
			<tr>
				<th><b>Service</b></th>
				<th><b>Amount</b></th>
				<th><b>Cost</b></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$userId = Session::get('userId');
				$type = Input::get('type');
				
				$totalcost = 0; 
				$total_bal = 0;
				$bal = 0;
				foreach (Report::totalReport_type($userId, $type) as $res):
				$totalcost += $res->cost;
				
				$total_bal = (Reseller::info($res->userid, 'balance') + $totalcost); 
				
				$bal = (Report::resellerCredit_user($userId, $type) - Report::resellerDebit_user($userId, $type));
				
				if(!empty($res))
				{
					?>
					<tr>	
						<td><?php echo Module::moduleName($res->service) ?></td>
						<td>৳<?php echo number_format($res->bal,2); ?></td>
						<td>৳<?php echo number_format($res->cost,2); ?></td>
					</tr>
					<?php
				}
				else
				{
					?>
					<tr>	
						<td colspan="2">No Data Found</td>
					</tr>
					<?php
					
				}
				?>
			<?php endforeach; ?>
		</tbody>
	</table>                        
</div>
<div class="total-cost">
	<h6 class="bg-secondary text-white p-1">Total Cost</h6>                            
	<table class="table table-borderless">
		<tbody>
			<tr>
				<td>Total Cost</td>
				<td>৳<?php echo number_format($totalcost,2) ?></td>
			</tr>
			<tr>
				<td>Current Balance(+)</td>
				<td>৳<?php echo number_format(Reseller::info($userId, 'balance'),2); ?></td>
			</tr>
			<tr>
				<td><b>Total</b></td>
				<td>৳<?=number_format($total_bal,2);?></td>
			</tr>
		</tbody>
	</table>                              
</div>
<div class="summery">
	<h6 class="bg-secondary text-white p-1">Summary</h6>                            
	<table class="table table-borderless">
		<tbody>
			<tr>
				<td>Total Recieve</td>
				<td>৳<?php echo number_format(Report::resellerCredit_user($userId, $type),2); ?></td>
			</tr>
			<tr>
				<td>Total Return(-)</td>
				<td>৳<?php echo number_format(Report::resellerDebit_user($userId, $type),2); ?></td>
			</tr>
			<tr>
				<td><b>Balance</b></td>
				<td>৳<?=number_format($bal,2);?></td>
			</tr>
		</tbody>
	</table>                              
</div>