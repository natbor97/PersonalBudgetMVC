<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Expense;
use \App\Models\DateManager;

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
        $currentDate = Flash::getCurrentDate();
       
        View::renderTemplate('AddExpense/expense.html', 
        [
            'user' => $this->user,
            'expenses' => $arg1,
            'errors' => $arg2,
            'success' => $success,
            'userExpenseCategories' => $userExpenseCategories,
            'userPaymentMethods' => $userPaymentMethods,
            'currentDate' => $currentDate
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

    public function getExpenseCategoryLimitAction() 
    { 
      $expense_category = $_GET['expense_category'];
      $expense_date = $_GET['expense_date'];
      $expense_amount = $_GET['expense_amount'];
      $user_id = $this->user->id;
      $expense_limit = Expense::getExpensesCategoryLimit($user_id, $expense_category);
      $expenses_sum = static::getExpensesSumForLimit($expense_date, $expense_category);
      $expense_limit_alert = $expense_limit - $expenses_sum - $expense_amount;
      if($expense_limit_alert < 0)
      {
          $expense_alert = "Uwaga! Przekroczysz limit o ".-($expense_limit_alert)." zł dla kategorii: ".$expense_category."!";                   
      }
      else
      {
          $expense_alert = "W kategorii '".$expense_category. "' do wydania pozostało: ".$expense_limit_alert." zł" ;  
      }

      echo json_encode($expense_alert, JSON_UNESCAPED_UNICODE);
        
    }

    public function getExpensesSumForLimit($date, $expense_category)
    {
        $user_id = $this->user->id; 
        $year = date("Y",strtotime($date));
        $month = date("m",strtotime($date));
        $day = date("d",strtotime($date));

        $expense_date = DateManager::getFirstSecondDate($year,$month,$day);        
        $expenses_sum = Expense::getExpensesSumForLimit($expense_date, $expense_category, $user_id);
        return $expenses_sum;   
    }
}
