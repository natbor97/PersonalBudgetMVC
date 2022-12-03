<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Balance;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Flash;
use \App\Auth;
use \App\Models\DateManager;

class showBalance extends Authenticated
{
    protected function before() 
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    public function newAction()
    {
        View::renderTemplate('ShowBalance/new.html');
    }

    public function currentMonthAction($arg1='', $arg2='')
    {
        $success = false; 
        $date = DateManager::getCurrentMonthDate();        
        $incomeBalanceTable = Balance::getIncomes($date,$this->user->id);
        $expenseBalanceTable = Balance::getExpenses( $date,$this->user->id);
        $expenseSum = Balance::getExpensesSum($date,$this->user->id);
        $incomeSum = Balance::getIncomesSum($date,$this->user->id);        
        $balance = Balance::getBalance($date,$this->user->id);
        $incomeDataPoints = Balance::getIncomes($date,$this->user->id);
        $expenseDataPoints = Balance::getExpenses($date,$this->user->id);
        $userIncomeCategories = Income::getUserIncomeCategories( $this->user->id); 
        $userExpenseCategories = Expense::getUserExpenseCategories( $this->user->id);
        $userPaymentMethods = Expense::getUserPaymentMethods( $this->user->id);
        

        View::renderTemplate('ShowBalance/new.html', 
        [
            'user' => $this->user,
            'incomeBalanceTable' => $incomeBalanceTable,
            'userIncomeCategories' => $userIncomeCategories,
            'userExpenseCategories' => $userExpenseCategories,
            'userPaymentMethods' => $userPaymentMethods,
            'expenseBalanceTable' => $expenseBalanceTable,
            'expenseSum' => $expenseSum,
            'incomeSum' => $incomeSum,            
            'balance' => $balance,
            'incomeDataPoints' => $incomeDataPoints,
            'expenseDataPoints' => $expenseDataPoints,          
            'start_date' => $date['start_date'],
            'end_date' => $date['end_date'],
            'success' => $arg1,
            'error' => $arg2
        
        ] );
    }

    public function lastMonthAction($arg1='', $arg2='')
    {
        $success = false; 
        $date = DateManager::getlastMonthDate();        
        $incomeBalanceTable = Balance::getIncomes( $date,$this->user->id);
        $expenseBalanceTable = Balance::getExpenses( $date,$this->user->id);
        $expenseSum = Balance::getExpensesSum($date,$this->user->id);
        $incomeSum = Balance::getIncomesSum($date,$this->user->id); 
        $balance = Balance::getBalance($date,$this->user->id);
        $incomeDataPoints = Balance::getIncomes($date,$this->user->id);
        $expenseDataPoints = Balance::getExpenses($date,$this->user->id);
        $userIncomeCategories = Income::getUserIncomeCategories( $this->user->id);
        $userExpenseCategories = Expense::getUserExpenseCategories( $this->user->id);
        $userPaymentMethods = Expense::getUserPaymentMethods( $this->user->id);
        
        View::renderTemplate('ShowBalance/new.html', 
        [
            'user' => $this->user,
            'incomeBalanceTable' => $incomeBalanceTable,
            'userIncomeCategories' => $userIncomeCategories,
            'userExpenseCategories' => $userExpenseCategories,
            'userPaymentMethods' => $userPaymentMethods,
            'expenseBalanceTable' => $expenseBalanceTable,
            'expenseSum' => $expenseSum,
            'incomeSum' => $incomeSum,    
            'balance' => $balance,
            'incomeDataPoints' => $incomeDataPoints,
            'expenseDataPoints' => $expenseDataPoints, 
            'start_date' => $date['start_date'],
            'end_date' => $date['end_date'],
            'success' => $arg1,
            'error' => $arg2
            
        ] );
    }

    public function currentYearAction($arg1='', $arg2='')
    {
        $success = false; 
        $date = DateManager::getCurrentYearDate();
        $userExpenseCategories = Expense::getUserExpenseCategories( $this->user->id);
        $userPaymentMethods = Expense::getUserPaymentMethods( $this->user->id);      
        $incomeBalanceTable = Balance::getIncomes($date,$this->user->id);
        $expenseBalanceTable = Balance::getExpenses( $date,$this->user->id);
        $expenseSum = Balance::getExpensesSum($date,$this->user->id);
        $incomeSum = Balance::getIncomesSum($date,$this->user->id); 
        $balance = Balance::getBalance($date,$this->user->id);
        $incomeDataPoints = Balance::getIncomes($date,$this->user->id);
        $expenseDataPoints = Balance::getExpenses($date,$this->user->id);
        $userIncomeCategories = Income::getUserIncomeCategories( $this->user->id); 

        View::renderTemplate('ShowBalance/new.html', 
        [
            'user' => $this->user,
            'userExpenseCategories' => $userExpenseCategories,
            'userPaymentMethods' => $userPaymentMethods,
            'incomeBalanceTable' => $incomeBalanceTable,
            'userIncomeCategories' => $userIncomeCategories,
            'expenseBalanceTable' => $expenseBalanceTable,
            'expenseSum' => $expenseSum,
            'incomeSum' => $incomeSum,    
            'balance' => $balance,
            'incomeDataPoints' => $incomeDataPoints,
            'expenseDataPoints' => $expenseDataPoints, 
            'start_date' => $date['start_date'],
            'end_date' => $date['end_date'],
            'success' => $arg1,
            'error' => $arg2
        ] );
    }

    public function selectedDateAction($arg1='', $arg2='', $arg3='', $arg4='' )
    {               
        $success = false;         
        $date = DateManager::getUserSelectedDate($arg3,$arg4);
        if (empty($date))
        {
            $message = '';
            $error = "Wystąpił błąd, spróbuj ponownie";
            $this -> currentMonthAction($message, $error);
        }
        else
        {

            $userExpenseCategories = Expense::getUserExpenseCategories( $this->user->id);
            $userPaymentMethods = Expense::getUserPaymentMethods( $this->user->id);       
            $incomeBalanceTable = Balance::getIncomes( $date,$this->user->id);
            $expenseBalanceTable = Balance::getExpenses( $date,$this->user->id);
            $expenseSum = Balance::getExpensesSum($date,$this->user->id);
            $incomeSum = Balance::getIncomesSum($date,$this->user->id); 
            $balance = Balance::getBalance($date,$this->user->id);
            $incomeDataPoints = Balance::getIncomes($date,$this->user->id);
            $expenseDataPoints = Balance::getExpenses($date,$this->user->id); 
            $userIncomeCategories = Income::getUserIncomeCategories( $this->user->id);
           

            View::renderTemplate('ShowBalance/new.html', 
            [

                'user' => $this->user,            
                'userExpenseCategories' => $userExpenseCategories,
                'userPaymentMethods' => $userPaymentMethods,
                'incomeBalanceTable' => $incomeBalanceTable,
                'userIncomeCategories' => $userIncomeCategories,
                'expenseBalanceTable' => $expenseBalanceTable,
                'expenseSum' => $expenseSum,
                'incomeSum' => $incomeSum,    
                'balance' => $balance,
                'incomeDataPoints' => $incomeDataPoints,
                'expenseDataPoints' => $expenseDataPoints, 
                'start_date' => $date['start_date'],
                'end_date' => $date['end_date'],
                'success' => $arg1,
                'error' => $arg2
                
            ] );

        }   
    }


}