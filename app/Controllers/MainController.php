<?php
namespace Nourhan\Controllers;

use Nourhan\Database\DB;
use Nourhan\ReCaptcha;


class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
/*****HOME*****/
    public function index()
    {
        echo $this->twig->render('login.twig');
    }

    public function calendar()
    {
        echo $this->twig->render('calendar.twig');
    }

    public function fitnessProgram()
    {
        $db = new DB();
        $classes = $db->getClasses();

        $classes = $this->beautifyClassesForCalendar($classes);

        echo $this->twig->render('fitnessProgram.twig', array('classes'=> $classes));
    }

    public function beautifyClassesForCalendar($classes) {
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
     * Workout Program
     */

    public function deleteProgramRequest()
    {
        $db = new DB();
        echo json_encode($db->deleteProgramRequest($_POST['id']));
    }

  /** Admin **/
    public function userStats(){

        $db = new DB();
        $ageDB = $db->getUsersAge();

        $age = array_count_values(convertJoinDBReturns($ageDB));
        $age = convertAgesToAgeGroups($age);

        $genderDB = $db->getUsersGender();
        $gender = convertJoinDBReturns($genderDB);
        $gender = array_count_values($gender);




        echo $this->twig->render('admin/userStats.twig', array('age'=>$age, 'gender'=> $gender));
    }

    /***********LOGS**************/

    public function realtimeLogs(){
        $db = new DB();

        if (isset($_GET['keyword'])){
            $users = $db->searchRealTimeUsers($_GET['keyword']);
        } else{
            $users = $db->getRealtimeLogs();
        }

        echo $this->twig->render('realtimeLogs.twig', array('users'=>$users));
    }



}