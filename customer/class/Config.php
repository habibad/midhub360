<?php
class Config
{

    /**
     *  @param null $path
     *  @return bool|mixed
     *  function Configuration
     *  used Config::get('mysql/host')
     *
     */
    public static function get($path = null)
    {
        if ($path) {
            require_once '../config/config.php';
            $config = $GLOBALS['config'];
            $path   = explode('/', $path);
            foreach ($path as $bit) {
                if (isset($config[$bit])) {
                    $config = $config[$bit];
                }
            }
            return $config;
        }
        return false;
    }
}
