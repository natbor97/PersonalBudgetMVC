<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;

/**
 * Signup controller
 * PHP version 8.1.10
 */
class Signup extends \Core\Controller
{

    //Show the signup page

    public function newAction()
    {
        if (Auth::getUser()) {
            $this->redirect('/Menu/mainmenu');
        } else {
        View::renderTemplate('Signup/new.html');
        }
    }

    //Sign up new user

    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
            $user->sendActivationEmail();
            View::renderTemplate('Signup/success.html');
        } else {

            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }
    }

    public function activateAction()
    {
        User::activate($this->route_params['token']);

        $this->redirect('/signup/activated');        
    }

    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}
