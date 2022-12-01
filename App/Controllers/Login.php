<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

/**
 * Login controller
 * PHP version 8.1.10
 */
class Login extends \Core\Controller
{

    /**
     * Show the login page
     * @return void
     */
    public function newAction()
    {
       View::renderTemplate('Login/new.html');
    }
}