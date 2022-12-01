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

    // Show the login page

    public function newAction()
    {
        if (Auth::getUser()) {
            $this->redirect('/Home/index');
        } else {
            View::renderTemplate('Login/new.html');
        }
    }

    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);
        $remember_me = isset($_POST['remember_me']);

        if ($user) {

            Auth::login($user, $remember_me);
            Flash::addMessage('Zalogowano pomyślnie');
            $this->redirect(Auth::getReturnToPage());
        } else {
            Flash::addMessage('Nieprawidłowy login lub hasło, spróbuj ponownie.', Flash::WARNING);
            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me
            ]);
        }
    }


    public function destroyAction()
    {
        Auth::logout();
        $this->redirect('/login/show-logout-message');
    }

    public function showLogoutMessageAction()
    {
        Flash::addMessage('Wylogowano pomyślnie');

        $this->redirect('/login/new');
    }
}
