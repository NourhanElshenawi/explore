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
        echo $this->twig->render('index.twig');
    }


}