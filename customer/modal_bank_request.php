<?php
include "login.check.php";
include "class/Session.php";
include "class/Input.php";
   
   $acnumber        = Input::get('acnumber');
   $amount          = Input::get('amount');
   $branch          = Input::get('branch');
   $bank_name       = Input::get('bankname');
   $ac_type         = Input::get('ac_type');   
   $acname          = Input::get('acname');
   $contact_number  = Input::get('contact_number');
   $note  			= Input::get('note');
  
   
  ?>

<table style="width:100%;">
			  <tr>
			  <td style="vertical-align:middle;padding: 0 .5rem;">
			  
			  
			  		<div class="alert alert-warning" role="alert">Bank Payment... Are you sure?</div>

			  			<p class="pinfo">Bank Name: <span style="color:green;"><?php echo $bank_name;?></span></p>
						<p class="pinfo">Account Name: <?php echo $acname;?></p>
						<p class="pinfo"><b>Account Number: <?php echo $acnumber;?></p>						
						<p class="pinfo"><b>Account Type: <?php echo $ac_type;?></p>						
						<p class="pinfo"><b> Amount : </b><span style="color:green;">৳ <?php echo $amount;?></span></p>						

  <?php 
	
	echo "
				<div class=\"form-group\">
				<input type=\"password\" name=\"pincode\" class=\"custom-form-control radius3\" placeholder=\"Enter your pin\" style=\"width:auto; font-size: 1.3rem;\" oninput=\"javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);\" maxlength = '6' required>
			  </div>
            </div>
				<input type=\"hidden\" name=\"bankname\" value=\"".$bank_name."\">
				<input type=\"hidden\" name=\"acname\" value=\"".$acname."\">
				<input type=\"hidden\" name=\"acnumber\" value=\"".$acnumber."\">
				<input type=\"hidden\" name=\"amount\" value=\"".$amount."\">
				<input type=\"hidden\" name=\"branch\" value=\"".$branch."\">
				<input type=\"hidden\" name=\"ac_type\" value=\"".$ac_type."\">
				<input type=\"hidden\" name=\"note\" value=\"".$note."\">
				<input type=\"hidden\" name=\"contact_number\" value=\"".$contact_number."\">
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	