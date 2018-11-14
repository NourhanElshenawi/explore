<?php namespace Gazzete\Controllers;
/**
 * Created by PhpStorm.
 * User: Antony
 * Date: 5/28/2015
 * Time: 14:30
 */

use League\Plates\Engine;

class BaseController {

	protected $templates;

	function __construct()
	{
		// Create new Plates instance
		$this->templates = new Engine(__DIR__ . '/../Views');
	}
}