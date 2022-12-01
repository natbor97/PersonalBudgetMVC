<?php

namespace App\Controllers;

use \Core\View;

/**
 * AddIncome controller (example)
 * PHP version 8.1.10
 */

class AddIncome extends Authenticated
{

    public function incomeAction()
    {
        View::renderTemplate('AddIncome/income.html');
    }

}
