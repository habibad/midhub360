<?php
include "login.check.php";
include "class/Session.php";
include "../function.php";
//include "class/Input.php";
   
   $id  	= Input::get('id');
   //$number 	= Input::get('number');
   //$amount  = Input::get('amount');
   //$ucid  	= Input::get('ucid');
   //$company = Input::get('company');

   $query  = "SELECT * from send_query where id='$id'";    
    
   $result = mysqli_query($connect, $query);
   $data= mysqli_fetch_object($result);

   $number 	= $data->phone;
   $amount 	= $data->balance;
   $company = $data->operator;
   $ucid 	= $data->user_id;
   
	
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
				<input type=\"hidden\" name=\"id\" value=\"".$id."\">
				<input type=\"hidden\" name=\"number\" value=\"".$number."\">
				<input type=\"hidden\" name=\"amount\" value=\"".$amount."\">
				<input type=\"hidden\" name=\"ucid\" value=\"".$ucid."\">
				<input type=\"hidden\" name=\"company\" value=\"".$company."\">
				
			  <p class=\"help-block form_error\" style=\"font-size:11px;\"></p>
			  <p class=\"help-block line\"></p>
			
			  </td>
			  <td>
			  </tr>
			</table>
			";
?>



	