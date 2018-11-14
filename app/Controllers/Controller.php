<?php

namespace Nourhan\Controllers;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Nourhan\Database\DB;

class Controller
{
    protected $twig;

    public function __construct()
    {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../Views/');
        $this->twig = new Twig_Environment($loader, array(
            'debug' => true,
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addGlobal('session', $_SESSION);
    }

}