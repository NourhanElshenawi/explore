<?php
namespace Nourhan\Router;

class Router
{
    private $_getUri = array();
    private $_getController = array();
    private $_getMethod = array();
    private $_postUri = array();
    private $_postController = array();
    private $_postMethod = array();

    public function __construct()
    {
    }

    /**
     * Build a collection of internal GET URLs to look for
     * @param $uri - The url that the user types in the browser
     * @param $controller - The controller that will handle the url
     * @param $method - The method of the controller that will run
     */
    public function get($uri, $controller, $method)
    {
        $this->_getUri[] = $uri;
        $this->_getController[] = $controller;
        $this->_getMethod[] = $method;
    }

    /**
     * Build a collection of internal POST URLs to look for
     * @param $uri - The url that the user types in the browser
     * @param $controller - The controller that will handle the url
     * @param $method - The method of the controller that will run
     */
    public function post($uri, $controller, $method)
    {
        $this->_postUri[] = $uri;
        $this->_postController[] = $controller;
        $this->_postMethod[] = $method;
    }

    public function submit()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); //get the url

            foreach ($this->_getUri as $key => $value)
            {
                if (preg_match("#^$value$#", $path))
                {
                    //echo $key . ' => ' . $value; //See what the $path returns

                    //Instantiate Controller
                    $controller = 'Nourhan\Controllers\\' . $this->_getController[$key];
                    $controller = new $controller();

                    //Call the appropriate method
                    $method = $this->_getMethod[$key];
                    $controller->$method();
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); //get the url

            foreach ($this->_postUri as $key => $value)
            {
                if (preg_match("#^$value$#", $path))
                {
                    //echo $key . ' => ' . $value; //See what the $path returns

                    //Instantiate Controller
                    $controller = 'Nourhan\Controllers\\' . $this->_postController[$key];
                    $controller = new $controller();

                    //Call the appropriate method
                    $method = $this->_postMethod[$key];
                    $controller->$method();
                }
            }
        }

    }

}