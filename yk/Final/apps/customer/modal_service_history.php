<?php
//session_start();
include "login.check.php";
require_once 'autoload.php';


$amountTotal ='';
$costTotal ='';
$service = Input::get('service');
$service_name = Input::get('service_name');

$rows = 30;
$i = 1; 
$pagin = new Pagination($rows,'page');
$start = $pagin->getStart();

$pagin->setTotal(Report::totalHistory());

$res = DB::getInstance()->get(Report::history_of_service($service, Session::get('userId')) . $pagin->getLimit())->result();


?>

<div class="modal-header">
        <h5 class="modal-title">History of <?=$service_name;?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <table class="table primary-table table-borderless">
	  
		<tbody>
		<?php 
			
			foreach($res as $pending):
			$amountTotal += $pending->balance; 
			$costTotal += $pending->cost; 
			$cur_bal = ($pending->prebalance - $pending->cost);
				
			if(empty($pending))
			{
				?>
				<tr>
					<td rowspan="3">
						No data Found.
					</td>
				</tr>
				<?php
			}
			else{
				?>
				<tr>
				<td>
					<?php
					if(!empty($pending->service))
					{
						?>
						<img src="../services/<?php echo Module::moduleName($pending->service) ?>.png" alt="">
						<?php
					}
					else{
						?>
						<img src="../services/default.png" alt="">
						<?php
					}
					?>
				</td>
				<td>
					<span class="transaction-id"><?php echo $pending->phone; ?></span>
					<span class="transaction-amount">(৳ <?php echo $pending->balance ?>)</span><br>
					<span class="tr-cost"><b>Cost : ৳</b><?php echo number_format($pending->cost,2) ?></span> <br>
					<span class="tr-bal"><b>Bal : ৳</b><?php echo number_format($cur_bal,2); ?></span><br>
					<span class="tr-number"><b>Trxn :</b> <?php echo empty($pending->trxid) ? "N/A" : $pending->trxid ?></span>
				</td>
				<td>
					<div class="tr-time-status">
						<span class="date"><b><?=date("M j, Y", strtotime($pending->submitted_date));?></b></span><br>
						<span class="time"><?=date("g:i a", strtotime($pending->submitted_date));?></span><br>
						<span class="status">
						<?php
							  if($pending->status == '0'):
									echo '<span class="badge badge-warning p-2">Pending</span>';
							  elseif($pending->status == '1'):
									echo '<span class="badge badge-success p-2">Success</span>';
							  elseif($pending->status == '2'):
									echo '<span class="badge badge-danger p-2">Cancelled</span>';
							  elseif ($pending->status == '4'):
									echo '<span class="badge badge-info p-2">Waiting</span>';
							  endif;
						?> 
						</span>
					</div>
				</td>
			</tr>
			<?php
			}
		   ?>
			
			<?php endforeach; ?>
		</tbody>
	</table>
      </div>