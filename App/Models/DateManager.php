<?php

namespace App\Models;
use PDO;
use \Core\View;

class DateManager extends \Core\Model
{   
	public function __construct( $data = [] ) 
	{
        foreach ( $data as $key => $value ) 
        {
            $this->$key = $value;
        }
    }

    public static function getCurrentMonthDate()
    {
        $month=date("m");    
        $year=date("Y");
        $day=cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $date = static::getFirstSecondDate($year,$month,$day);
        return $date;
    }
    
    public static function getFirstSecondDate($year,$month,$day)
    {      
        if($month == "year")
        {
            $start_date="$year"."-"."01-01";
            $end_date="$year"."-"."12-31";
        }
        else
        {
            $start_date="$year"."-"."$month"."-"."01";
            $end_date="$year"."-"."$month"."-".$day;
        }        
        
        $date =['start_date'=>$start_date,
                'end_date'=>$end_date ];        
        return $date;      
    }           

    public static function getLastMonthDate()
    {     
        $m=date("m");
        if($m=="01")
        {
            $month="12";
            $year = date("Y")-1;
        }
        else
        {
            $month = date($m)-1;
            $year = date("Y");
        }

        $day=cal_days_in_month(CAL_GREGORIAN,$month,$year);
        if($month<10)
        {
          $month="0".$month;
        }

        $date = static::getFirstSecondDate($year,$month,$day);
        return $date;        	
    }

    public static function getCurrentYearDate()
    {
        $year=date("y");
        $month="year";
        $day="31";
        $date = static::getFirstSecondDate($year,$month,$day);
        return $date; 
    }
    public static function getUserSelectedDate($arg1='',$arg2='')
    {
        $date = 
        [
        'start_date' => isset($_POST['start_date']) ? $_POST['start_date']: $arg1,
        'end_date' => isset($_POST['end_date']) ? $_POST['end_date']: $arg2
        ];
        
        return $date;
    }  
}
