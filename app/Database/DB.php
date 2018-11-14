<?php
namespace Nourhan\Database;

use Nourhan\Services\Upload;
use PDO;
use PDOException;

class DB
{

    protected $host;
    protected $port;
    protected $dbName;
    protected $username;
    protected $password;
    protected $conn;

    /**
     * select *
    from heroku_c4df5ab8bcadabc.classes
    WHERE endTime >= '12:40:00' AND
    startTime <='12:00:00' AND classes.period = 'spring' AND
    location = 'studio 1' AND monday = '1' AND
    tuesday ='1' AND wednesday ='1' AND
    thursday ='1' AND friday ='1';
    SELECT * FROM heroku_c4df5ab8bcadabc.classes;
     */

    /**
     * DB constructor. By default connect to Homestead virtual DB server and to the 'kourtis' database schema.
     * @param string $servername
     * @param string $port
     * @param string $dbname
     * @param string $username
     * @param string $password
     */

    /** Local DB for testing **/
//    public function __construct($host = "127.0.0.1", $port = "33060", $dbName = "dereeAthletics",$username = "homestead", $password = "secret" )
//    {
//        $this->host = $host;
//        $this->port = $port;
//        $this->dbname = $dbName;
//        $this->username = $username;
//        $this->password = $password;
//
//        $this->connect();
//    }


    /**
     * DB constructor. Connect to Heroku's DB (ClearDB).
     * Live DB
     */
        public function __construct()
        {
            $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
            $this->host = $cleardb_url["host"];;
            $this->port = 3306;
            $this->dbname = substr($cleardb_url["path"], 1);
            $this->username = $cleardb_url["user"];
            $this->password = $cleardb_url["pass"];

            $this->options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];

            $this->connect();
        }

        // connecting to the DB
    public function connect()
    {
        try{
            $conn = new PDO("mysql:host=$this->host;port:$this->port;dbname=$this->dbName", $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn = $conn;
        }   catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    //get all the classes offered by the gym as well as the information for the instructor giving the class
    public function getClasses()
    {
        //joining the classes and instructors tables to get the information on the classes plus the instructor giving the class
        $stmt = $this->conn->prepare("select *, classes.name AS className, users.name AS instructorName,
                                      classes.id AS classID, instructors.id AS instructorID
                                      from {$this->dbname}.classes
                                      join {$this->dbname}.instructors
                                      on classes.instructorID = instructors.id
                                      join {$this->dbname}.users
                                      on instructors.userID = users.id");


        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    //get a specific class offered by the gym using its id as well as the information for the instructor giving the class
    public function getClass($id)
    {
        $stmt = $this->conn->prepare("select *, classes.name AS className, 
                                      classes.id AS classID, instructors.id AS instructorID,
                                      users.name as instructorName
                                      from {$this->dbname}.classes
                                      join {$this->dbname}.instructors
                                      on classes.instructorID = instructors.id
                                      join {$this->dbname}.users
                                      on instructors.userID = users.id
                                      WHERE classes.id =:classID");
        $stmt->bindParam(':classID',$id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

    //get a specific class offered by the gym using its id as well as the information for the instructor giving the class
    public function getFriendsRegisteredForClass($id, $classID)
    {
        $stmt = $this->conn->prepare("select *, friends.followsID AS friendID, registrations.id as registrationID
                                      from {$this->dbname}.users
                                      join {$this->dbname}.friends
                                      on users.id = friends.followsID
                                      join {$this->dbname}.registrations
                                      on registrations.userID = friends.followsID
                                      WHERE friends.userID=:id AND registrations.classID =:classID
                                      ");
        $stmt->bindParam(':id',$id);
        $stmt->bindParam(':classID',$classID);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }



    //get all the instructors at the gym
    public function getInstructors()
    {
        $stmt = $this->conn->prepare("select *, instructors.id as id, 
                                      users.id as userID, users.name as name
                                      from {$this->dbname}.instructors
                                      join {$this->dbname}.users
                                      on instructors.userID = users.id");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    //get a specific instructor using his id
    public function getInstructor($id)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.instructors WHERE id=?");
        $stmt->bindValue(1,$id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

    public function getUser($email, $password)
    {
        try {
            $stmt = $this->conn->prepare("select *, users.id AS id, instructors.id as instructorID from {$this->dbname}.users
                                          LEFT JOIN {$this->dbname}.instructors
                                          on {$this->dbname}.users.id = {$this->dbname}.instructors.userID 
                                          WHERE users.email = ? and users.password = ?");
            $stmt->bindValue(1, $email);
            $stmt->bindValue(2, $password);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();

        } catch (PDOException $e) {
            $result["success"] = 0;
            $result["message"] = "Database Error1. Please Try Again!";
        }

        return $result;
    }

    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE email=:email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();

        } catch (PDOException $e) {
            $result["success"] = 0;
            $result["message"] = "Database Error1. Please Try Again!".$e;
        }

        return $result;
    }

    public function androidLogin($email, $password)
    {
        try {
            $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE email = ? and password = ?");
            $stmt->bindValue(1, $email);
            $stmt->bindValue(2, $password);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetch();

            if(isset($user) && !empty($user) && $user!=false ) {
                $result["success"] = 1;
                $result["message"] = "Welcome";
                $result['user'] = $user;
            } else {
                $result["success"] = 0;
                $result["message"] = "Invalid Credentials";
            }

        } catch (PDOException $e) {
            $result["success"] = 0;
            $result["message"] = "Database Error1. Please Try Again!";
        }

        return $result;
    }


    public function androidViewProgram($id)
    {
        $result = [];

        try {
            $stmt = $this->conn->prepare("select *
                                          from {$this->dbname}.program_requests
                                          WHERE userID=:id and trainerResponse=:trainerResponse
                                          ORDER BY program_requests.date DESC
                                          LIMIT 1");
            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':trainerResponse', 1);
            $stmt->execute();
            $row = $stmt->fetch();

            $result["success"] = 1;
            $result["message"] = "Success. Program found!";
            $result["row"] = $row;
            $result["program"] = $row['trainerComments'];
            $result["trainer"] = $row['trainerName'];

        } catch (PDOException $e) {
            $result["success"] = 0;
            $result["message"] = "Database Error1. Please Try Again!";
        }

        return $result;
    }

    /** User**/

    public function getUserProfile($id)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE id = ?");
        $stmt->bindValue(1,$id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

    public function getUserLatestCertificateYear($id)
    {
        try {
            $stmt = $this->conn->prepare("select YEAR (user_certificates.uploaded_at), user_certificates.certificate_status
                                      from {$this->dbname}.user_certificates
                                      WHERE user_certificates.userID =:id
                                      ORDER BY user_certificates.uploaded_at DESC limit 1;");

            $stmt->bindParam(':id', $id);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();

            d($row);
            $result['success'] = true;
            $result['msg'] = $row;

            return $result;
        } catch (PDOException $e){

            $result['success'] = false;
            $result['msg'] = "Could not execute query! \n Error: ". $e;
            return $result;
        }
    }
    public function uploadUserCertificate($id, $file_name)
    {
        try {
                $stmt = $this->conn->prepare("
                INSERT INTO {$this->dbname}.user_certificates 
                (`userID`, `certificate_file`) 
                VALUES (:userID, :certificate_file);
            ");
                $stmt->bindParam(':userID', $id);
                $stmt->bindValue(':certificate_file', date("Y-m-d H:i:s")."_".$_SESSION['user']['id']."_".$file_name);
                $stmt->execute();

                $result['success'] = 1;
                $result['message'] = "Request Successfully Submitted!";

        } catch (PDOException $e){

            $result['success'] = false;
            $result['msg'] = "Could not execute query! \n Error: ". $e;
            return $result;
        }
    }

    public function getUserLogs($id) {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs WHERE userID=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUserLogsByKeyword($id, $keyword){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs
                                      WHERE userID= :id 
                                      AND (DATE (login) like :date or 
                                      TIME (login) like :time or
                                      HOUR (login) =:hour or
                                      MONTH (login) like :month or
                                      DAYOFWEEK (login) like :day or
                                      YEAR (login) like :year or
                                      MONTHNAME (login) like :monthName)");

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':date', $keyword);
        $stmt->bindParam(':time', $keyword);
        $stmt->bindParam(':hour', $keyword);
        $stmt->bindParam(':month', $keyword);
        $stmt->bindParam(':day', $keyword);
        $stmt->bindParam(':month', $keyword);
        $stmt->bindParam(':monthName', $keyword);
        $stmt->bindParam(':year', $keyword);

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();
        return $result;

    }

    public function createProgramRequest ($data)
    {
        $result = [];
        $userID = $_SESSION['user']['id'];
        $monday = $data['monday'] ?? 0;
        $tuesday = $data['tuesday'] ?? 0;
        $wednesday = $data['wednesday'] ?? 0;
        $thursday = $data['thursday'] ?? 0;
        $friday = $data['friday'] ?? 0;
        $saturday = $data['saturday'] ?? 0;
        $sunday = $data['sunday'] ?? 0;
        $goal = json_decode($_POST['goal']);


        try
        {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->dbname}.program_requests 
                (`userID`, `height`, `weight`, `pastExercise`, `currentlyExercising`, `currentExercisingIntensity`, `activities`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`,
                 `developMuscleStrength`, `rehabilitateInjury`, `overallFitness`, `loseBodyFat`, `startExerciseProgram`, `designAdvanceProgram`, `increaseFlexibility`, `sportsSpecificTraining`,
                 `increaseMuscleSize`, `cardioExercise`,`comments`) 
                VALUES (:userID, :height, :weight, :pastExercise, :currentlyExercising, :currentExercisingIntensity, :activities, :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday,
                 :developMuscleStrength, :rehabilitateInjury, :overallFitness, :loseBodyFat,:startExerciseProgram, :designAdvanceProgram, :increaseFlexibility, :sportsSpecificTraining,
                 :increaseMuscleSize, :cardioExercise, :comments );
            ");
            $stmt->bindParam(':userID', $userID);
            $stmt->bindValue(':height', $data['height'] ?? 0);
            $stmt->bindValue(':weight', $data['weight'] ?? 0);
            $stmt->bindParam(':pastExercise', $data['pastExercise']);
            $stmt->bindParam(':currentlyExercising', $data['currentlyExercising']);
            $stmt->bindValue(':currentExercisingIntensity', $data['currentExercisingIntensity']??0);
            $stmt->bindValue(':activities', $data['activities'] ?? "");
            $stmt->bindValue(':monday', (int) $monday ?? 0);
            $stmt->bindValue(':tuesday', (int) $tuesday ?? 0);
            $stmt->bindValue(':wednesday', (int) $wednesday ?? 0);
            $stmt->bindValue(':thursday', (int) $thursday ?? 0);
            $stmt->bindValue(':friday', (int) $friday ?? 0);
            $stmt->bindValue(':saturday', (int) $saturday ?? 0);
            $stmt->bindValue(':sunday', (int) $sunday ?? 0);
            $stmt->bindValue(':comments', $data['comments'] ?? "");

            foreach($goal as $key => $value)
            {
                $stmt->bindValue(":".$value, (int)$key ?? 0);
            }

            $stmt->execute();

            $result['success'] = 1;
            $result['message'] = "Request Successfully Submitted!";
        }
        catch (PDOException $exception)
        {
            ddd($exception->getMessage());
            $result['success'] = 0;
            $result['message'] = $exception->getMessage();
        }

        return $result;
    }

    public function createProgramRequestFromAndroid ($data)
    {
        try
        {
            $result = [];

            $userID = $data['userID'] ?? 1;
            $monday = $data['monday'] ?? 0;
            $tuesday = $data['tuesday'] ?? 0;
            $wednesday = $data['wednesday'] ?? 0;
            $thursday = $data['thursday'] ?? 0;
            $friday = $data['friday'] ?? 0;
            $saturday = $data['saturday'] ?? 0;
            $sunday = $data['sunday'] ?? 0;

            $stmt = $this->conn->prepare("
                INSERT INTO {$this->dbname}.program_requests 
                (`userID`, `height`, `weight`, `pastExercise`, `currentlyExercising`, `currentExercisingIntensity`, `activities`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `comments`) 
                VALUES (:userID, :height, :weight, :pastExercise, :currentlyExercising, :currentExercisingIntensity, :activities, :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday, :comments );
            ");
            $stmt->bindParam(':userID', $userID);
            $stmt->bindValue(':height', $data['height'] ?? 0);
            $stmt->bindValue(':weight', $data['weight'] ?? 0);
            $stmt->bindParam(':pastExercise', $data['pastExercise']);
            $stmt->bindParam(':currentlyExercising', $data['currentlyExercising']);
            $stmt->bindValue(':currentExercisingIntensity', $data['currentExercisingIntensity']??0);
            $stmt->bindValue(':activities', $data['activities'] ?? "");
            $stmt->bindValue(':monday', (int) $monday ?? 0);
            $stmt->bindValue(':tuesday', (int) $tuesday ?? 0);
            $stmt->bindValue(':wednesday', (int) $wednesday ?? 0);
            $stmt->bindValue(':thursday', (int) $thursday ?? 0);
            $stmt->bindValue(':friday', (int) $friday ?? 0);
            $stmt->bindValue(':saturday', (int) $saturday ?? 0);
            $stmt->bindValue(':sunday', (int) $sunday ?? 0);
            $stmt->bindValue(':comments', $data['comments'] ?? "");

            $stmt->execute();

            $result['success'] = 1;
            $result['message'] = "Request Successfully Submitted!";
        }
        catch (PDOException $exception)
        {
            ddd($exception->getMessage());
            $result['success'] = 0;
            $result['message'] = $exception->getMessage();
        }

        return $result;
    }

    public function getUserMonthlyVisits($id)
    {
        $stmt = $this->conn->prepare("select MONTH(login) from {$this->dbname}.logs WHERE userID = ?");
        $stmt->bindValue(1, $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUserProgramRequests($id)
    {
        try {
            $stmt = $this->conn->prepare("select *, instructors.id as instructorID, program_requests.id as id
                                      from {$this->dbname}.program_requests
                                      join {$this->dbname}.instructors
                                      on program_requests.instructorID = instructors.id
                                      WHERE program_requests.userID =:id
                                      ORDER BY program_requests.date DESC;");

            $stmt->bindParam(':id', $id);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            return $result;
        } catch (PDOException $e){
            ddd($e);
        }
    }

    public function getUserCurrentProgram($id)
    {
        try {
            $stmt = $this->conn->prepare("select *, instructors.id as instructorID, program_requests.id as id
                                      from {$this->dbname}.program_requests
                                      join {$this->dbname}.instructors
                                      on program_requests.instructorID = instructors.id
                                      WHERE program_requests.userID =:id and trainerResponse =:trainerResponse
                                      ORDER BY program_requests.date DESC limit 1;");

            $stmt->bindParam(':id', $id);
            $stmt->bindValue(':trainerResponse', 1);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            return $result;
        } catch (PDOException $e){

            $result['success'] = false;
            $result['msg'] = "Could not execute query! \n Error: ". $e;
            return $result;
        }
    }
    
    public function deleteProgramRequest($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE
                                      from {$this->dbname}.program_requests
                                      WHERE program_requests.id =:id;");

            $stmt->bindParam(':id', $id);
            $success = $stmt->execute();

            if ($success) {
                $result['success'] = true;
                $result['message'] = "Request Deleted!";
            }
            return $result;
        } catch (PDOException $e){
            $result['success'] = false;
            $result['msg'] = "Request could not be deleted! \n Error: " . $e;
            return $result;
        }
    }

    public function getUserLogsTime($id)
    {
        $stmt = $this->conn->prepare("select TIME (login), TIME (logout) from {$this->dbname}.logs 
                                      WHERE userID =:id and logout is not NULL ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUserFriends($id)
    {
        //joining the classes and instructors tables to get the information on the classes plus the instructor giving the class
        $stmt = $this->conn->prepare("select *, users.id AS friendID
                                      from {$this->dbname}.users
                                      join {$this->dbname}.friends
                                      on friends.followsID = users.id
                                      WHERE friends.userID =:id ");


        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function searchUserFriends($id, $keyword)
    {
        //joining the classes and instructors tables to get the information on the classes plus the instructor giving the class
        $stmt = $this->conn->prepare("select *, users.id AS friendID
                                      from {$this->dbname}.users
                                      join {$this->dbname}.friends
                                      on friends.followsID = users.id
                                      WHERE friends.userID =:id AND 
                                      users.email =:keyword or users.name =:keyword or birthDate=:keyword");


        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function addUserFriend($id, $followID)
    {
        $stmt = $this->conn->prepare("insert into {$this->dbname}.friends 
                                    (userID, followsID) 
                                    VALUES  (:id,:followID)");

        try{
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':followID', $followID);

            $stmt->execute();

            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    public function removeUserFriend($id, $followsID)
    {
        $stmt = $this->conn->prepare("delete from {$this->dbname}.friends WHERE userID=:id and followsID=:followsID");
        $stmt->bindParam(":id",$id);
        $stmt->bindParam(":followsID",$followsID);
        try{
            $stmt->execute();

            $result['success']= true;
            $result['msg']= "Removed!";
            return $result;
        } catch (PDOException $e){
            $result['success']= false;
            $result['msg']= "Could not remove friend";
            return $result;
        }
    }



    /*********USER VERIFICATION************/

    public function getUserCredentials($username, $password)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE email = ? and password = ?");
        $stmt->bindValue(1,$username);
        $stmt->bindValue(2,$password);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

 /*************ADMIN***************/

 ////// EDIT CLASSES
    public function updateClass($period,$id, $endTime, $startTime, $capacity,$instructorID)
    {
        $stmt = $this->conn->prepare("update {$this->dbname}.classes set endTime = ?, startTime = ?, capacity = ?,
 instructorID = ?, period = ?  WHERE id = ? ");

        try{
            $stmt->bindValue(1, $endTime);
            $stmt->bindValue(2, $startTime);
            $stmt->bindValue(3, $capacity);
            $stmt->bindValue(4, $instructorID);
            $stmt->bindValue(5, $period);
            $stmt->bindValue(6, $id);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            //redirect error!
        }
    }


    public function searchClasses($keyword)
    {
        $stmt = $this->conn->prepare("select *, classes.name AS className, users.name AS instructorName,
                                      classes.id AS classID, instructors.id AS instructorID
                                      from {$this->dbname}.classes
                                      join {$this->dbname}.instructors
                                      on classes.instructorID = instructors.id
                                      join {$this->dbname}.users
                                      on instructors.userID = users.id
                                      WHERE users.name LIKE :keyword or classes.name LIKE :keyword");

            $stmt->bindParam(':keyword', $keyword);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

        return $result;

    }
    public function searchClassesForConflict($instructorID,$startTime, $endTime, $period, $location,
                                             $monday, $tuesday, $wednesday, $thursday, $friday)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.classes
                                      WHERE :startTime <= classes.endTime AND 
                                      :endTime >= classes.startTime AND classes.period =:period AND 
                                      (
                                      (classes.location =:location AND 
                                      (classes.monday =:monday OR 
                                      classes.tuesday =:tuesday OR classes.wednesday =:wednesday or 
                                      classes.thursday =:thursday or classes.friday =:friday)
                                      ) OR
                                      (classes.instructorID=:instructorID)
                                      )");



        $stmt->bindValue(':instructorID', $instructorID);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->bindValue(':period', $period);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':monday', $monday);
        $stmt->bindValue(':tuesday', $tuesday);
        $stmt->bindValue(':wednesday', $wednesday);
        $stmt->bindValue(':thursday', $thursday);
        $stmt->bindValue(':friday', $friday);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function addClass($name, $instructorID, $startTime, $endTime, $period, $capacity, $location,
                             $monday, $tuesday, $wednesday, $thursday, $friday)
    {
        $stmt = $this->conn->prepare("insert into {$this->dbname}.classes 
                                    (name, instructorID, startTime, endTime, period, 
                                    capacity, location, monday, tuesday, wednesday, thursday, friday) 
                                    VALUES  (:name,:instructorID, :startTime, :endTime, :period, :capacity,
                                    :location, :monday, :tuesday, :wednesday, :thursday, :friday)");

        try{
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':instructorID', $instructorID);
            $stmt->bindValue(':startTime', $startTime);
            $stmt->bindValue(':endTime', $endTime);
            $stmt->bindValue(':period', $period);
            $stmt->bindValue(':capacity', $capacity);
            $stmt->bindValue(':location', $location);
            $stmt->bindValue(':monday', $monday);
            $stmt->bindValue(':tuesday', $tuesday);
            $stmt->bindValue(':wednesday', $wednesday);
            $stmt->bindValue(':thursday', $thursday);
            $stmt->bindValue(':friday', $friday);

            $stmt->execute();

            return true;
        } catch (Exception $e) {
            return false;
        }


    }

    public function deleteClass($id)
    {
        $stmt = $this->conn->prepare("delete from {$this->dbname}.classes WHERE id = ?");
        $stmt->bindValue(1,$id);
        $stmt->execute();
    }


    /**
     * Nurse
     */

    /**
     * @return mixed
     */
    public function getUserCertificates()
    {
        $stmt = $this->conn->prepare("
          select *
          from {$this->dbname}.users
          join {$this->dbname}.user_certificates
          on users.id = user_certificates.userID
          WHERE user_certificates.certificate_status = ?;
          ");
        $stmt->bindValue(1, '0');
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function approveUserCertificate($id)
    {
        $stmt = $this->conn->prepare("update {$this->dbname}.user_certificates set certificate_status = ? WHERE id = ? ");

        try{
            $stmt->bindValue(1, '1');
            $stmt->bindValue(2, $id);
            $result = $stmt->execute();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    public function rejectUserCertificate($id)
    {
        $stmt = $this->conn->prepare("update {$this->dbname}.user_certificates set certificate_status = ? WHERE id = ? ");

        try{
            $stmt->bindValue(1, '2');
            $stmt->bindValue(2, $id);
            $result = $stmt->execute();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getApprovedCertificates()
    {
        $stmt = $this->conn->prepare("
          select *
          from {$this->dbname}.users
          join {$this->dbname}.user_certificates
          on users.id = user_certificates.userID
          WHERE user_certificates.certificate_status = ?;
          ");
        $stmt->bindValue(1, '1');
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getRejectedCertificates()
    {
        $stmt = $this->conn->prepare("
          select *
          from {$this->dbname}.users
          join {$this->dbname}.user_certificates
          on users.id = user_certificates.userID
          WHERE user_certificates.certificate_status = ?;
          ");
        $stmt->bindValue(1, '2');
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Edit Users
    */

    public function getUsers()
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.users");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function userExists($email)
    {
        $stmt = $this->conn->prepare("
 				Select * FROM {$this->dbname}.users
 				WHERE email=:email;"
        );
        $stmt->bindParam(':email', $email);

        try {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();

            return $row;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function getUsersByID($id)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE id = ?");
        $stmt->bindValue(1, $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

    public function searchUsers($keyword)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.users WHERE name LIKE ? OR id LIKE ? OR email LIKE ?");

        $stmt->bindValue(1, $keyword);
        $stmt->bindValue(2, $keyword);
        $stmt->bindValue(3, $keyword);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;


    }

    public function updateUser($data)
    {
        $stmt = $this->conn->prepare("update {$this->dbname}.users 
                                      set name =:name, email =:email, password =:password, birthDate =:birthDate,
                                      gender =:gender, external =:external, admin =:admin, faculty =:faculty,
                                      student =:student, staff =:staff, alumni =:alumni, nurse =:nurse
                                      WHERE id =:id ");

        try{
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':birthDate', $data['birthDate']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindValue(':external', (int) in_array('external', $data['classification']));
            $stmt->bindValue(':admin', (int) in_array('admin', $data['classification']));
            $stmt->bindValue(':faculty', (int) in_array('faculty', $data['classification']));
            $stmt->bindValue(':student', (int) in_array('student', $data['classification']));
            $stmt->bindValue(':staff', (int) in_array('staff', $data['classification']));
            $stmt->bindValue(':alumni', (int) in_array('alumni', $data['classification']));
            $stmt->bindValue(':nurse', (int) in_array('nurse', $data['classification']));
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            //redirect error!
        }
    }

    public function getUserRegistrations($id)
    {
        try{
            $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.registrations
                                      join {$this->dbname}.classes
                                      on registrations.classID = classes.id
                                      WHERE registrations.userID =:id ORDER BY registrations.id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            return $result;
        } catch (PDOException $e){
            $result['success'] = false;
            $result['msg'] = "Error: " . $e;
            return $result;
        }
    }

    public function getUserRegistrationsForClass($id, $classID)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.registrations
                                      join {$this->dbname}.classes
                                      on registrations.classID = classes.id
                                      WHERE registrations.userID =:id AND registrations.classID=:classID");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':classID', $classID);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result;
    }

    public function getRegisteredUsers($classID)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.registrations
            ON {$this->dbname}.registrations.userID={$this->dbname}.users.id
            AND {$this->dbname}.registrations.classID=:class limit 5;"
        );

        $stmt->bindParam(':class', $classID);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function searchClassRegistrations($classID, $keyword)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.registrations
            ON {$this->dbname}.registrations.userID={$this->dbname}.users.id
            AND {$this->dbname}.registrations.classID=:class WHERE {$this->dbname}.users.id LIKE :id or {$this->dbname}.users.email LIKE :email
            OR {$this->dbname}.users.name LIKE :username or {$this->dbname}.users.gender LIKE :gender limit 5;"
        );

        $stmt->bindParam(':class', $classID);
        $stmt->bindParam(':id', $keyword);
        $stmt->bindParam(':email', $keyword);
        $stmt->bindParam(':username', $keyword);
        $stmt->bindParam(':gender', $keyword);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function addUser($user, $userImage)
    {
        //Check if user exists already
        $row = $this->userExists($user['email']);

        if (isset($row) && $row != false) {
            $result['success'] = false;
            $result['message'] = "Error. A user with email {$user['email']} already exists.";

            return $result;
        }

        //Upload user image
        $uploadService = new Upload();

        $userImage['name'] = $user['email'] . '_' . basename($userImage["name"]);

        $uploaded = $uploadService->upload($userImage, $user);

        if ($uploaded['success'] == false) {
            die($uploaded['message']);
        }

        $stmt = $this->conn->prepare("
                    insert into {$this->dbname}.users
                    (name, email, password, birthDate, gender, membershipType, picture, admin, active)
                    VALUES  (:name, :email, :password, :birthDate, :gender, :membershipType, :picture, :admin, :active);"
        );
        $stmt->bindParam(':name', $user['name']);
        $stmt->bindParam(':email', $user['email']);
        $stmt->bindParam(':password', $user['password']);
        $stmt->bindParam(':birthDate', $user['birthDate']);
        $stmt->bindParam(':gender', $user['gender']);
        $stmt->bindParam(':membershipType', $user['membershipType']);
        $stmt->bindParam(':picture', $userImage['name']);
        $stmt->bindParam(':admin', $user['admin']);
        $stmt->bindParam(':active', $user['active']);

        try
        {
            $success = $stmt->execute();

            if ($success) {
                $result['success'] = true;
                $result['message'] = "Success! User {$user['name']} added.";
            }
            return $result;
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }

        return $result;
    }

    public function addMutlipleUsers($user)
    {
        $result['success'] = false;
        $result['message'] = "";

        //Check if user exists
        $row = $this->userExists($user->email);

        //If user exists then update his info
        if (isset($row) && $row != false) {
            $stmt = $this->conn->prepare("
 				update {$this->dbname}.users
 				set name=:name, email=:email, password=:password, birthDate=:birthDate, gender=:gender, membershipType=:membershipType, picture=:picture, admin=:admin, active=:active
 				WHERE id=:id;"
            );
            $stmt->bindParam(':name', $user->name);
            $stmt->bindParam(':email', $user->email);
            $stmt->bindParam(':password', $user->password);
            $stmt->bindParam(':birthDate', $user->birthDate);
            $stmt->bindParam(':gender', $user->gender);
            $stmt->bindParam(':membershipType', $user->membershipType);
            $stmt->bindParam(':picture', $user->picture);
            $stmt->bindParam(':admin', $user->admin);
            $stmt->bindParam(':active', $user->active);
            $stmt->bindParam(':id', $row['id']);

            try
            {
                $stmt->execute();

                $result['success'] = true;
                $result['message'] = "Success! User {$row['id']} updated!";
            }
            catch (PDOException $e)
            {
                die($e->getMessage());
            }

        }
        else { //if user does not exist then add a new one
            $stmt = $this->conn->prepare("
                    insert into {$this->dbname}.users
                    (name, email, password, birthDate, gender, membershipType, picture, admin, active)
                    VALUES  (:name, :email, :password, :birthDate, :gender, :membershipType, :picture, :admin, :active);"
            );
            $stmt->bindParam(':name', $user->name);
            $stmt->bindParam(':email', $user->email);
            $stmt->bindParam(':password', $user->password);
            $stmt->bindParam(':birthDate', $user->birthDate);
            $stmt->bindParam(':gender', $user->gender);
            $stmt->bindParam(':membershipType', $user->membershipType);
            $stmt->bindParam(':picture', $user->picture);
            $stmt->bindParam(':admin', $user->admin);
            $stmt->bindParam(':active', $user->active);

            try
            {
                $success = $stmt->execute();

                if ($success) {
                    $result['success'] = true;
                    $result['message'] = "Success! User {$user->name} added.";
                }
                return $result;
            }
            catch (PDOException $e)
            {
                die($e->getMessage());
            }
        }

        return $result;
    }

    public function deleteUser($id)
    {
        $stmt = $this->conn->prepare("delete from {$this->dbname}.users WHERE id = ?");
        $stmt->bindValue(1,$id);
        $stmt->execute();
    }

    /**
     * Registrations
     */

    /**
     * @param $id
     * @param $classID
     * @return mixed
     */
    public function unregisterClass($id, $classID)
    {
        if($this->getUserRegistrationsForClass($id,$classID)) {
            $stmt = $this->conn->prepare("delete from {$this->dbname}.registrations
                                      WHERE userID =:userID and classID =:classID");
            $stmt->bindParam(':userID', $id);
            $stmt->bindParam(':classID', $classID);
            $stmt->execute();

            $result['success'] = true;
            $result['msg'] = "Unregistered!";
            return $result;
        }
        else{
            $result['success'] = false;
            $result['msg'] = "You're not Registered for This Class anyway";
            return $result;
        }
    }

    public function registerClass($id, $classID)
    {
//        return json_encode($this->getUserRegistrationsForClass($id,$classID));
        if(!$this->getUserRegistrationsForClass($id,$classID)) {
            try {
                $stmt = $this->conn->prepare("insert into {$this->dbname}.registrations
                                            (userID, classID) VALUES  (?, ?)");
                $stmt->bindValue(1, $id);
                $stmt->bindValue(2, $classID);
                $stmt->execute();

                $result['success'] = true;
                $result['msg'] = "Registered!";
                return $result;
            } catch (Exception $e) {

                $result['success'] = false;
                $result['msg'] = "Could Not Register for Class!";
                return $result;
            }
        }
        else {
            $result['success'] = false;
            $result['msg'] = "Already Registered For This Class";
            return $result;
        }
    }

    public function searchUserClassesForConflict($id,$startTime, $endTime, $period,
                                             $monday, $tuesday, $wednesday, $thursday, $friday)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.registrations
                                      JOIN {$this->dbname}.classes
                                      ON classes.id = registrations.classID
                                      WHERE registrations.userID=:id AND 
                                      :startTime <= classes.endTime AND 
                                      :endTime >= classes.startTime AND classes.period =:period AND  
                                      (classes.monday =:monday OR 
                                      classes.tuesday =:tuesday OR classes.wednesday =:wednesday or 
                                      classes.thursday =:thursday or classes.friday =:friday)
                                      ");



        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->bindValue(':period', $period);
        $stmt->bindValue(':monday', $monday);
        $stmt->bindValue(':tuesday', $tuesday);
        $stmt->bindValue(':wednesday', $wednesday);
        $stmt->bindValue(':thursday', $thursday);
        $stmt->bindValue(':friday', $friday);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getClassCapacity($id)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.registrations
                                      WHERE registrations.classID=:id
                                      ");

        $stmt->bindValue(':id', $id);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }


    /** GET USER LOGS FOR USER STATS **/

    /** Logs **/

    public function getUsersLogin(){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs WHERE logout IS NULL ");

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getUsersLogs(){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs
                                      join {$this->dbname}.users
                                      on logs.userID = users.id");

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();
        return $result;

    }

    public function getLogsByKeyword($keyword){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs
                                      join {$this->dbname}.users
                                      on logs.userID = users.id
                                      WHERE DATE (logs.login) like :date or TIME (logs.login) like :time or
                                      HOUR (logs.login) =:hour or
                                      MONTH (logs.login) like :month or DAYOFWEEK (logs.login) like :day or
                                      YEAR (logs.login) like :year or users.name like :name or 
                                      users.email like :email or MONTHNAME (logs.login) like :monthName");

        $stmt->bindParam(':date', $keyword);
        $stmt->bindParam(':time', $keyword);
        $stmt->bindParam(':hour', $keyword);
        $stmt->bindParam(':name', $keyword);
        $stmt->bindParam(':email', $keyword);
        $stmt->bindParam(':month', $keyword);
        $stmt->bindParam(':day', $keyword);
        $stmt->bindParam(':month', $keyword);
        $stmt->bindParam(':monthName', $keyword);
        $stmt->bindParam(':year', $keyword);

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();
        return $result;

    }

    public function getRealtimeLogs(){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.users
                                      JOIN {$this->dbname}.logs
                                      ON users.id = logs.userID
                                      WHERE logs.logout is NULL GROUP BY logs.userID");

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function searchRealTimeUsers($keyword)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.users
                                      JOIN {$this->dbname}.logs
                                      ON users.id = logs.userID
                                      WHERE logs.logout is NULL 
                                      AND (users.id LIKE :id OR users.name LIKE :name or 
                                      users.email like :email or users.gender like :gender or users.birthDate like :birthDate) GROUP BY logs.userID ");
        $stmt->bindParam(':id', $keyword);
        $stmt->bindParam(':name', $keyword);
        $stmt->bindParam(':email', $keyword);
        $stmt->bindParam(':gender', $keyword);
        $stmt->bindParam(':birthDate', $keyword);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getFriendsRealtimeLogs($id){

        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs 
                                      JOIN {$this->dbname}.users
                                      ON users.id = logs.userID
                                      JOIN {$this->dbname}.friends
                                      ON friends.followsID = users.id
                                      WHERE friends.userID=:id and logs.logout is NULL GROUP BY logs.userID");

        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function searchFriendsRealTime($keyword)
    {
        $stmt = $this->conn->prepare("select * from {$this->dbname}.logs 
                                      JOIN {$this->dbname}.users
                                      ON users.id = logs.userID
                                      JOIN {$this->dbname}.friends
                                      ON friends.followsID = users.id
                                      WHERE friends.userID=:id and logs.logout is NULL
                                      AND (users.id LIKE :id OR users.name LIKE :name or 
                                      users.email like :email or users.gender like :gender or users.birthDate like :birthDate) GROUP BY logs.userID ");
        $stmt->bindParam(':id', $keyword);
        $stmt->bindParam(':name', $keyword);
        $stmt->bindParam(':email', $keyword);
        $stmt->bindParam(':gender', $keyword);
        $stmt->bindParam(':birthDate', $keyword);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function userInGym($id){

        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.logs
                                      WHERE logs.userID =:id and logs.logout is NULL limit 1");

        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result;

    }


    /*******STATS******/

    public function getUsersLogsYears(){

        $stmt = $this->conn->prepare("select YEAR (login) from {$this->dbname}.logs");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getUsersLogsMonths(){

        $stmt = $this->conn->prepare("
            SELECT MONTH({$this->dbname}.logs.login)
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.logs
            ON {$this->dbname}.logs.userID={$this->dbname}.users.id;"
        );

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUserLogsMonths($id){

        $stmt = $this->conn->prepare("
            SELECT MONTH(login)
            FROM {$this->dbname}.logs
            WHERE userID=:id;"
        );

        $stmt->bindParam(':id',$id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUsersLogsDays(){

        $stmt = $this->conn->prepare("select DAYOFWEEK (login) from {$this->dbname}.logs");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getUsersLogsHours(){

        $stmt = $this->conn->prepare("select HOUR (login) from {$this->dbname}.logs");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getUsersLogsYearsFilter(){

        try {
            $statement = "
            SELECT YEAR ({$this->dbname}.logs.login)
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.logs
            ON {$this->dbname}.logs.userID={$this->dbname}.users.id
            WHERE";

            $gender = $_POST['gender'];


            if ($gender =='b') {
                $statement = $statement . " {$this->dbname}.users.gender IS NOT NULL";
            } else {
                $statement = $statement . " {$this->dbname}.users.gender=:gender";
            }

            if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])) {
                $statement = $statement . " AND {$this->dbname}.users.birthDate BETWEEN :down AND :up";
            }

            if ($_POST['student']) {
                $statement = $statement . " AND {$this->dbname}.users.student=:student ";
            }

            if ($_POST['staff']) {
                $statement = $statement . " AND {$this->dbname}.users.staff=:staff ";
            }

            if ($_POST['alumni']) {
                $statement = $statement . " AND {$this->dbname}.users.alumni=:alumni ";
            }

            if ($_POST['faculty']) {
                $statement = $statement . " AND {$this->dbname}.users.faculty=:faculty ";
            }

            if ($_POST['admin']) {
                $statement = $statement . " AND {$this->dbname}.users.admin=:admin ";
            }

            if ($_POST['external']) {
                $statement = $statement . " AND {$this->dbname}.users.external=:external ";
            }

            $stmt = $this->conn->prepare($statement);

            if ($gender =='b') {
            } else {
                $stmt->bindParam(':gender', $gender);
            }
            if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])) {
                $stmt->bindParam(':up', $_POST['ageUpper']);
                $stmt->bindParam(':down', $_POST['ageLower']);
            }


            if ($_POST['student']) {
                $stmt->bindValue(':student', $_POST['student']);
            }
            if ($_POST['staff']) {
                $stmt->bindValue(':staff', $_POST['staff']);
            }
            if ($_POST['alumni']) {
                $stmt->bindValue(':alumni', $_POST['alumni']);
            }
            if ($_POST['faculty']) {
                $stmt->bindValue(':faculty', $_POST['faculty']);
            }
            if ($_POST['admin']) {
                $stmt->bindValue(':admin', $_POST['admin']);
            }
            if ($_POST['external']) {
                $stmt->bindValue(':external', $_POST['external']);
            }

            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();

            return $result;
        } catch (PDOException $e){
            return $e->getMessage();
        }
    }

    public function getUsersLogsMonthsFilter(){

        $statement = "
            SELECT MONTH({$this->dbname}.logs.login)
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.logs
            ON {$this->dbname}.logs.userID={$this->dbname}.users.id
            WHERE";

        $gender = $_POST['gender'];

        if (!$gender =='b') {
            $statement = $statement . " {$this->dbname}.users.gender=:gender";
        } else {
            $statement = $statement . " {$this->dbname}.users.gender IS NOT NULL";
        }

        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $statement = $statement . " AND {$this->dbname}.users.birthDate BETWEEN :down AND :up";
        }

        if ($_POST['student']){
            $statement = $statement . " AND {$this->dbname}.users.student=:student ";
        }

        if ($_POST['staff']){
            $statement = $statement . " AND {$this->dbname}.users.staff=:staff ";
        }

        if ($_POST['alumni']){
            $statement = $statement . " AND {$this->dbname}.users.alumni=:alumni ";
        }

        if ($_POST['faculty']){
            $statement = $statement . " AND {$this->dbname}.users.faculty=:faculty ";
        }

        if ($_POST['admin']){
            $statement = $statement . " AND {$this->dbname}.users.admin=:admin ";
        }

        if ($_POST['external']){
            $statement = $statement . " AND {$this->dbname}.users.external=:external ";
        }


        $stmt = $this->conn->prepare($statement);

        if (!$gender == 'b') {
            $stmt->bindParam(':gender', $gender);
        }
        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $stmt->bindParam(':up', $_POST['ageUpper']);
            $stmt->bindParam(':down', $_POST['ageLower']);
        }

        if ($_POST['student']) {
            $stmt->bindValue(':student', $_POST['student']);
        }
        if ($_POST['staff']) {
            $stmt->bindValue(':staff', $_POST['staff']);
        }
        if ($_POST['alumni']) {
            $stmt->bindValue(':alumni', $_POST['alumni']);
        }
        if ($_POST['faculty']) {
            $stmt->bindValue(':faculty', $_POST['faculty']);
        }
        if ($_POST['admin']) {
            $stmt->bindValue(':admin', $_POST['admin']);
        }
        if ($_POST['external']) {
            $stmt->bindValue(':external', $_POST['external']);
        }

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUsersLogsDaysFilter(){

        $statement = "SELECT DAYOFWEEK ({$this->dbname}.logs.login)
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.logs
            ON {$this->dbname}.logs.userID={$this->dbname}.users.id
            WHERE";

        $gender = $_POST['gender'];

        if (!$gender =='b') {
            $statement = $statement . " {$this->dbname}.users.gender=:gender";
        } else {
            $statement = $statement . " {$this->dbname}.users.gender IS NOT NULL";
        }

        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $statement = $statement . " AND {$this->dbname}.users.birthDate BETWEEN :down AND :up";
        }

        if ($_POST['student']){
            $statement = $statement . " AND {$this->dbname}.users.student=:student ";
        }

        if ($_POST['staff']){
            $statement = $statement . " AND {$this->dbname}.users.staff=:staff ";
        }

        if ($_POST['alumni']){
            $statement = $statement . " AND {$this->dbname}.users.alumni=:alumni ";
        }

        if ($_POST['faculty']){
            $statement = $statement . " AND {$this->dbname}.users.faculty=:faculty ";
        }

        if ($_POST['admin']){
            $statement = $statement . " AND {$this->dbname}.users.admin=:admin ";
        }

        if ($_POST['external']){
            $statement = $statement . " AND {$this->dbname}.users.external=:external ";
        }


        $stmt = $this->conn->prepare($statement);

        if (!$gender=='b') {
            $stmt->bindParam(':gender', $gender);
        }
        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $stmt->bindParam(':up', $_POST['ageUpper']);
            $stmt->bindParam(':down', $_POST['ageLower']);
        }


        if ($_POST['student']) {
            $stmt->bindValue(':student', $_POST['student']);
        }
        if ($_POST['staff']) {
            $stmt->bindValue(':staff', $_POST['staff']);
        }
        if ($_POST['alumni']) {
            $stmt->bindValue(':alumni', $_POST['alumni']);
        }
        if ($_POST['faculty']) {
            $stmt->bindValue(':faculty', $_POST['faculty']);
        }
        if ($_POST['admin']) {
            $stmt->bindValue(':admin', $_POST['admin']);
        }
        if ($_POST['external']) {
            $stmt->bindValue(':external', $_POST['external']);
        }

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUsersLogsHoursFilter(){

        $statement = "SELECT HOUR ({$this->dbname}.logs.login)
            FROM {$this->dbname}.users
            INNER JOIN {$this->dbname}.logs
            ON {$this->dbname}.logs.userID={$this->dbname}.users.id
            WHERE ";

        $gender = $_POST['gender'];

        if (!$gender =='b') {
            $statement = $statement . " {$this->dbname}.users.gender=:gender";
        } else {
            $statement = $statement . " {$this->dbname}.users.gender IS NOT NULL";
        }

        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $statement = $statement . " AND {$this->dbname}.users.birthDate BETWEEN :down AND :up";
        }


        if ($_POST['student']){
            $statement = $statement . " AND {$this->dbname}.users.student=:student ";
        }

        if ($_POST['staff']){
            $statement = $statement . " AND {$this->dbname}.users.staff=:staff ";
        }

        if ($_POST['alumni']){
            $statement = $statement . " AND {$this->dbname}.users.alumni=:alumni ";
        }

        if ($_POST['faculty']){
            $statement = $statement . " AND {$this->dbname}.users.faculty=:faculty ";
        }

        if ($_POST['admin']){
            $statement = $statement . " AND {$this->dbname}.users.admin=:admin ";
        }

        if ($_POST['external']){
            $statement = $statement . " AND {$this->dbname}.users.external=:external ";
        }


        $stmt = $this->conn->prepare($statement);

        if (!$gender=='b') {
            $stmt->bindParam(':gender', $gender);
        }
        if (isset($_POST['ageUpper']) && isset($_POST['ageLower'])){
            $stmt->bindParam(':up', $_POST['ageUpper']);
            $stmt->bindParam(':down', $_POST['ageLower']);
        }


        if ($_POST['student']) {
            $stmt->bindValue(':student', $_POST['student']);
        }
        if ($_POST['staff']) {
            $stmt->bindValue(':staff', $_POST['staff']);
        }
        if ($_POST['alumni']) {
            $stmt->bindValue(':alumni', $_POST['alumni']);
        }
        if ($_POST['faculty']) {
            $stmt->bindValue(':faculty', $_POST['faculty']);
        }
        if ($_POST['admin']) {
            $stmt->bindValue(':admin', $_POST['admin']);
        }
        if ($_POST['external']) {
            $stmt->bindValue(':external', $_POST['external']);
        }

        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getUsersAge(){

        $stmt = $this->conn->prepare("select TIMESTAMPDIFF(YEAR,birthDate,CURDATE()) AS age from {$this->dbname}.users");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getUsersGender(){

        $stmt = $this->conn->prepare("select gender from {$this->dbname}.users");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;

    }

    public function getClassesRegistrations()
    {
        try{
            $stmt = $this->conn->prepare("select * 
                                      from {$this->dbname}.registrations
                                      join {$this->dbname}.classes
                                      on registrations.classID = classes.id");
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            d($result);
            return $result;
        } catch (PDOException $e){
            $result['success'] = false;
            $result['msg'] = "Error: ".$e;
            return $result;
        }
    }


    /** RECORDING USER LOGS WITH NFC **/

    public function signout($id, $date)
    {
        $stmt = $this->conn->prepare("update {$this->dbname}.logs set logout = ? WHERE userID = ? AND logout IS NULL ");

        try{
            $stmt->bindValue(1, $date);
            $stmt->bindValue(2, $id);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            //redirect error!
            return false;
        }
    }

    public function signin($id, $date)
    {
        $stmt = $this->conn->prepare("insert into {$this->dbname}.logs (userID, login) VALUES  (?,?)  ");

        try{

            $stmt->bindValue(1, $id);
            $stmt->bindValue(2, $date);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            //redirect error!
            return false;
        }
    }

    /**
     * Edit Instructors
     */

    public function getInstructorsClasses($id)
    {
        try{
            $stmt = $this->conn->prepare("select *, instructors.id as id, classes.id as classID, 
                                      classes.name as className
                                      from {$this->dbname}.classes
                                      join {$this->dbname}.instructors
                                      on instructors.id = classes .instructorID
                                      where classes.instructorID =:id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            return $result;
        } catch (PDOException $e){
            $result['success'] = false;
            $result['msg'] = "Error: ".$e;
            return $result;
        }
    }

    public function addInstructor($userID, $specialty)
    {
        $stmt = $this->conn->prepare("
                    insert into {$this->dbname}.instructors
                    (userID, specialty)
                    VALUES  (:userID, :specialty);"
        );
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':specialty', $specialty);

        try
        {
            $success = $stmt->execute();

            if ($success) {
                $result['success'] = true;
                $result['message'] = "Success! Instructor added.";
            }
            return $result;
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }

        return $result;
    }

    public function searchInstructors($keyword)
    {
        $stmt = $this->conn->prepare("select *, instructors.id as id, 
                                      users.id as userID, users.name as name
                                      from {$this->dbname}.instructors
                                      join {$this->dbname}.users
                                      on instructors.userID = users.id
                                      WHERE users.name LIKE :keyword OR instructors.id LIKE :keyword OR users.email LIKE :keyword or
                                      instructors.userID LIKE :keyword ");

        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;


    }

    /** Trainer **/
    public function getPendingProgramRequests()
    {
        $stmt = $this->conn->prepare("select *
          from {$this->dbname}.users
          join {$this->dbname}.program_requests
          on users.id = program_requests.userID
          WHERE program_requests.trainerResponse = 0 ORDER by program_requests.DATE  ASC ;");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function searchPendingProgramRequests($keyword)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.users
                                      join {$this->dbname}.program_requests
                                      on users.id = program_requests.userID
                                      WHERE program_requests.trainerResponse = 0
                                      AND (
                                      users.name LIKE :keyword OR users.birthDate LIKE :keyword OR users.email LIKE :keyword
                                      OR program_requests.date LIKE :keyword
                                      )
                                      ORDER by program_requests.DATE  ASC ;");
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function trainerResponse($id, $trainerComments, $instructorID)
    {
        try
        {
            $stmt = $this->conn->prepare("UPDATE {$this->dbname}.program_requests SET trainerResponse = 1,
                                          trainerComments=:trainerComments, instructorID =:instructorID 
                                          WHERE id=:id");
            $stmt->bindParam(':trainerComments', $trainerComments);
            $stmt->bindParam(':instructorID', $instructorID);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $result['success'] = true;
            $result['message'] = "Request Successfully Submitted!";
        }
        catch (PDOException $exception)
        {
            ddd($exception->getMessage());
            $result['success'] = false;
            $result['message'] = $exception->getMessage();
        }
        return $result;
    }

    public function getFinalizedProgramRequests()
    {
        $stmt = $this->conn->prepare("select *
          from {$this->dbname}.users
          join {$this->dbname}.program_requests
          on users.id = program_requests.userID
          WHERE program_requests.trainerResponse = 1 ORDER by program_requests.DATE  DESC ;");
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function searchFinalizedProgramRequests($keyword)
    {
        $stmt = $this->conn->prepare("select *
                                      from {$this->dbname}.users
                                      join {$this->dbname}.program_requests
                                      on users.id = program_requests.userID
                                      WHERE program_requests.trainerResponse = 1
                                      AND (
                                      users.name LIKE :keyword OR users.birthDate LIKE :keyword OR users.email LIKE :keyword
                                      OR program_requests.date LIKE :keyword
                                      )
                                      ORDER by program_requests.DATE  DESC ;");
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Addinf payment transaction
     */

    public function addPayment($paymentID, $token, $payerID, $id )
    {
        $stmt = $this->conn->prepare("
                    insert into {$this->dbname}.payments
                    (userID, paymentID, token, payerID)
                    VALUES  (:userID, :paymentID, :token, :payerID);"
        );
        $stmt->bindParam(':userID', $id);
        $stmt->bindParam(':paymentID', $paymentID);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':payerID', $payerID);

        try
        {
            $result = $stmt->execute();

            return $result;
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }

        return $result;
    }

    public function getLastPaymentByUser($id)
    {
        $stmt = $this->conn->prepare("select *
          from {$this->dbname}.payments
          WHERE payments.userID =:id ORDER by payments.date DESC LIMIT 1;");

        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // set the resulting array to associative
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        return $result;
    }

}