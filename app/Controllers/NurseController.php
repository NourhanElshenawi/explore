<?php
namespace Nourhan\Controllers;

use Nourhan\Database\DB;
use Nourhan\Services\Upload;
use Nourhan\ReCaptcha;


class NurseController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function seePendingCertificates()
    {
        $db = new DB();

        $users = $db->getUserCertificates();

        echo $this->twig->render('nurse/pendingCertificates.twig', array('users'=>$users));
    }

    public function seeApprovedCertificates()
    {
        $db = new DB();

        $users = $db->getApprovedCertificates();

        echo $this->twig->render('nurse/approvedCertificates.twig', array('users'=>$users));
    }

    public function seeRejectedCertificates()
    {
        $db = new DB();

        $users = $db->getRejectedCertificates();

        echo $this->twig->render('nurse/rejectedCertificates.twig', array('users'=>$users));
    }

    public function approveCertificate()
    {
        $db = new DB();

        $result = $db->approveUserCertificate($_POST['user_certificate_id']);
        if($result){
//            // the message
//            $msg = "Dear ". $_SESSION['user']['name'].",\n".
//                "We would like to inform you that your Dr. Certificate has been approved!\n".
//                "You can now use all the athletics facilities";
//
//            // use wordwrap() if lines are longer than 70 characters
//            $msg = wordwrap($msg,70);
//
//            // send email
//            mail("n.elshenawi@acg.edu","Dr. Certificate Approved!",$msg);

            $to = "n.elshenawi@acg.edu";
            $subject = "Dr. Certificate Approved!";
            $txt = "Dear ". $_SESSION['user']['name'].",\n".
                    "We would like to inform you that your Dr. Certificate has been approved!\n".
                    "You can now use all the athletics facilities";
            $headers = "From: nourhanelshenawy@gmail.com" . "\r\n" .
                "CC: nourhan_elshenawy@hotmail.com";

            mail($to,$subject,$txt,$headers);
        }
        echo json_encode($result);
    }

    public function rejectCertificate()
    {
        $db = new DB();

        $result = $db->rejectUserCertificate($_POST['user_certificate_id']);

        echo json_encode($result);
    }

}