<?php
include "login.check.php";
include "class/Session.php";
include "../function.php";
//include "class/Input.php";
   
   $id  	= Input::get('id');
   $number 	= Input::get('number');
   $amount  = Input::get('amount');
   $ucid  	= Input::get('ucid');
   $company  	= Input::get('company');
   
   
	
	echo "
		<table style=\"width:100%;\">
			  <tr>
			  <td style=\"vertical-align:middle;padding: 0 .5rem;\">
			  <font size=\"+0.5\">
			  				
				<p class=\"pinfo\"> <b>ID : </b><span style=\"color:green;\">".$id."</span></p>	
				<p class=\"pinfo\"> <b>Number : </b><span class=\"hist-number\">".$number."</span></p>	
				<p class=\"pinfo\"> <b>Amount : </b><span class=\"hist-number\">".$amount."</span></p>
				<p class=\"pinfo\"> <b>Operator : </b><span class=\"hist-number\">".$company."</span></p>
				

			</font>
		
			
            </div>
				<input type=\"hidden\" name=\"id\" value=\"".Input::get('id')."\">
				<input type=\"hidden\" name=\"number\" value=\"".Input::get('number')."\">
				<input type=\"hidden\" name=\"amount\" value=\"".Input::get('amount')."\">
				<input type=\"hidden\" name=\"ucid\" value=\"".Input::get('ucid')."\">
				<input type=\"hidden\" name=\"company\" value=\"".Input::get('company')."\">
				
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	