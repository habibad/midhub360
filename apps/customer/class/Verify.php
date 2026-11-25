<?php
class Verify
{
	/**
	* 	function verify reseller user name
	*	@param $tableField String | contain table_name.column_name
	*	@param $value Mixed
	*	@return boolean 
	*/
	public static function isExist($tableField, $value)
	{
		$table = explode('.', $tableField);
		if(count($table) > 1 ){
			$tableName = $table[0];
			$field = $table[1];

			$sql = "SELECT * FROM {$tableName} WHERE {$field} = '$value'";
			$row = DB::getInstance()->get($sql)->Count();
			if($row > 0){
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}

	/**
	*	reseller or admin pin verify
	*	@param pin | var mixed
	*	@return boolean
	*/
	public static function pin($pin = '')
	{
		$userId = Session::get('us_id');
		$sql = "SELECT pin_code FROM user WHERE id = $userId";
		$getPin = DB::getInstance()->get($sql)->result();
		foreach ($getPin as $code):
			$pincode = $code->pin_code;
		endforeach;
		if(md5($pin) == $pincode){
			return true;
		}else{
			return false;
		}
	}
	// end pin verification function

	public static function scure($index = '')
	{
		$secutity = [];
		$setting = DB::getInstance()->get("SELECT * FROM security_option WHERE id = 1")->result();
		foreach ($setting as $scure):
			$secutity['1'] = $scure->admin_capcha;
			$secutity['2'] = $scure->reseller_capcha;
			$secutity['3'] = $scure->pin_exp;
			$secutity['4'] = $scure->min_pin;
			$secutity['5'] = $scure->req_Interval;
			$secutity['6'] = $scure->support;
			$secutity['7'] = $scure->OTP;
			$secutity['8'] = $scure->rlverify;
			$secutity['9'] = $scure->bulk_flexi_limit;
			$secutity['10'] = $scure->reseller_OverPayment;
			$secutity['11'] = $scure->reseller_dayli_limit;
			$secutity['12'] = $scure->illegal;
		endforeach;
		return $secutity[$index];
	}
	/* get all secure data */

	/* domain license verification */
	public static function licenseVerify($domain)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"localhost/licence/api.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		            "domain=".$domain);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
		if($server_output == 'inActive'){
		    return true;
		}else{
			return false;
		}
	}

}