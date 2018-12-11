<?php
/**
 * Created by PhpStorm.
 * User: antony
 * Date: 11/17/16
 * Time: 4:37 PM
 */

namespace Nourhan\Controllers;

require __DIR__ . '/../start.php';

use Nourhan\Database\DB;
use Nourhan\Services\Upload;

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;



class UserController extends Controller
{

    public function login()
    {
        // check if a user is logged in at the moment. if he is the session user should be set
        if(isset($_SESSION['user'])){
            redirect('/profile');
        }
        // if a user is not logged in attempt to login
        else {
            $db = new DB();
            //get any user with the collected credentials
            $user = $db->getUser($_POST["email"], $_POST["password"]);

            //check if there was indeed a user found matching these credentials
            if (empty($user)) {
                $error['message'] = "Invalid Credentials!";
                echo $this->twig->render('error.twig', array('error'=>$error));
            }
            //if a user was found with these credentials, set the session user variable with all his information
            else {
                $_SESSION['user'] = $user;
//                redirect('/profile');

                echo $this->twig->render('error.twig', array('error'=>$user));
            }

        }
    }

    public function logout()
    {
        //remove session user variable as the user will logout
        unset($_SESSION['user']);
        redirect('/login');
    }


/** Profile**/
    public function profile()
    {
        if(isLoggedIn()) {
            $db = new DB();

            $classes = $this->getUserClasses($_SESSION['user']['id']);
            $classes = beautifyClassesForCalendar($classes);

            $dateOfPayment = explode(" ", $db->getLastPaymentByUser($_SESSION['user']['id'])['date'])[0];
            $periodCoveredByPayment = daysDateDifference(date("Y-m-d"), $dateOfPayment);

            if ($periodCoveredByPayment >= 31) {
                $needToPay = true;
            } else {
                $needToPay = false;
            }

//            $certificate = date('Y') == $this->getLatestCertificate()['msg']['YEAR (user_certificates.uploaded_at)'];
            $certificate = $this->getLatestCertificate()['msg'];
            $certificate['YEAR (user_certificates.uploaded_at)'] = date('Y') == $certificate['YEAR (user_certificates.uploaded_at)'];
            d($certificate);

            $paymentSuccess = $db->getLastPaymentByUser($_SESSION['user']['id']);
            if (isset($_GET['success'])) {
                if ($db->addPayment($_GET['paymentId'], $_GET['token'], $_GET['PayerID'], $_SESSION['user']['id'])) {
                    $paymentSuccess = true;
                } else {
                    $paymentSuccess = "Please contact support regarding your last Payment!";
                }
            } else if (!isset($_GET['success'])) {
                $paymentSuccess = false;
            }

            echo $this->twig->render('customer/profile.twig', array('classes' => $classes, 'needToPay' => $needToPay,'certificate'=>$certificate, 'paymentSuccess' => $paymentSuccess));
        } else{
            redirect('/');
        }
    }

    public function getUserClasses($id) {

        $db = new DB();

        $userRegistrations = $db->getUserRegistrations($id);
        $classes = array();

        foreach ($userRegistrations as $registration){

            $classes[] = $db->getClass($registration['classID']);

        }

        return $classes;
    }

    /**
     * Dr Certificates
     */

    public function getLatestCertificate()
    {
        $db = new DB();

        $latestCertificate = $db->getUserLatestCertificateYear($_SESSION['user']['id']);

        return $latestCertificate;
    }

    public function uploadCertificate()
    {
        $uploadService = new Upload();
        $db = new DB();

        $result = $uploadService->uploadCertificate($_FILES['users_file']);

        //If file was NOT uploaded show message to user and exit
        if ($result['success'] == false){
            redirect('/404Certificate');
        } else {
            $db->uploadUserCertificate($_SESSION['user']['id'],$_FILES['users_file']['name'])   ;
            redirect('/profile');
        }
    }

/** Registration for classes **/
    public function register()
    {
        if(isLoggedIn()) {
            $db = new DB();

            $calendarClasses = $db->getClasses();
            $classes = $db->getClasses();
            //get user's friends who are also registered for available classes
            $classes = $this->getFriendsRegisteredForClasses($classes);

            $userClasses = $this->getUserClasses($_SESSION['user']['id']);
            $userClasses = $this->getFriendsRegisteredForClasses($userClasses);

            $classes = beautifyClasses($classes);
            $userClasses = beautifyClasses($userClasses);
            $calendarClasses = beautifyClassesForCalendar($calendarClasses);

            d($classes);
            d($userClasses);

            echo $this->twig->render('customer/register.twig', array('calendarClasses' => $calendarClasses, 'classes' => $classes, 'userClasses' => $userClasses));
        } else {
            redirect('/');
        }
    }

    public function getFriendsRegisteredForClasses($classes)
    {
        $db = new DB();

        foreach ($classes as $key=>$class){
            $classes[$key]['registeredFriends'] = $db->getFriendsRegisteredForClass($_SESSION['user']['id'], $class['classID']);
        }

        return $classes;
    }

    public function unregisterClass()
    {
        if(isLoggedIn()) {
            $db = new DB();
            echo json_encode($db->unregisterClass($_POST['userID'], $_POST['classID']));
        } else{
            redirect('/');
        }

    }

    public function registerClass()
    {
        if(isLoggedIn()) {
            $db = new DB();
            $class = $db->getClass($_POST['classID']);
            $testConflict = $db->searchUserClassesForConflict($_SESSION['user']['id'], $class['startTime'], $class['endTime'],
                $class['period'], $class['monday'], $class['tuesday'], $class['wednesday'], $class['thursday'], $class['friday']);
            if ($testConflict == false) {

                $db->registerClass($_POST['userID'], $_POST['classID']);
                echo json_encode("You're Now Registered for " . ucwords($class['className']) . " Class!");
            } else {
                $result = "Could not register for " . ucwords($class['className']) . " class." . " There was a conflict in your schedule with ";

                foreach ($testConflict as $conflict) {
                    if ($conflict === end($testConflict)) {
                        $result = $result . ucwords($conflict['name']) . ".";
                    } else {
                        $result = $result . ucwords($conflict['name']) . ", ";
                    }
                }
                echo json_encode($result);
            }
        }
        else {
            redirect('/');
        }
    }

    /**
     * General Stats
     */

    public function generalStats()
    {

        if(isLoggedIn()) {
            //get how many times a day was repeated aka the number of visits per day by users
            $hours = array_count_values($this->getHourLogs());

            //get how many times a day was repeated aka the number of visits per day by users
            $days = array_count_values($this->getDayLogs());

            //get how many times a month was repeated aka the number of visits per month by users
            $months = array_count_values($this->getMonthsLogs());

            //sort the array by month number
            ksort($months);
            $months = convertMonths($months);
            //sort the array by day number
            ksort($days);
            $days = convertDays($days);

            echo $this->twig->render('customer/generalStats.twig', array('months' => $months, 'days' => $days, 'hours' => $hours));
        } else {
            redirect('/');
        }
    }

    public function getMonthsLogs()
    {
        $db = new DB();

        $monthsDB = $db->getUsersLogsMonths();

        //create an array with months as string values
        $months = convertJoinDBReturns($monthsDB);

        return $months;
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

    public function getDayLogs()
    {
        $db = new DB();
//        $logs = $db->getUsersLogs();
        $dayLogs = $db->getUsersLogsDays();
        $days = convertJoinDBReturns($dayLogs);


        return $days;
    }

    /**
     * Personal Stats
     */

    public function personalStats()
    {
        if(isLoggedIn()) {
            $visitAnalysis = $this->TimeSpentPerVisitAnalysis();
            $monthVisitationAnalysis = $this->numberOfVisitsMonth();
            $graphValues = $this->monthVisitsGraph();

            echo $this->twig->render('customer/personalStats.twig', array('visitAnalysis' => $visitAnalysis, 'monthVisitationAnalysis' => $monthVisitationAnalysis,
                'graphValues' => $graphValues));
        } else{
            redirect('/');
        }

    }

    public function monthVisitsGraph()
    {
        $db = new DB();

        $logs = $db->getUserMonthlyVisits($_SESSION['user']['id']);

        $logs = convertJoinDBReturns($logs);

        $logs = array_count_values($logs);
        $logs = convertMonths($logs);

        return $logs;
    }

    public function numberOfVisitsMonth()
    {

        $db = new DB();
        $monthlyLogs = $db->getUserLogsMonths($_SESSION['user']['id']);
        $logMonthAnalysis = LogMonthAnalysis($monthlyLogs);

        return $logMonthAnalysis;
    }

    public function numberOfVisitsWeek()
    {

    }

    public function TimeSpentPerVisitAnalysis()
    {
        $db = new DB();
        $logTimes = $db->getUserLogsTime($_SESSION['user']['id']);
        $LogTimeAnalysis = LogTimeAnalysis($logTimes);

        return $LogTimeAnalysis;

    }

    /** Requesting workout programs **/
    public function requestProgram ()
    {
        if(isLoggedIn()) {
            echo $this->twig->render('customer/requestProgram.twig');
        } else{
            redirect('/');
        }
    }

    public function submitProgram ()
    {
        $db = new DB();

        $db->createProgramRequest($_POST);
    }


/** View Workout Program **/

    public function programHistory()
    {
        if(isLoggedIn()) {
            $db = new DB();
            $allRequests = $db->getUserProgramRequests($_SESSION['user']['id']);
            $allRequests = convertGoalsList($allRequests);

            echo $this->twig->render('customer/previousProgramRequests.twig', array('requests' => $allRequests));
        } else{
            redirect('/');
        }
    }

    public function currentProgramRequest()
    {
        if(isLoggedIn()) {
            $db = new DB();
            $program = $db->getUserCurrentProgram($_SESSION['user']['id']);
            $program = convertGoalsList($program);

            echo $this->twig->render('customer/currentProgramRequest.twig', array('requests' => $program));
        } else{
            redirect('/');
        }
    }

    /**
     * Logs
     */

    public function userLogs(){

        if(isLoggedIn()) {
            $db = new DB();
            if (isset($_GET['keyword'])) {
                if (convertDayToNum($_GET['keyword']) != false) {
                    $logs = $db->getUserLogsByKeyword(convertDayToNum($_GET['keyword']));
                } else {
                    $logs = $db->getUserLogsByKeyword($_GET['keyword']);
                }
            } else {
                $logs = $db->getUserLogs($_SESSION['user']['id']);
            }
            echo $this->twig->render('customer/logs.twig', array('logs' => $logs));
        } else {
            redirect('/');
        }
    }

    /**
     * Friends
     */
    public function userFriends(){

        if(isLoggedIn()) {
            $db = new DB();
            if (isset($_GET['keyword'])) {
                $friends = $db->searchUserFriends($_SESSION['user']['id'], $_GET['keyword']);
                foreach ($friends as $key => $friend) {
                    $friends[$key]['classes'] = $db->getUserRegistrations($friend['friendID']);
                }
            } else {
                $friends = $db->getUserFriends($_SESSION['user']['id']);
                foreach ($friends as $key => $friend) {
                    $friends[$key]['classes'] = $db->getUserRegistrations($friend['friendID']);
                }
            }
            echo $this->twig->render('customer/friends.twig', array('friends' => $friends));
        } else {
            redirect('/');
        }
    }

    public function addFriend()
    {
        $db = new DB();
        $friend = $db->getUserByEmail($_POST['email']);
        $db->addUserFriend($_SESSION['user']['id'], $friend['id']);

        redirect('/myfriends');
    }

    public function removeFriend()
    {
        $db = new DB();
        $friend = $db->removeUserFriend($_SESSION['user']['id'], $_POST['followsID']);
        echo json_encode($friend);
    }

    public function friendsRealTime(){
        if(isLoggedIn()) {
            $db = new DB();
            if (isset($_GET['keyword'])) {
                $users = $db->searchFriendsRealTime($_SESSION['user']['id'], $_GET['keyword']);
            } else {
                $users = $db->getFriendsRealtimeLogs($_SESSION['user']['id']);
            }
            echo $this->twig->render('customer/friendsRealTime.twig', array('users' => $users));
        } else {
            redirect('/');
        }
    }

    /** Android **/

    public function androidLogin()
    {
        $db = new DB();

        $user = $db->androidLogin($_POST["username"], $_POST["password"]);

        echo json_encode($user);
    }

    public function androidViewProgram()
    {
        $db = new DB();

        $result = $db->androidViewProgram($_POST["ID"]);

        echo json_encode($result);

//        $result = [];
//        $result["success"] = 1;
//        $result["message"] = "Success. We did it!";
//        $result["program"] = "Trainer comments for program go here.";
    }

    public function androidSubmitProgram ()
    {
        $db = new DB();

        $result = $db->createProgramRequestFromAndroid($_POST);

        echo json_encode($result);
    }

    public function convertMonths($months)
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

    public function convertDateToString($dates)
    {
        $date = array();
        foreach ($dates as $dt){
            foreach ($dt as $r){
                $date[]= $r;
            }
        }
        return $date;
    }

    /**
     * Pay with PayPal
     */

    public function pay()
    {
        $product = 'DEREE Gym Subscription Fee';
        $price = (float)10;
        $shipping = 2.00;
        $total = $price + $shipping;

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($product)
            ->setCurrency('EUR')
            ->setQuantity(1)
            ->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping($shipping)
                ->setSubtotal($price);

        $amount = new Amount();
        $amount->setCurrency('EUR')
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("DEREE Gym Monthly Fee")
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("http://athletics-deree.app/profile?paymentSuccess=true")
            ->setCancelUrl("http://athletics-deree.app/profile?paymentSuccess=false");

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try{
            $payment->create(
                new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        'Ad6QnpfuKV6qbAKPj0Uy7OykghBAQCBN5_MO8l2_Croc7PfI9pubbrbzqOmx5oerciLLXeFHUvN2RdPk',
                        'EGyc8_npQKix-febAo1c1eDl1y-21hO1n2hzhW5AZI_ZIFrPiBccz-Cd9PaqG2YOqIosh0Zi1Qguqusb'
                    )
                )
            );
        }catch (Exception $e){
//            d($e);
//            ddd($e->getMessage());
        }

        $paymentURL = $payment->getApprovalLink();

//        redirect($payment->getApprovalLink());
//        redirect('/testing');
    }
}