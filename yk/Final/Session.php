<?php

/**
 *   class name session
 *   author Rotun Ahmed
 */
class Session
{
    /**
     * @param $userdata
     * @return bool
     * session if isset()
     */
    public static function exists($userdata)
    {
        return (isset($_SESSION[$userdata])) ? true : false;
    }

    /**
     * @param $userdata
     * @param $value
     * @return mixed
     * session set
     */
    public static function set($userdata, $value)
    {
        return $_SESSION[$userdata] = $value;
    }

    /**
     * @param $userdata
     * @return mixed
     * session get
     */
    public static function get($userdata)
    {
        return isset($_SESSION[$userdata]) ? $_SESSION[$userdata] : "";
    }

    /**
     *  @param $userdata
     *  unset session
     */
    public static function unsetSession($userdata)
    {
        if (self::exists($userdata)) {
            unset($_SESSION[$userdata]);
        }
    }

    /**
     * @param $name
     * @param string $string
     * @return mixed
     * flush session data
     */
    public static function Flush($name, $string = '')
    {
        if (self::exists($name)) {
            $session = self::get($name);
            self::unsetSession($name);
            return $session;
        } else {
            self::set($name, $string);
        }
    }
}

//end class
