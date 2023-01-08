<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Expense;

/**
 * AddExpense controller (example)
 * PHP version 8.1.10
 */
class AddExpense extends Authenticated
{
     
    protected function before() 
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    public function expenseAction($arg1 = 0, $arg2 = 0)
    {
        $success = false; 
        $userExpenseCategories = Expense::getUserExpenseCategories($this->user->id);
        $userPaymentMethods = Expense::getUserPaymentMethods( $this->user->id);
       
        View::renderTemplate('AddExpense/expense.html', 
        [
            'user' => $this->user,
            'expenses' => $arg1,
            'errors' => $arg2,
            'success' => $success,
            'userExpenseCategories' => $userExpenseCategories,
            'userPaymentMethods' => $userPaymentMethods
        ] );
    }

    public function createAction()
    {
        $expense = new Expense($_POST);

        if ($expense->saveUserExpense($this->user->id)) 
        {
            Flash::addMessage('Wydatek dodano pomyślnie.');
        } else {
            Flash::addMessage('Operacja nie powiodła się.', Flash::WARNING);
        }
        $this->redirect('/addExpense/expense');
    }
}
