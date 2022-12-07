<?php

namespace App\Models;

use PDO;
use \Core\View;
use \App\Flash;

class balance extends \Core\Model
{
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function getIncomes($date, $id)
    {

        $sql = 'SELECT inc.id, inc.amount, inc.date_of_income, inc.income_category_assigned_to_user_id, inc.income_comment, cat.name 
                FROM incomes as inc, incomes_category_assigned_to_users AS cat 
                WHERE inc.date_of_income BETWEEN :start_date AND :end_date AND inc.user_id = :user_id AND inc.income_category_assigned_to_user_id = cat.id 
                ORDER BY inc.date_of_income ASC';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':start_date', $date['start_date'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $date['end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getIncomesSum($date, $id)
    {
        $sql = 'SELECT ROUND(SUM(inc.amount), 2) as incomeSumArray , cat.name as income_name  
                FROM incomes as inc, incomes_category_assigned_to_users as cat 
                WHERE inc.user_id = :user_id AND inc.user_id = cat.user_id AND cat.id = inc.income_category_assigned_to_user_id AND inc.date_of_income 
                BETWEEN :start_date AND :end_date GROUP BY cat.name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':start_date', $date['start_date'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $date['end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $incomeSumArray = $stmt->fetchAll();
        $incomeSum = array_sum(array_column($incomeSumArray, 'incomeSumArray'));

        return $incomeSum;
    }


    public static function getExpenses($date, $id)
    {
        $sql = 'SELECT exp.id, exp.amount, exp.date_of_expense, exp.expense_category_assigned_to_user_id, exp.payment_method_assigned_to_user_id, exp.expense_comment, cat.name as expense_name, pay.name as payment_name 
                FROM expenses AS exp, expenses_category_assigned_to_users AS cat, payment_methods_assigned_to_users AS pay 
                WHERE exp.date_of_expense BETWEEN :start_date AND :end_date AND exp.user_id = :user_id AND pay.user_id = :user_id AND  exp.expense_category_assigned_to_user_id = cat.id AND exp.payment_method_assigned_to_user_id = pay.id 
                ORDER BY exp.date_of_expense ASC';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':start_date', $date['start_date'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $date['end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute();

        return $stmt->fetchAll();
    }


    public static function getExpensesSum($date, $id)
    {
        $sql = 'SELECT ROUND(SUM(exp.amount), 2) as expenseSumArray, cat.name as expense_name FROM expenses as exp, expenses_category_assigned_to_users as cat 
                WHERE exp.user_id = :user_id AND exp.user_id = cat.user_id AND cat.id = exp.expense_category_assigned_to_user_id AND exp.date_of_expense 
                BETWEEN :start_date AND :end_date GROUP BY cat.name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':start_date', $date['start_date'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $date['end_date'], PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $expenseSumArray = $stmt->fetchAll();
        $expenseSum = array_sum(array_column($expenseSumArray, 'expenseSumArray'));

        return $expenseSum;
    }

    public static function getBalance($date, $id)
    {
        $incomeSum = static::getIncomesSum($date, $id);
        $expenseSum = static::getExpensesSum($date, $id);
        $balanceSum = $incomeSum - $expenseSum;
        return $balanceSum;
    }
}
