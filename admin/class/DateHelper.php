<?php
class DateHelper
{

      public static function today()
      {
            $dt = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
            return $dt->format('Y-m-d');
      }

      public static  function timeToday()
      {
            $dt = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
            return $dt->format('Y-m-d H:i:s');
      }

      public static function getGtmDate($gtm = '')
      {
            $format      = date('F j Y g:i a', time() + 6*3600);
            $dateTime    = gmdate('Y-m-d H:i:s', time()+$gtm*60*60);
            $idate       = gmdate('Y-m-d', time()+$gtm*60*60);

            return array(
                        'format' => $format,
                        'time'   => $dateTime,
                        'date'   => $idate
                  );
      }

      public static function gtm($gtm = '', $format='')
      {
            return self::getGtmDate($gtm)[$format];
      }
}
