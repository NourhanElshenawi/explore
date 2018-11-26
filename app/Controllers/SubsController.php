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



class SubsController extends Controller
{

    public function subscribe()
    {
        $db = new DB();
//        var_dump($_GET['email']);
        $db->addSubs($_POST['email']);

        redirect('/');
    }

}