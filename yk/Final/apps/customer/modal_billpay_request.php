<?php
include "login.check.php";
include "class/Session.php";
include "class/Input.php";
   
   $number          = Input::get('number');
   $amount          = Input::get('amount');
   $type            = Input::get('type');
   $company         = Input::get('company');
   $company_type    = Input::get('company_type');
   $cs_name         = Input::get('cs_name');
   $month           = Input::get('month');
   $contact_number  = Input::get('contact_number');
  
   
  ?>

<table style="width:100%;">
			  <tr>
			  <td style="vertical-align:middle;padding: 0 .5rem;">
			  
			  <font size="+0.5">
			  <div class="alert alert-warning" role="alert">Pay <?php echo "$type"; ?> Bill... Are you sure?</div>



			  <?php
					if($type == "Electricity")
					{
					?>
						<p class="pinfo">Company: <span style="color:green;"><?php echo "$company";?></span></p>
						<p class="pinfo">Meter No/Customer/Consumer No: <span style="color:green;"><?php echo "$number";?></span></p>
						<p class="pinfo"><b> Amount : </b><span style="color:green;">৳ <?php echo "$amount";?></span></p>						
						<p class="pinfo">Type: <span style="color:green;"><?php echo "$company_type";?></span></p>
						<p class="pinfo">Billing Month: <span style="color:green;"><?php echo "$month";?></span></p>

					<?php
						}
						elseif($type == "Gas")
						{
					?>
						<p class="pinfo">Company: <span style="color:green;"><?php echo "$company";?></span></p>
						<p class="pinfo">Meter No/Customer/Consumer No: <span style="color:green;"><?php echo "$number";?></span></p>
						<p class="pinfo"><b> Amount : </b><span style="color:green;">৳ <?php echo "$amount";?></span></p>						
						<p class="pinfo">Type: <span style="color:green;"><?php echo "$company_type";?></span></p>
						<p class="pinfo">Billing Month: <span style="color:green;"><?php echo "$month";?></span></p>

					<?php
						}
						elseif($type == "Water")
						{
					?>
						<p class="pinfo">Company: <span style="color:green;"><?php echo "$company";?></span></p>
						<p class="pinfo">Meter No/Customer/Consumer No: <span style="color:green;"><?php echo "$number";?></span></p>
						<p class="pinfo"><b> Amount : </b><span style="color:green;">৳ <?php echo "$amount";?></span></p>						
						<p class="pinfo">Type: <span style="color:green;"><?php echo "$company_type";?></span></p>
						<p class="pinfo">Billing Month: <span style="color:green;"><?php echo "$month";?></span></p>
												
						<?php
							}
							else
							{
								echo "Unknown";

							}
					    ?>	

  <?php 
	
	echo "
				<div class=\"form-group\">
				<input type=\"password\" name=\"pincode\" class=\"custom-form-control radius3\" placeholder=\"Enter your pin\" style=\"width:auto; font-size: 1.3rem;\" oninput=\"javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);\" maxlength = '6' required>
			  </div>
            </div>
				<input type=\"hidden\" name=\"number\" value=\"".Input::get('number')."\">
				<input type=\"hidden\" name=\"amount\" value=\"".Input::get('amount')."\">
				<input type=\"hidden\" name=\"type\" value=\"".$type."\">
				<input type=\"hidden\" name=\"company\" value=\"".$company."\">
				<input type=\"hidden\" name=\"company_type\" value=\"".$company_type."\">
				<input type=\"hidden\" name=\"cs_name\" value=\"".$cs_name."\">
				<input type=\"hidden\" name=\"month\" value=\"".$month."\">
				<input type=\"hidden\" name=\"contact_number\" value=\"".$contact_number."\">
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	