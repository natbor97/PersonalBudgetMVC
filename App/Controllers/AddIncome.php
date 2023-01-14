<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Income;
use \App\Models\DateManager;

/**
 * AddIncome controller (example)
 * PHP version 8.1.10
 */

class AddIncome extends Authenticated
{

    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    public function incomeAction($arg1 = 0, $arg2 = 0)
    {
        $success = false;
        $userIncomeCategories = Income::getUserIncomeCategories($this->user->id);
        $currentDate = Flash::getCurrentDate();
        
        View::renderTemplate(
            'AddIncome/income.html',
            [
                'user' => $this->user,
                'incomes' => $arg1,
                'errors' => $arg2,
                'success' => $success,
                'userIncomeCategories' => $userIncomeCategories,
                'currentDate' => $currentDate
            ]
        );
    }

    public function createAction()
    {
        $income = new Income($_POST);

        if ($income->saveUserIncome($this->user->id)) 
        {
            Flash::addMessage('Przychód dodano pomyślnie.');
        } else {
            Flash::addMessage('Operacja nie powiodła się.', Flash::WARNING);
        }
        $this->redirect('/addIncome/income');
    }
}
