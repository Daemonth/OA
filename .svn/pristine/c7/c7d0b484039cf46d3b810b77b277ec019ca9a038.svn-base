<?php
namespace Oa\Tools;
class  StaticInfo
{
    public static function getAge($identify)
    {
        if(empty($identify)){
           return '';
        }
        $identify = substr($identify,6,8);
        $year = substr($identify,0,4);
        $month = substr($identify,4,2);
        $day = substr($identify,6,2);
        $now = date('Y-m-d');
        $nowDate = explode('-',$now);
        $yearDiff = $nowDate[0]-$year;
        if($nowDate[1] - $month < 0)
        {
            $yearDiff--;
        }elseif($nowDate[1] - $month == 0)
        {
            if($nowDate[2] - $day < 0)
                $yearDiff--;
        }
        return $yearDiff;
    }
}