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
        $db = new DB();
        $contactInfo = $db->getContactInfo();
        echo $this->twig->render('index.twig', array('contactInfo'=> $contactInfo));
//        echo $this->twig->render('mapStyle.twig');
    }


}