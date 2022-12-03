<?php

namespace App\Models;

use \Core\View;
use PDO;

class Income extends \Core\Model
{
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    public static function getUserIncomeCategories($id)
    {
        $sql = 'SELECT id, name FROM incomes_category_assigned_to_users WHERE user_id=:id';
        $db = static::getDB();

        $query_income_categories = $db->prepare($sql);

        $query_income_categories->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $query_income_categories->execute();
        $income_categories = $query_income_categories->fetchAll();

        return $income_categories;
    }

    public function saveUserIncome($user_id)
    {
        $this->validate();

        if (empty($this->errors)) {
            $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
        VALUES (:user_id, :income_category_id, :amount, :date_of_income, :income_comment)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':income_category_id', $this->getUserCategoryId($user_id), PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->incomeAmount, PDO::PARAM_STR);
            $stmt->bindValue(':date_of_income', $this->incomeDate, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $this->incomeComment, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public function getUserCategoryId($user_id)
    {
        $sql = 'SELECT id, user_id, name FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :name';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $this->incomeCategory, PDO::PARAM_STR);
        $stmt->execute();

        $categories = $stmt->fetch();

        return $categories['id'];
    }

    public function validate()
    {
      // date
      if ($this->incomeDate == '') {
        $this->errors[] = 'Wprowadź datę';
      }
    }
}
