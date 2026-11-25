<?php
/**
 * Author Rotun Ahmed
 */
class Input
{
    /**
     * @param string $type
     * @return bool
     * if post or get method true
     */
    public static function exists($type = 'post')
    {
        switch ($type) {
            case 'post':
                return (!empty($_POST)) ? true : false;
                break;
            case 'get':
                return (!empty($_GET)) ? true : false;
                break;
            default:
                return false;
                break;
        }

    }

    /**
     * @param $item
     * @return string
     * get Post Data or Get Method data
     * this method used Input::get(@param  $item)
     */
    public static function get($item)
    {
        if (isset($_POST[$item])) {
            return $_POST[$item];
        } elseif (isset($_GET[$item])) {
            return $_GET[$item];
        }
        return '';
    }

    /**
     *  @param $input
     *  @return string
     *  clean all Input Field Data
     */
    public static function xssClean($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);

        return $input;
    }

}
