<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/recaptchalib.php';

use Dotenv\Dotenv;
use Nourhan\Controllers;
use Nourhan\Router;

//Load .env
$dotenv = new Dotenv($path = __DIR__ . '/..');
if (file_exists($path . '/.env'))
{
    $dotenv->load();
    $dotenv->required(
        [
            'CLEARDB_DATABASE_URL',
        ]
    )->notEmpty();
}


$router = new Router\Router();



/** PUBLIC **/
$router->get('/', 'MainController', 'index');
$router->get('/login', 'UserController', 'login');
$router->get('/calendar', 'MainController', 'calendar');
$router->get('/fitnessProgram', 'MainController', 'fitnessProgram');
$router->get('/realtimelogs', 'MainController', 'realtimeLogs');
$router->post('/deleteProgramRequest', 'MainController', 'deleteProgramRequest');

/** USER **/
$router->get('/profile', 'UserController', 'profile');
$router->post('/login', 'UserController', 'userLogin');
$router->get('/logout', 'UserController', 'logout');
$router->get('/signin', 'UserController', 'signin');

//Dr Certificate

$router->post('/upload_dr_certificate', 'UserController', 'uploadCertificate');
//$router->post('/add_multiple_users', 'AdminController', 'addMultipleUsers');


//Class Registration
$router->get('/register', 'UserController', 'register');
$router->post('/registerclass', 'UserController', 'registerClass');
$router->post('/unregisterclass', 'UserController', 'unregisterClass');

//Workout Program
$router->get('/requestProgram', 'UserController', 'requestProgram');
$router->post('/submitProgram', 'UserController', 'submitProgram');
$router->get('/workoutProgramHistory', 'UserController', 'programHistory');
$router->get('/currentProgramRequest', 'UserController', 'currentProgramRequest');

//Payment
$router->post('/pay', 'UserController', 'pay');

//Stats
$router->get('/generalStats', 'UserController', 'generalStats');
$router->get('/personalStats', 'UserController', 'personalStats');

//Logs
$router->get('/mylogs', 'UserController', 'userLogs');

//Friends
$router->get('/myfriends', 'UserController', 'userFriends');
$router->get('/friendsRealTime', 'UserController', 'friendsRealTime');
$router->post('/addFriend', 'UserController', 'addFriend');
$router->post('/removeFriend', 'UserController', 'removeFriend');


/** Android App Web Service**/
$router->post('/androidLogin', 'UserController', 'androidLogin');
$router->post('/androidViewProgram', 'UserController', 'androidViewProgram');
$router->post('/submitProgramAndroid', 'UserController', 'androidSubmitProgram');



/** ADMIN **/
//CLASSES SCHEDULE
$router->get('/editschedule', 'AdminController', 'editSchedule');
$router->get('/adminsearchclasses', 'AdminController', 'searchClasses');
$router->post('/addclass', 'AdminController', 'addClass');
$router->post('/deleteclass', 'AdminController', 'deleteClass');
$router->post('/updateclass', 'AdminController', 'updateClass');

//CLASS REGISTRATIONS
$router->get('/registrations', 'AdminController', 'registrations');
$router->get('/searchregistrations', 'AdminController', 'searchRegistrations');

//Users
$router->get('/editusers', 'AdminController', 'editUsers');
$router->post('/updateUser', 'AdminController', 'updateUser');
$router->post('/deleteuser', 'AdminController', 'deleteUser');
$router->post('/add_multiple_users', 'AdminController', 'addMultipleUsers');
$router->post('/add_user', 'AdminController', 'addUser');

//Instructors
$router->get('/editInstructors', 'AdminController', 'editInstructors');
$router->post('/addInstructor', 'AdminController', 'addInstructor');
$router->post('/addInstructorByEmail', 'AdminController', 'addInstructorByEmail');

//STATS
$router->get('/adminvisitstats', 'AdminController', 'stats');
$router->post('/adminstatsyear', 'AdminController', 'postStatsYear');
$router->post('/adminstatsmonth', 'AdminController', 'postStatsMonth');
$router->post('/adminstatsday', 'AdminController', 'postStatsDay');
$router->post('/adminstatshour', 'AdminController', 'postStatsHour');
$router->get('/adminclassstats', 'AdminController', 'registrationStats');

//Logs
$router->get('/adminstatsuser', 'MainController', 'userStats');
$router->get('/adminlogssearchclasses', 'MainController', 'searchLogs');
$router->get('/adminsearchrealtime', 'MainController', 'searchRealtimeLogs');
$router->get('/logs', 'AdminController', 'usersLogs');
//$router->get('/admin-search-logs', 'AdminController', 'searchLogs');
$router->get('/manualLog', 'AdminController', 'manualLogUser');
$router->post('/manualSignin', 'AdminController', 'signin');
$router->post('/manualSignout', 'AdminController', 'signout');

/** Nurse **/
$router->get('/nurse-pending', 'NurseController', 'seePendingCertificates');
$router->get('/nurse-approved', 'NurseController', 'seeApprovedCertificates');
$router->get('/nurse-rejected', 'NurseController', 'seeRejectedCertificates');
$router->post('/approveCertificate', 'NurseController', 'approveCertificate');
$router->post('/rejectCertificate', 'NurseController', 'rejectCertificate');

/** Trainer **/
$router->get('/finalizedRequests', 'TrainerController', 'finalizedProgramRequests');
$router->get('/programRequests', 'TrainerController', 'programRequests');
$router->post('/trainerResponse', 'TrainerController', 'trainerResponse');



////See inside $router
//echo "<pre>";
//print_r($router);

$router->submit();


