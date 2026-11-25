<?php
/**
 *  class name redirect
 *  this class for header location
 *  author Rotun Ahmed
 */
class Redirect
{
    /**
     * @param $location
     */
    public static function to($location)
    {
        if ($location) {
            if (is_numeric($location)) {
                switch ($location) {
                    case 404:
                        header('HTTP/1.0 404 Not Found ');
                        include 'include/errors/404.php';
                        exit();
                        break;
                }
            }
            Header('Location:' . $location);
        }
    }

}
