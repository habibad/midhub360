<?php

class Brand
{
	public static function customBrand()
	{
		$company = Input::get( 'company' );
		$title = Input::get( 'title' );
		$footer = Input::get( 'footer' );
		$android_link = Input::get( 'android_link' );
		$ios_link = Input::get( 'ios_link' );
		$meta = Input::get( 'meta' );
		$keyword = Input::get( 'keyword' );
		$logo = Input::get( 'logo' );
		$login_theme = Input::get( 'login_theme' );
		$data = [
		    'company'      => $company,
		    'title'        => $title,
		    'footer'       => $footer,
		    'android_link' => $android_link,
		    'ios_link'     => $ios_link,
		    'meta'         => $meta,
		    'keyword'      => $keyword,
		    'logo'         => $logo,
		    'login_theme'  => $login_theme,
		    'userid'       => Session::get( 'userId' ),
		    'created'      => DateHelper::today()
		];
		//print_r($data);
		$insert = DB::getInstance()->insert('custom_brand', $data );
		if ( $insert ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	# band if exits custom brand
	public static function isBrand()
	{
		$userId = Session::get( 'userId' );
		$get = DB::getInstance()->get( "SELECT * FROM custom_brand WHERE  userid = '$userId'" );
		$count = $get->Count();
		if ( $count > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/* custom brand */
	public static function updateCustomBrand()
	{
		$id = Input::get('id');
		$company    = Input::get( 'company' );
		$title      = Input::get( 'title' );
		$footer     = Input::get( 'footer' );
		$android_link = Input::get( 'android_link' );
		$ios_link   = Input::get( 'ios_link' );
		$meta       = Input::get( 'meta' );
		$keyword    = Input::get( 'keyword' );
		$logo       = Input::get( 'logo' );
		$login_theme = Input::get( 'login_theme' );
		$data = [
		    'company'      => $company,
		    'title'        => $title,
		    'footer'       => $footer,
		    'android_link' => $android_link,
		    'ios_link'     => $ios_link,
		    'meta'         => $meta,
		    'keyword'      => $keyword,
		    'logo'         => $logo,
		    'login_theme'  => $login_theme,
		];
		$update = DB::getInstance()->update('custom_brand', $id, $data );
		if ( $update ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function setBand()
	{
		if(self::isBrand() === TRUE){
			$set = self::updateCustomBrand();
		}else{
			$set = self::customBrand();
		}
		if($set){
			Session::flush('success', ' Custom Branding Successfully');
			Redirect::to('custom-brand.php');
		}else{
			Session::flush('error', ' Please Try Again');
			Redirect::to('custom-brand.php');
		}
	}

	/*  get custom branding information */
	public static function getCustomBrand($index = '')
	{
		$branding = [];
		$userId = Session::get('userId');
		$sql    = "SELECT * FROM custom_brand WHERE userid = '$userId'";
		$res = DB::getInstance()->get($sql)->result();
		$count = Db::getInstance()->get($sql)->count();
		if($count > 0) {
			foreach ( $res as $brand ) {
				$branding[ 'id' ] = $brand->id;
				$branding[ 'company' ] = $brand->company;
				$branding[ 'title' ] = $brand->title;
				$branding[ 'footer' ] = $brand->footer;
				$branding[ 'android_link' ] = $brand->android_link;
				$branding[ 'ios_link' ] = $brand->ios_link;
				$branding[ 'meta' ] = $brand->meta;
				$branding[ 'keyword' ] = $brand->keyword;
				$branding[ 'logo' ] = $brand->logo;
				$branding[ 'theme' ] = $brand->login_theme;
			}
		}
		return $branding[$index];
	}

	/* insert custom home notice */
	public static function insertCustomHome()
	{
		$data = [
		    'notice'  => trim(Input::get('notice')),
		    'userid'  => Session::get('userId'),
		    'created' => DateHelper::today(),
		];
		$insert = DB::getInstance()->insert('custom_home', $data);
		return $insert ? TRUE : FALSE;
	}

	/* update home notice */
	public static function updateCustomHome()
	{
		$notice = trim(Input::get('notice'));
		$userId=  Session::get('userId');
		$sql = "UPDATE custom_home SET notice = '$notice' WHERE  userid= '$userId'";
		$update = DB::getInstance()->get($sql);
		return $update ? TRUE : FALSE;
	}

	/* if sure customHome is set */
	public static function isHome()
	{
		$user = Session::get('userId');
		$sql = "SELECT * FROM custom_home WHERE userid = '$user'";
		$isHome = DB::getInstance()->get($sql)->Count();
		if($isHome > 0){
			return TRUE;
		}else{
			return FALSE;
		}

	}

	/* set custom home notice */
	public static function setCustomHomeNotice()
	{
		if(self::isHome() === TRUE){
			$set = self::updateCustomHome();
		}elseif(self::isHome() === FALSE){
			$set = self::insertCustomHome();
		}

		/* if success */
		if($set){
			Session::Flush('success', 'Home Notice Save Successfully');
			Redirect::to('custom-home-notice.php');
		}else{
			Session::Flush('error', 'Please Try Again');
			Redirect::to('custom-home-notice.php');
		}
	}

	/* login notice  */
	public static function insertCustomLogin()
	{
		$data = [
		    'notice' => trim(Input::get('notice')),
		    'userid' => Session::get('userId'),
		    'created' => DateHelper::today(),
		];
		$insert = DB::getInstance()->insert('custom_login', $data);
		return $insert ? TRUE : FALSE;
	}

	/* update custom login */
	public static function updateCustomLogin()
	{
		$userId = Session::get('userId');
		$notice = trim(Input::get('notice'));
		$sql = "UPDATE custom_login SET notice = '$notice' WHERE userid= '$userId'";

		$update = DB::getInstance()->get($sql);
		return $update ? TRUE : FALSE;
	}

	# if custom login notice is set
	public static function isNotice()
	{
		$userId = Session::get('userId');
		$sql = "SELECT * FROM custom_login WHERE  userid = '$userId'";
		$count = DB::getInstance()->get($sql)->Count();
		if($count > 0){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/* set custom login notice */
	public static function setCustomLoginNotice()
	{
		if(self::isNotice() === TRUE){
			$set = self::updateCustomLogin();
		}else{
			$set = self::insertCustomLogin();
		}

		if ($set){
			Session::Flush('success', 'Login Notice Save Successfully');
			Redirect::to('custom-login-notice.php');
		}else{
			Session::Flush('success', 'Please Try Again');
			Redirect::to('custom-login-notice.php');
		}
	}

	/* get custom login notic */

	public static function loginNotice($userid = '')
	{
		$noticeData = '';
		$sql = "SELECT * FROM custom_login WHERE userid= '$userid'";
		$res = DB::getInstance()->get($sql)->result();
		foreach ($res as $notice):
			$noticeData .= $notice->notice;
		endforeach;
		return $noticeData;
	}
	/* get custom home notice */
	public static function homeNotice($userid = '')
	{
		$noticeData = '';
		$sql = "SELECT * FROM custom_home WHERE userid= '$userid'";
		$res = DB::getInstance()->get($sql)->result();
		foreach ($res as $notice):
			$noticeData .= $notice->notice;
		endforeach;
		return $noticeData;
	}
	/* end this function */
	
	
	/* band name by domain */
	public static function brandByDomain()
	{
	    $domain = $_SERVER['SERVER_NAME'];
	    $resId = '';
	    $sql = "SELECT * FROM reseller WHERE domain_name = '$domain'";
	    $res = DB::getInstance()->get($sql)->result();
	    $isDomain = DB::getInstance()->get($sql)->Count();
	    if($isDomain > 0){
    	    foreach($res as $do){
    	        $resId .= $do->id;
    	    }
	    }
	    return $resId;
	} 
    /* end function */
    
    /* end this function */
    
    /* getBrand by domain */
    public static function getDomainBrand($index = '')
    {
        $brand = [];
        # redirect user
        $userId = self::brandByDomain();
        $sql = "SELECT * FROM custom_brand WHERE userid = '$userId'";
        $isBrand = DB::getInstance()->get($sql)->Count();
        $result = DB::getInstance()->get($sql)->result();
        if($isBrand > 0){
            foreach($result as $brandData){
                $brand['company'] = $brandData->company;
                $brand['title'] = $brandData->title;
                $brand['footer'] = $brandData->footer;
                $brand['android_link'] = $brandData->android_link;
                $brand['ios_link'] = $brandData->ios_link;
                $brand['meta'] = $brandData->meta;
                $brand['keyword'] = $brandData->keyword;
                $brand['logo'] = $brandData->logo;
            }
        }
        return $brand[$index];
    }
    /* end this function */
    
    /* function get login notice */
    
    /* get login notice if user domain is exits */
    public static function loginNoticeDomain()
    {
        $login = '';
        $user = self::brandByDomain();
        $sql = "SELECT * FROM  custom_login WHERE userid = '$user'";
        $count = DB::getInstance()->get($sql)->Count();
        
        $res = DB::getInstance()->get($sql)->result();
        if($count > 0){
            foreach($res as $lo){
                $login .=$lo->notice; 
            }
        }
        return $login;
    }

}
