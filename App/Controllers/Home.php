<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

/**
 * Home controller
 * PHP version 8.1.10
 */
class Home extends \Core\Controller
{
    //Show the index page

    public function indexAction()
    {
        View::renderTemplate('Home/index.html', [
            //'user' => Auth::getUser()
        ]);
    }
}
