<?php
include "login.check.php";
include "class/Session.php";
include "../function.php";
//include "class/Input.php";
   
   $id  	= Input::get('id');
   $username 	= Input::get('username');
   $amount  = Input::get('amount');
   $ucid  	= Input::get('ucid');
   $company = Input::get('company');
   $cost  	= Input::get('cost');
   $bltype  = Input::get('bltype');
   
   
	
	echo "
		<table style=\"width:100%;\">
			  <tr>
			  <td style=\"vertical-align:middle;padding: 0 .5rem;\">
			  <font size=\"+0.5\">
			  	<p class=\"pinfo\"> <b>SL. No : </b><span style=\"color:green;\">".$id."</span></p>			
				<p class=\"pinfo\"> <b>ID : </b><span style=\"color:green;\">".$ucid."</span></p>	
				<p class=\"pinfo\"> <b>Username : </b><span class=\"hist-number\">".$username."</span></p>
				<p class=\"pinfo\"> <b>Balance Type: </b><span class=\"hist-number\">".$bltype."</span></p>	
				<p class=\"pinfo\"> <b>Balance : </b><span class=\"hist-number\">".$amount."</span></p>
				
				

			</font>
		
			
            </div>
				<input type=\"hidden\" name=\"id\" value=\"".Input::get('id')."\">
				<input type=\"hidden\" name=\"ucid\" value=\"".Input::get('ucid')."\">
				<input type=\"hidden\" name=\"amount\" value=\"".Input::get('amount')."\">		
			
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	