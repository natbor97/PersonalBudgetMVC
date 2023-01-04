<?php

namespace App\Models;

use \Core\View;
use PDO;


class Expense extends \Core\Model
{
  public function __construct($data = [])
  {
    foreach ($data as $key => $value) {
      $this->$key = $value;
    };
  }

  public static function getUserExpenseCategories($id)
  {
    $sql = 'SELECT id, name FROM expenses_category_assigned_to_users WHERE user_id=:id';

    $db = static::getDB();
    $query_expense_categories = $db->prepare($sql);

    $query_expense_categories->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $query_expense_categories->execute();
    $expense_categories = $query_expense_categories->fetchAll();

    return $expense_categories;
  }

  public static function getUserExpenseLimit($id)
  {
    $sql = 'SELECT id, amount_limit FROM expenses_category_assigned_to_users WHERE user_id=:id';

    $db = static::getDB();
    $query_expense_limit = $db->prepare($sql);

    $query_expense_limit->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $query_expense_limit->execute();
    $expense_limit = $query_expense_limit->fetchAll();

    return $expense_limit;
  }

  public static function getUserPaymentMethods($id)
  {
    $sql = 'SELECT id, name FROM payment_methods_assigned_to_users WHERE user_id=:id';

    $db = static::getDB();
    $query_payment_methods = $db->prepare($sql);

    $query_payment_methods->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $query_payment_methods->execute();
    $payment_methods = $query_payment_methods->fetchAll();

    return $payment_methods;
  }

  public function saveUserExpense($user_id)
  {
    $this->validate();

    if (empty($this->errors)) {
      $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
              VALUES (:user_id, :expense_category_id, :payment_method_id, :amount, :date_of_expense, :expense_comment)';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':expense_category_id', $this->getUserCategoryId($user_id), PDO::PARAM_INT);
      $stmt->bindValue(':payment_method_id', $this->getUserPaymentId($user_id), PDO::PARAM_INT);
      $stmt->bindValue(':amount', $this->expenseAmount, PDO::PARAM_STR);
      $stmt->bindValue(':date_of_expense', $this->expenseDate, PDO::PARAM_STR);
      $stmt->bindValue(':expense_comment', $this->expenseComment, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }

  public function getUserCategoryId($user_id)
  {
    $sql = 'SELECT id, user_id, name FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :name';

    $db = static::getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $this->expenseCategory, PDO::PARAM_STR);
    $stmt->execute();

    $categories = $stmt->fetch();

    return $categories['id'];
  }

  public function getUserPaymentId($user_id)
  {
    $sql = 'SELECT id, user_id, name FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :name';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $this->paymentMethods, PDO::PARAM_STR);
    $stmt->execute();

    $methods = $stmt->fetch();

    return $methods['id'];
  }

  public function validate()
  {
    // date
    if ($this->expenseDate == '') {
      $this->errors[] = 'Wprowadź datę';
    }
  }
}
