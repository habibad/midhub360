<?php
include "login.check.php";
include "class/Session.php";
include "../function.php";
//include "class/Input.php";
   
   $number  = Input::get('number');
   $amount = Input::get('amount');
   $type    = Input::get('type');
   
   if($type == '1'){
		  $typeName = 'prepaid';
	}elseif($type == '2'){
		  $typeName = 'postpaid';
	}
	elseif($type == '3'){
		  $typeName = 'skitto';
	}
	
	echo "
		<table style=\"width:100%;\">
			  <tr>
			  <td style=\"vertical-align:middle;padding: 0 .5rem;\">
			  <font size=\"+0.5\">
			  <div class=\"alert alert-warning\" role=\"alert\">Sending Flexiload ... Are you sure?</div>
				<p class=\"pinfo\"><b> Number : </b><span style=\"color:green;\">".Input::get('number')."</span></p>
				<p class=\"pinfo\"><b> Amount : </b><span style=\"color:green;\">৳".number_format(Input::get('amount'),2)."</span></p>
				<p class=\"pinfo\"> <b>Type : </b><span style=\"color:green;\">".$typeName."</span></p>				
			</font>
				</label>
			  <div class=\"form-group\">
				<input type=\"password\" name=\"pincode\" class=\"custom-form-control radius3\" placeholder=\"Enter your pin\" style=\"width:auto; font-size: 1.3rem;\" oninput=\"javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);\" maxlength = '6' required>
			  </div>
            </div>
				<input type=\"hidden\" name=\"number\" value=\"".Input::get('number')."\">
				<input type=\"hidden\" name=\"amount\" value=\"".Input::get('amount')."\">
				<input type=\"hidden\" name=\"type\" value=\"".$typeName."\">
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	