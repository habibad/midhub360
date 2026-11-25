<?php
//ini_set('memory_limit', '50G');
class Hash
{

    /**
     * @param $string
     * @param string $salt
     * @return string
     *  This Used Hash::make(@param $string)
     */
    public static function make($string, $salt = 'sfdihsfosewseiopvvpogpdrioesroitwfcsf')
    {
        return hash('sha256', $string . $salt);
    }

    /**
     * @param $length
     * @return string
     */
    public static function salt($length)
    {
        return mcrypt_create_iv($length);
    }

    public function unique()
    {
        return self::make(self::unique());
    }
}
