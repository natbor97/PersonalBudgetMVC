<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * Signup controller
 * PHP version 8.1.10
 */
class Signup extends \Core\Controller
{

    //Show the signup page

    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }

    //Sign up new user

    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
            View::renderTemplate('Signup/success.html');
        } else {

            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }
    }
}
