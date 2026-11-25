<?php
class User
{
    public static function isOnline()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql = "SELECT * from user_online WHERE ip='$ip'";

        $userOnline = DB::getInstance()->get($sql);

        if ($userOnline->count() > 0)
        {
            foreach ($userOnline->result() as $res)
            {
                foreach (json_decode($res->session) as $key => $value)
                {
                    Session::set($key, $value);
                }

                return true;
            }
        }

        return false;
    }
}