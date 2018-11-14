<?php
/**
 * Created by PhpStorm.
 * User: nourhan
 * Date: 20/11/2016
 * Time: 06:44
 */

namespace Nourhan\Controllers;

use Nourhan\Database\DB;

class AdminController extends Controller
{
    /***********ADMIN************/

    ////////EDIT CLASS SCHEDULE
    public function editSchedule()
    {
        if(isAdmin()) {
            $db = new DB();

            if(isset($_GET['keyword'])){
                $classes = $db->searchClasses($_GET['keyword']);
            } else{
                $classes = $db->getClasses();
            }
            $classes = beautifyClasses($classes);
            $allInstructors = $db->getInstructors();

            echo $this->twig->render('admin/editSchedule.twig', array('classes' => $classes, 'allInstructors' => $allInstructors));
        } else {
            redirect('/404');
        }
    }

    public function addClass()
    {

        $db = new DB();

        $classes = $db->getClasses();
        $classes = beautifyClasses($classes);
        $allInstructors = $db->getInstructors();

        $testingConflict = $db->searchClassesForConflict($_POST['instructorID'],$_POST['startTime'], $_POST['endTime'],
            $_POST['period'],$_POST['location'],(int)in_array('monday',$_POST['days']),
            (int)in_array('tuesday',$_POST['days']), (int)in_array('wednesday',$_POST['days']),
            (int)in_array('thursday',$_POST['days']), (int)in_array('friday',$_POST['days']));

        if ($testingConflict == false){

            $testInsert = $db->addClass($_POST['name'], $_POST['instructorID'], $_POST['startTime'], $_POST['endTime'],
                $_POST['period'],
                $_POST['capacity'],$_POST['location'],(int)in_array('monday',$_POST['days']),
                (int)in_array('tuesday',$_POST['days']), (int)in_array('wednesday',$_POST['days']),
                (int)in_array('thursday',$_POST['days']), (int)in_array('friday',$_POST['days']));
            if($testInsert){
                $result = "Class Successfully Added!";
            } else{
                $result = "There was an issue adding the class. please contact support!";
            }
        } else{
            $result = "Could not add class! There is a conflict with the following class(es): ";
                foreach ($testingConflict as $conflict){
                    if($conflict === end($testingConflict)){
                        $result = $result . $conflict['name'] . ".";
                    } else {
                        $result = $result . $conflict['name'] . ", ";
                    }
                }
        }
        echo $this->twig->render('admin/editSchedule.twig', array('classes'=> $classes, 'allInstructors'=> $allInstructors, 'result'=>$result));


    }

    public function searchClasses(){
        if(isAdmin()) {
            $db = new DB();

            $classes = $db->searchClasses($_GET['keyword']);
            $classes = beautifyClasses($classes);
            $allInstructors = $db->getInstructors();

            echo $this->twig->render('admin/editSchedule.twig', array('classes' => $classes, 'allInstructors' => $allInstructors));
        } else{
            redirect('/404');
        }
    }

    public function updateClass()
    {
        $DB = new DB();
        if($DB->updateClass($_POST['period'],$_POST['id'],$_POST['endTime'],$_POST['startTime'],$_POST['capacity'], $_POST['instructor']))
        {
            header('Location: /editschedule');
        }

    }

    public function deleteClass()
    {
        $db = new DB();
        $db->deleteClass($_POST['id']);
    }

    public function registrations()
    {
        if(isAdmin()) {
            $db = new DB();
            $classes = $db->getClasses();

            if (!isset($_GET['class'])) {
                echo $this->twig->render('admin/registrations.twig', array('classes' => $classes));
            } else {
                $users = $db->getRegisteredUsers($_GET['class']);
                $class = $db->getClass($_GET['class']);

                echo $this->twig->render('admin/registrations.twig', array('classes' => $classes, 'users' => $users, 'selectedClass' => $class));
            }
        } else{
            redirect('/404');
        }

    }


    /**
     * Edit Users
     */

    /**
     * @param $users
     * @return mixed -> all the registrations of the given users
     */

    /**
     * @param array $users An array of users
     * @return array The initial array of users with each user having an array of classes that they're registered fot
     */

    public function getUsersWithRegistrations($users)
    {
        $db = new DB();

        foreach ($users as $key=>$user){

            $users[$key]['classes']= $db->getUserRegistrations($user['id']);
        }

        return $users;
    }

    public function editUsers()
    {
        if(isAdmin()) {
            $db = new DB();

            $classes = $db->getClasses();

            if (isset($_GET['keyword'])) {

                $users = $db->searchUsers($_GET['keyword']);
            } else {
                $users = $db->getUsers();
            }

            $users = $this->getUsersWithRegistrations($users);

            echo $this->twig->render('admin/editUsers.twig', array('users' => $users, 'classes' => $classes));
        } else{
            redirect('/404');
        }

    }

    /**
     * Edit Instructors
     */
    public function editInstructors()
    {
        if(isAdmin()) {
            $db = new DB();
            $classes = $db->getClasses();

            if (isset($_GET['keyword'])) {

                $instructors = $db->searchInstructors($_GET['keyword']);

                foreach ($instructors as $key => $instructor) {
                    $instructors[$key]['classes'] = $db->getInstructorsClasses($instructor['id']);
                }
            } else {
                $instructors = $db->getInstructors();

                foreach ($instructors as $key => $instructor) {
                    $instructors[$key]['classes'] = $db->getInstructorsClasses($instructor['id']);
                }
            }
            echo $this->twig->render('admin/editInstructors.twig', array('instructors' => $instructors, 'classes' => $classes));
        } else{
            redirect('/404');
        }

    }

    public function updateInstructor()
    {
        $DB = new DB();

        if($DB->updateUser($_POST['id'],$_POST['name'],$_POST['email'],$_POST['password'], $_POST['birthDate'],
            $_POST['gender'], $_POST['membershipType'], $_POST['admin']))
        {
            header('Location: /editusers');
        }

    }

    public function addInstructor()
    {
        $db = new DB();

        $result = $db->addUser($_POST, $_FILES['picture']);
        if($result) {
            $newUser = $db->getUserByEmail($_POST['email']);
            $result = $db->addInstructor($newUser['id'], $_POST['specialty']);
        }

        echo $this->twig->render('admin/editInstructors.twig', ['result'=>$result]);
    }

    public function addInstructorByEmail()
    {
        $db = new DB();

        $user = $db->getUserByEmail($_POST['email']);
        if(!empty($user)) {
            $db->addInstructor($user['id'], $_POST['specialty']);
        }

        redirect('/editInstructors');
    }


    public function updateUser()
    {
        $DB = new DB();

        var_dump($_POST);


        if($DB->updateUser($_POST))
        {
            header('Location: /editusers');
        }

    }

    public function deleteUser()
    {
        $db = new DB();
        $db->deleteUser($_POST['id']);
    }

    public function addMultipleUsers()
    {
        $uploadService = new Upload();
        $db = new DB();

        $result = $uploadService->uploadFile($_FILES['users_file']);

        //If file was NOT uploaded show message to user and exit
        if ($result['success'] == false){
            echo $this->twig->render('admin/editUsers.twig', ['result'=>$result]);

            die();
        }

        $encodedUsers = file_get_contents(__DIR__.'/../storage/users.json');

        $decodedUsers = json_decode($encodedUsers);

        foreach ($decodedUsers as $user)
        {
            $result = $db->addMutlipleUsers($user);
        }

        echo $this->twig->render('admin/editUsers.twig', ['result'=>$result]);
    }

    public function addUser()
    {
        $db = new DB();

        $result = $db->addUser($_POST, $_FILES['picture']);

        echo $this->twig->render('admin/editUsers.twig', ['result'=>$result]);
    }

    ///////STATS
    public function stats(){

        if(isAdmin()) {
            //get how many times a day was repeated aka the number of visits per day by users
            $hours = array_count_values($this->getHourLogs());

            //get how many times a day was repeated aka the number of visits per day by users
            $days = array_count_values($this->getDayLogs());

            //get how many times a month was repeated aka the number of visits per month by users
            $months = array_count_values($this->getMonthsLogs());

            //get how many times a year was repeated aka the number of visits per year by users
            $years = array_count_values($this->getYearsLogs());

            //sort the array by year
            ksort($years);
            //sort the array by month number
            ksort($months);
            $months = convertMonths($months);
            //sort the array by day number
            ksort($days);
            $days = convertDays($days);

            echo $this->twig->render('admin/logStatistics.twig', array('years' => $years, 'months' => $months, 'days' => $days, 'hours' => $hours));
        } else {
            redirect('/');
        }
    }

    public function postStatsMonth(){

        //get how many times a month was repeated aka the number of visits per month by users
        $months = array_count_values($this->getMonthsLogsFilter());
        //sort the array by month number
        ksort($months);

        $months = convertMonths($months);

        echo json_encode($months);
    }

    public function postStatsYear(){

        //get how many times a month was repeated aka the number of visits per month by users
        $years = array_count_values($this->getYearsLogsFilter());
        //sort the array by month number
        ksort($years);

        echo json_encode($years);
    }

    public function postStatsDay(){

        //get how many times a month was repeated aka the number of visits per month by users
        $days = array_count_values($this->getDaysLogsFilter());
        //sort the array by month number
        ksort($days);
        $days = convertDays($days);

        echo json_encode($days);
    }

    public function postStatsHour(){

        //get how many times a month was repeated aka the number of visits per month by users
        $hours = array_count_values($this->getHoursLogsFilter());
        //sort the array by month number
        ksort($hours);

        echo json_encode($hours);
    }

    public function getYearsLogs()
    {
        $db = new DB();
        //create an array with years as string values
        $yearsDB = $db->getUsersLogsYears();

        foreach ($yearsDB as $year){
            foreach ($year as $r){
                $years[]= $r;
            }
        }
        return $years;
    }

    public function getMonthsLogs()
    {
        $db = new DB();

        $monthsDB = $db->getUsersLogsMonths();

        //create an array with months as string values
        $months = convertJoinDBReturns($monthsDB);

        return $months;
    }

    public function getDayLogs()
    {
        $db = new DB();
//        $logs = $db->getUsersLogs();
        $dayLogs = $db->getUsersLogsDays();
        $days =convertJoinDBReturns($dayLogs);

        return $days;
    }

    public function getHourLogs()
    {
        $db = new DB();
        $hourLogs = $db->getUsersLogsHours();
        $hours = convertJoinDBReturns($hourLogs);
        //sort to start array with earliest hour
        asort($hours);
        //add :00 next to hour to produce make it more natural looking
        $hours = convertHours($hours);

        return $hours;
    }

    public function getYearsLogsFilter()
    {
        $db = new DB();
        $yearsDB = $db->getUsersLogsYearsFilter();
        //create an array with months as string values
        $years = convertJoinDBReturns($yearsDB);
        return $years;
    }

    public function getMonthsLogsFilter()
    {
        $db = new DB();
        $monthsDB = $db->getUsersLogsMonthsFilter();
        $months = array();
        //create an array with months as string values
        foreach ($monthsDB as $mon){
            foreach ($mon as $r){
                $months[]= $r;
            }
        }
        return $months;
    }

    public function getDaysLogsFilter()
    {
        $db = new DB();

        $daysDB = $db->getUsersLogsDaysFilter();
        $days = array();
        //create an array with months as string values
        foreach ($daysDB as $day){
            foreach ($day as $r){
                $days[]= $r;
            }
        }
        return $days;
    }

    public function getHoursLogsFilter()
    {
        $db = new DB();

        $hoursDB = $db->getUsersLogsHoursFilter();
        $hours = convertJoinDBReturns($hoursDB);


        //sort to start array with earliest hour
        asort($hours);
        //add :00 next to hour to produce make it more natural looking
        $hours = convertHours($hours);

        return $hours;
    }

    /**
     * @return array
     */
    public function getRegistrations()
    {
        $db = new DB();

        $registrations = convertJoinDBReturns($db->getClassesRegistrations());

        return $registrations;
    }

    public function registrationStats()
    {
        //get how many times a day was repeated aka the number of visits per day by users
        $classRegistrations = array_count_values($this->getRegistrations());
        ddd($classRegistrations);

        //sort the array by year
        ksort($classRegistrations);

        ddd($classRegistrations);

        echo $this->twig->render('admin/classStats.twig', array('classRegistrations'=>$classRegistrations));
    }

    //Logs
    public function usersLogs(){

        $db = new DB();
        if(isset($_GET['keyword'])){
            if(convertDayToNum($_GET['keyword']) != false){
                $logs = $db->getLogsByKeyword(convertDayToNum($_GET['keyword']));
            } else{
                $logs = $db->getLogsByKeyword($_GET['keyword']);
            }
        }
        else {
            $logs = $db->getUsersLogs();
        }
        echo $this->twig->render('admin/logs.twig', array('logs'=>$logs));
    }

    public function manualLogUser()
    {
        $db = new DB();

        if(isset($_GET['keyword'])){

            $users = $db->searchUsers($_GET['keyword']);
            foreach ($users as $key=>$user){
                if(empty($db->userInGym($user['id']))){
                $users[$key]['inGym'] = false;
            } else{
                $users[$key]['inGym'] = true;
            }
            }
        }
        else{

            $users = $db->getUsers();
            foreach ($users as $key=>$user){
                if(empty($db->userInGym($user['id']))){
                    $users[$key]['inGym'] = false;
                } else{
                    $users[$key]['inGym'] = true;
                }
            }
        }

        echo $this->twig->render('admin/manualLogUser.twig', array('users'=> $users));
    }

    /**
     * Log user in using NFC
     */
    public function signin()
    {
        $db = new DB();
        $date = date_create();

        if(empty($db->userInGym($_POST['id']))) {
            $db->signin($_POST['id'], date_format($date, 'Y-m-d H:i:s'));
            $result['success'] = true;
            $result['msg'] = "User Logged in Successfully!";
            echo json_encode($result);
        } else {
            $result['success'] = false;
            $result['msg'] = "User Already In The Gym";
            echo json_encode($result);
        }
    }

    /**
     * Log user out using NFC
     */
    public function signout()
    {
        $db = new DB();
        $date = date_create();

        if(!empty($db->userInGym($_POST['id']))){
            $db->signout($_POST['id'],date_format($date, 'Y-m-d H:i:s'));

            $result['success'] = true;
            $result['msg'] = "User Logged out Successfully!";
            echo json_encode($result);
        } else{
            $result['success'] = false;
            $result['msg'] = "User Not In The Gym";
            echo json_encode($result);
        }

    }



}