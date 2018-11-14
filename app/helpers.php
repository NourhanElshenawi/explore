<?php

/**
 *
 * Retrieve part of string
 *
 **/
use Nourhan\Database\DB;

//function after ($this, $inthat)
//{
//	if (!is_bool(strpos($inthat, $this)))
//	{
//		return substr($inthat, strpos($inthat, $this) + strlen($this));
//	}
//
//	return false;
//}
//
//function before ($this, $inthat)
//{
//	return substr($inthat, 0, strpos($inthat, $this));
//}
//
//function between ($this, $that, $inthat)
//{
//    return before($that, after($this, $inthat));
//}

function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    die();
}

function beautifyClassesForCalendar($classes) {
    foreach ($classes as $key=>$class) {

        $class['days']=array();

        if($class['monday']){
            $classes[$key]['days'][]="1";
        }
        if($class['tuesday']){
            $classes[$key]['days'][]="2";
        }
        if($class['wednesday']){
            $classes[$key]['days'][]="3";
        }
        if($class['thursday']){
            $classes[$key]['days'][]="4";
        }
        if($class['friday']){
            $classes[$key]['days'][]="5";
        }
    }

    return $classes;
}

/**
 * @param $classes
 * @return mixed
 */
function beautifyClasses($classes){

    $db = new DB();

    foreach ($classes as $key=>$class ){

        $currentCapacity = count($db->getClassCapacity($class['classID']));
        $temp = ($currentCapacity*100/$class['capacity']);


        $classes[$key]['currentCapacityPercentage'] = $temp;
        $classes[$key]['currentCapacity'] = $currentCapacity;

        $classes[$key]['users'] = $db->getRegisteredUsers($class['classID']);

        $class['days']=array();

        if($class['monday']){
            $classes[$key]['days']['monday']="1";
        } else {
            $classes[$key]['days']['monday']="0";
        }
        if($class['tuesday']){
            $classes[$key]['days']['tuesday']="1";
        }else {
            $classes[$key]['days']['tuesday']="0";
        }
        if($class['wednesday']){
            $classes[$key]['days']['wednesday']="1";
        }else {
            $classes[$key]['days']['wednesday']="0";
        }
        if($class['thursday']){
            $classes[$key]['days']['thursday']="1";
        }else {
            $classes[$key]['days']['thursday']="0";
        }
        if($class['friday']){
            $classes[$key]['days']['friday']="1";
        }else {
            $classes[$key]['days']['friday']="0";
        }
    }

    return $classes;
}

/**
 * @param $programRequests
 * @return mixed
 */
function convertGoalsList($programRequests){
    foreach ($programRequests as $key=>$request){
        $goal = array();
        $goal['develop Muscle Strength']= $request['developMuscleStrength'];
        $goal['rehabilitate Injury'] = $request['rehabilitateInjury'];
        $goal['overall Fitness'] = $request['overallFitness'];
        $goal['loseBody Fat'] = $request['loseBodyFat'];
        $goal['start Exercise Program'] = $request['startExerciseProgram'];
        $goal['design Advance Program'] = $request['designAdvanceProgram'];
        $goal['increase Flexibility'] = $request['increaseFlexibility'];
        $goal['sports Specific Training'] = $request['sportsSpecificTraining'];
        $goal['increase MuscleSize'] = $request['increaseMuscleSize'];
        $goal['cardio Exercise'] = $request['cardioExercise'];
        asort($goal);
        $programRequests[$key]['goals'] = $goal;
    }
    return $programRequests;
}

/**
 * @param $array
 * @return array
 */
function convertJoinDBReturns($array){
    $arrayReturn = array();
    foreach ($array as $arr){
        foreach ($arr as $r){
            $arrayReturn[]= $r;
        }
    }

    return $arrayReturn;
}

/**
 * @param $date1
 * @param $date2
 * @return mixed
 */
function daysDateDifference($date1, $date2){
    $days = date_diff(date_create($date1), date_create($date2))->days;

    return $days;
}

/**
 * @param $date1
 * @param $date2
 * @return bool|DateInterval
 */
function TimeDifference($date1, $date2){
    $diff = date_diff(date_create($date1), date_create($date2));

    return $diff;
}

/**
 * @param $logTimes
 * @return array
 */
function LogTimeAnalysis($logTimes){

    $hours = array();
    $minutes = array();
    $seconds = array();

    foreach ($logTimes as $log){
        $date1 = date("Y-m-d")." ". $log['TIME (logout)'];
        $date2 = date("Y-m-d")." ". $log['TIME (login)'];
        $diff = TimeDifference($date1, $date2);
        $hours[] = $diff->h;
        $minutes[] = $diff->i;
        $seconds[] = $diff->s;
    }


    $totalHours = array_sum($hours);

    if (count($hours) == 0){
        $hours[] = 0;
        $avgHours = 0;
    } else {
        $avgHours = $totalHours/count($hours);
    }

    $totalMins = array_sum($minutes);

    if (count($minutes) == 0){
        $minutes[] = 0;
        $avgMins = 0;
    } else {
        $avgMins = $totalMins/count($minutes);
    }

    $totalSecs = array_sum($seconds);

    if (count($seconds) == 0){
        $seconds[] = 0;
        $avgSecs = 0;
    } else {
        $avgSecs = $totalSecs/count($seconds);
    }

    $min = array(
        'hours' => min($hours),
        'mins' => min($minutes),
        'secs' => min($seconds)
    );
    $avg = array(
        'hours' => $avgHours,
        'mins' => $avgMins,
        'secs' => $avgSecs
    );
    $max = array(
        'hours' => max($hours),
        'mins' => max($minutes),
        'secs' => max($seconds)
    );

    $analysis = array(
        'min'=> $min,
        'avg'=>$avg,
        'max'=> $max
    );

    return $analysis;
}


/**
 * @param $logMonths
 * @return array
 */
function LogMonthAnalysis($logMonths){

    $logMonths = convertJoinDBReturns($logMonths);
    $logMonths = array_count_values($logMonths);

    if(count($logMonths) == 0){
        $logMonths[]= 0 ;
    }

    $analysis = array(
        'min'=> min($logMonths),
        'avg'=> array_sum($logMonths)/count($logMonths),
        'max'=> max($logMonths)
    );

    return $analysis;
}


/**
 * @param array $months An array containing Months in number format
 * @return array The Initial array of months in letter format
 */

function convertMonths($months)
{
    foreach ($months as $monthNum=>$value){

        //convert month number to name
        $dateObj   = \DateTime::createFromFormat('!m', $monthNum);
        //use name of the month as the new key of the array
        $months[$dateObj->format('F')] = $months[$monthNum]; // March
        //unset old array key
        unset($months[$monthNum]);

    }
    return $months;
}

function convertDays($days)
{
    $dayConverter = array(
        1 => 'Sunday',
        2 => 'Monday',
        3 => 'Tuesday',
        4 => 'Wednesday',
        5 => 'Thursday',
        6 => 'Friday',
        7 => 'Saturday'
    );

    foreach ($days as $dayNum=>$value){
        //convert day number to name
        //use name of the month as the new key of the array
        $days[$dayConverter[$dayNum]] = $days[$dayNum];
        //unset old array key
        unset($days[$dayNum]);

    }
    return $days;
}

function convertHours($hours){

    foreach ($hours as $key=>$hour){
        $hours[$key] = $hour.':00';
    }

    return $hours;
}

function convertDayToNum($day){
    $dayConverter = array(
        1 => 'sunday',
        2 => 'monday',
        3 => 'tuesday',
        4 => 'wednesday',
        5 => 'thursday',
        6 => 'friday',
        7 => 'saturday'
    );

    $testValue = array_search(strtolower($day),$dayConverter,false);

    if($testValue == false){
        return false;
    } else {
        return $testValue;
    }
}

function convertAgesToAgeGroups($ages){
    $ageGroups = array(
        '19-'=>0,
        '20-29'=>0,
        '30-39'=>0,
        '40+'=>0
    );
    foreach ($ages as $key=>$value){
        if($key <=19){
            $ageGroups['19-'] = $ageGroups['19-'] +$value;
        } else if($key > 20 && $key <=29){
            $ageGroups['20-29'] = $ageGroups['20-29'] +$value;
        } else if($key > 29 && $key <=39){
            $ageGroups['30-39'] = $ageGroups['30-39'] +$value;
        } else if($key > 39){
            $ageGroups['40+'] = $ageGroups['40+'] +$value;
        }
    }

    return $ageGroups;
}


function isLoggedIn(){
    return isset($_SESSION['user']);
}

function isAdmin(){
    return isset($_SESSION['user']['admin']);
}


