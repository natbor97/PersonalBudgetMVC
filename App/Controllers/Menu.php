<?php

namespace App\Controllers;

use \Core\View;

/**
 * Menu controller
 * PHP version 8.1.10
 */
class Menu extends Authenticated
{

    public function mainmenuAction()
    {
        View::renderTemplate('Menu/mainmenu.html');
    }
}