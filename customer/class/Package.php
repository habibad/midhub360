<?php
class Package
{
	public static function oparetor()
	{
		return DB::getInstance()->getTable('operator')->result();
	}

	/* add package operator */
	public static function addOparetor()
	{
		$data =[
			'opname' => Xss::xssClean(Input::get('operator')),
			'status' => 1
		];
		$insert = DB::getInstance()->insert('package_op', $data);
		if($insert){
			Session::Flush('success', 'Add Package Operator Successfully');
			Redirect::to('recharge-package.php');
		}else{
			Session::Flush('error', 'Please Try Again');
			Redirect::to('recharge-package.php');
		}
	}

	/* delete package operator */
	public static function deleteOpt()
	{
		# get variable from ajax post
		$id = Input::get('id');
		$sql = "DELETE FROM package_op WHERE id = $id";
		$dle = DB::getInstance()->get($sql);
	}

	/* end this function */
	public static function packageList()
	{
		$sql = "SELECT * FROM package_list ORDER BY id desc";
		return DB::getInstance()->get($sql)->result();
	}
	/* end this function */

	/* Add package */
	public static function addPackage()
	{
		$data = [
			'opname' => Input::get('operator'),
			'pk_name'=> Input::get('title'),
			'amount' => Input::get('amount')
		];
		$insert = DB::getInstance()->insert('package_list', $data);
		if($insert){
			Session::Flush('success', 'Add Package Successfully');
			Redirect::to('packagemanage.php');
		}else{
			Session::Flush('error', 'Please Try Again');
			Redirect::to('packagemanage.php');
		}
	}
	/* end this function */

	/**
	*	get operator name
	*	@param  $id | null
	*	@return String
	*/

	public static function onName($id ='')
	{
		$sql = "SELECT * FROM package_op WHERE id = $id";
		$res = DB::getInstance()->get($sql)->result();
		foreach ($res as $op):
			return $op->opname;
		endforeach;
	}
	/* end this function */

	public static function delPackage()
	{
		$id = Input::get('id');
		$sql = "DELETE FROM package_list WHERE id = $id";
		$dle = DB::getInstance()->get($sql);
	}

	/* get package  */
	public static function getPackage()
	{
		$opid = Input::get('val');
		$sql = "SELECT * FROM package WHERE operator_id = '$opid'";
		echo '<option value="">Select Package</option>';
		foreach(DB::getInstance()->get($sql)->result() as $pk){
			
			echo '<option value="'.$pk->amount.'"> '.$pk->package_name.'</option>';
		}
	}
	
	public static function getPkg($opid)
	{
		$sql = "SELECT * FROM package WHERE operator_id = '$opid' and pkg_type='1' and status='1' ORDER BY id DESC";
		
		return DB::getInstance()->get($sql)->result();
	}

	public static function minute_getPkg($opid)
	{
		$sql = "SELECT * FROM package WHERE operator_id = '$opid' and pkg_type='2' and status='1' ORDER BY id DESC";
		
		return DB::getInstance()->get($sql)->result();
	}

	public static function getdrive($opid)
	{
		$sql = "SELECT * FROM drive WHERE operator_id = '$opid' and status='1' ORDER BY id DESC";
		
		return DB::getInstance()->get($sql)->result();
	}

}