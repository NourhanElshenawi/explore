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
$router->post('/subscribe', 'SubsController', 'subscribe');
$router->get('/login', 'MainController', 'login');
$router->post('/login', 'UserController', 'login');


////See inside $router
//echo "<pre>";
//print_r($router);

$router->submit();