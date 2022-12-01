<?php

namespace App\Controllers;

/**
 * Authenticated base controller
 * PHP version 8.1.10
 */
abstract class Authenticated extends \Core\Controller
{
    //Require authentication
    protected function before()
    {
        $this->requireLogin();
    }
}
