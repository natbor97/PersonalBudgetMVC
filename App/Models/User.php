<?php

namespace App\Models;

use \App\Token;
use \App\Mailer;
use \Core\View;

use PDO;

/**
 * Example user model
 * PHP version 8.1.10
 */
class User extends \Core\Model
{
    public $errors = [];

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $sql = 'INSERT INTO users (username, password, email, activation_hash)
                    VALUES (:username, :password_hash, :email, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $user = static::findByEmail($this->email);
                return $this->copyDefaultCategories($user->id);
            }
        }
        return false;
    }

    public function validate()
    {
        // name
        if ($this->username == '') {
            $this->errors[] = 'Podaj login';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Wprowadź poprawny adres email!';
        }

        if (static::emailExists($this->email)) {
            $this->errors[] = 'Podany adres email jest już zajęty!';
        }

        // password
        if (strlen($this->password) < 6 || (preg_match('/.*\d+.*/i', $this->password) == 0)) {
            $this->errors[] = 'Hasło powinno zawierać minimum 6 znaków i posiadać co najmniej jedną cyfrę!';
        }
    }

    public static function emailExists($email)
    {
        return static::findByEmail($email) !== false;
    }

    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        return $stmt->fetch();
    }

    protected function copyDefaultCategories($userId)
    {
        $db = static::getDB();
        $sql = 'INSERT INTO payment_methods_assigned_to_users (id, user_id, name) SELECT NULL, :newUserId, name FROM payment_methods_default';
        $copyPayments = $db->prepare($sql);
        $copyPayments->bindValue(':newUserId', $userId, PDO::PARAM_INT);

        $sql = 'INSERT INTO incomes_category_assigned_to_users (id, user_id, name) SELECT NULL, :newUserId, name FROM incomes_category_default';
        $copyIncomes = $db->prepare($sql);
        $copyIncomes->bindValue(':newUserId', $userId, PDO::PARAM_INT);

        $sql = 'INSERT INTO expenses_category_assigned_to_users (id, user_id, name) SELECT NULL, :newUserId, name FROM expenses_category_default';
        $copyExpenses = $db->prepare($sql);
        $copyExpenses->bindValue(':newUserId', $userId, PDO::PARAM_INT);

        return ($copyPayments->execute() && $copyIncomes->execute() && $copyExpenses->execute());
    }

    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);
        if ($user && $user->is_active) {
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }
        return false;
    }

    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();


        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mailer::send($this->email, 'Aktywacja konta', $text, $html);
    }

    public function sendActivationNewEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mailer::send($this->newEmail, 'Aktywacja konta', $text, $html);
    }

    public static function findByToken($hashed_token)
    {
        $sql = 'SELECT * FROM users WHERE activation_hash = :hashed_token';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();
        $user = static::findByToken($hashed_token);

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function addCategoryIncome()
    {
        $this->validateCategoryIncomes();
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "INSERT INTO incomes_category_assigned_to_users (id, user_id, name)
					VALUES (NULL, '$user_id', :categoryAddIncomes)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryAddIncomes', $this->categoryAddIncomes, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validateCategoryIncomes()
    {

        if ($this->categoryAddIncomes == '') {
            $this->errors[] = 'Kategoria jest wymagana';
        }

        if (static::categoryIncomeExists($this->categoryAddIncomes, $this->id ?? null)) {
            $this->errors[] = 'Kategoria już istnieje';
        }
    }

    public static function categoryIncomeExists($categoryAddIncomes, $ignore_id = null)
    {
        $user = static::findByCategoryIncome($categoryAddIncomes);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByCategoryIncome($categoryAddIncomes)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE name = :categoryAddIncomes';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':categoryAddIncomes', $categoryAddIncomes, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function addCategoryExpense()
    {
        $this->validateCategoryExpenses();
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "INSERT INTO expenses_category_assigned_to_users (id, user_id, name)
					VALUES (NULL, '$user_id', :categoryAddExpenses)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryAddExpenses', $this->categoryAddExpenses, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validateCategoryExpenses()
    {

        if ($this->categoryAddExpenses == '') {
            $this->errors[] = 'Kategoria jest wymagana';
        }

        if (static::categoryExpenseExists($this->categoryAddExpenses, $this->id ?? null)) {
            $this->errors[] = 'Kategoria już istnieje';
        }
    }

    public static function categoryExpenseExists($categoryAddExpenses, $ignore_id = null)
    {
        $user = static::findByCategoryExpense($categoryAddExpenses);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByCategoryExpense($categoryAddExpenses)
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE name = :categoryAddExpenses';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':categoryAddExpenses', $categoryAddExpenses, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function addPay()
    {
        $this->validatePay();
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "INSERT INTO payment_methods_assigned_to_users (id, user_id, name)
					VALUES (NULL, '$user_id', :addPay)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':addPay', $this->addPay, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validatePay()
    {

        if ($this->addPay == '') {
            $this->errors[] = 'Sposób płatności jest wymagany';
        }

        if (static::addPayExists($this->addPay, $this->id ?? null)) {
            $this->errors[] = 'Sposób płatnośc już istnieje';
        }
    }

    public static function addPayExists($addPay, $ignore_id = null)
    {
        $user = static::findByAddPay($addPay);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByAddPay($addPay)
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE name = :addPay';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':addPay', $addPay, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function removeIdCategoryIncome()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "UPDATE incomes SET income_category_assigned_to_user_id =(SELECT id FROM incomes_category_assigned_to_users WHERE user_id='$user_id' AND name=:chosenCategoryIncomes) WHERE user_id='$user_id' AND income_category_assigned_to_user_id =(SELECT id FROM incomes_category_assigned_to_users WHERE user_id='$user_id' AND name=:categoryIncomes)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryIncomes', $this->categoryIncomes, PDO::PARAM_STR);
            $stmt->bindValue(':chosenCategoryIncomes', $this->chosenCategoryIncomes, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removeCategoryIncome()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM incomes_category_assigned_to_users WHERE user_id='$user_id' AND name=:categoryIncomes";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryIncomes', $this->categoryIncomes, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removeIdCategoryExpense()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "UPDATE expenses SET expense_category_assigned_to_user_id =(SELECT id FROM expenses_category_assigned_to_users WHERE user_id='$user_id' AND name=:chosenCategoryExpenses) WHERE user_id='$user_id' AND expense_category_assigned_to_user_id =(SELECT id FROM expenses_category_assigned_to_users WHERE user_id='$user_id' AND name=:categoryExpenses)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryExpenses', $this->categoryExpenses, PDO::PARAM_STR);
            $stmt->bindValue(':chosenCategoryExpenses', $this->chosenCategoryExpenses, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removeCategoryExpense()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM expenses_category_assigned_to_users WHERE user_id='$user_id' AND name=:categoryExpenses";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryExpenses', $this->categoryExpenses, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removeIdPay()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "UPDATE expenses SET payment_method_assigned_to_user_id =(SELECT id FROM payment_methods_assigned_to_users WHERE user_id='$user_id' AND name='Inne') WHERE user_id='$user_id' AND payment_method_assigned_to_user_id  =(SELECT id FROM payment_methods_assigned_to_users WHERE user_id='$user_id' AND name=:removePay)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':removePay', $this->removePay, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removePay()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM payment_methods_assigned_to_users WHERE user_id='$user_id' AND name=:removePay";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':removePay', $this->removePay, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function removeUserFromApp()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM users WHERE id='$user_id' ";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }

    public function removeAllIncomes()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM incomes WHERE user_id='$user_id' ";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }

    public function removeAllExpenses()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM expenses WHERE user_id='$user_id' ";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }



    public function removeAllIncomesCategory()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM incomes_category_assigned_to_users WHERE user_id='$user_id'";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }

    public function removeAllExpensesCategory()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM expenses_category_assigned_to_users WHERE user_id='$user_id'";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }

    public function removeAllPaymentCategory()
    {
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "DELETE FROM payment_methods_assigned_to_users WHERE user_id='$user_id'";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute();
        }

        return false;
    }

    public function updateCategoryIncome()
    {
        $this->validateCategoryEditIncomes();
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "UPDATE incomes_category_assigned_to_users SET name = :categoryEditIncomes WHERE name = :categoryIncomes AND user_id='$user_id'";


            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryIncomes', $this->categoryIncomes, PDO::PARAM_STR);
            $stmt->bindValue(':categoryEditIncomes', $this->categoryEditIncomes, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validateCategoryEditIncomes()
    {
        if (static::categoryEditIncomesExists($this->categoryEditIncomes, $this->id ?? null)) {
            $this->errors[] = 'Kategoria już istnieje';
        }
    }

    public static function categoryEditIncomesExists($categoryEditIncomes, $ignore_id = null)
    {
        $user = static::findByCategoryEditIncome($categoryEditIncomes);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByCategoryEditIncome($categoryEditIncomes)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE name = :categoryEditIncomes';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':categoryEditIncomes', $categoryEditIncomes, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function updateCategoryExpense()
    {
        $this->validateCategoryEditExpenses();
        $user_id = $_SESSION['user_id'];

        if (empty($this->errors)) {

            $sql = "UPDATE expenses_category_assigned_to_users SET name = :categoryEditExpenses WHERE name = :categoryExpenses AND user_id='$user_id'";


            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':categoryExpenses', $this->categoryExpenses, PDO::PARAM_STR);
            $stmt->bindValue(':categoryEditExpenses', $this->categoryEditExpenses, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validateCategoryEditExpenses()
    {
        if (static::categoryEditExpensesExists($this->categoryEditExpenses, $this->id ?? null)) {
            $this->errors[] = 'Kategoria już istnieje';
        }
    }

    public static function categoryEditExpensesExists($categoryEditExpenses, $ignore_id = null)
    {
        $user = static::findByCategoryEditExpenses($categoryEditExpenses);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByCategoryEditExpenses($categoryEditExpenses)
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE name = :categoryEditExpenses';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':categoryEditExpenses', $categoryEditExpenses, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function updatePay()
    	{
		$this->validateUpdatePay();
		$user_id = $_SESSION['user_id'];
        
		if (empty($this->errors)) {

		    $sql = "UPDATE payment_methods_assigned_to_users SET name = :updatePay WHERE name = :editPay AND user_id='$user_id'";


		    $db = static::getDB();
		    $stmt = $db->prepare($sql);

		    $stmt->bindValue(':editPay', $this->editPay, PDO::PARAM_STR);
		    $stmt->bindValue(':updatePay', $this->updatePay, PDO::PARAM_STR);

		    return $stmt->execute();
		}

        	return false;
    	}

    public function validateUpdatePay()
    {
        if (static::updatePayExists($this->updatePay, $this->id ?? null)) {
            $this->errors[] = 'Kategoria już istnieje';
        }
    }

    public static function updatePayExists($updatePay, $ignore_id = null)
    {
        $user = static::findByUpdatePay($updatePay);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByUpdatePay($updatePay)
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE name = :updatePay';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':updatePay', $updatePay, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function limitCategoryExpense()
    	{
		$user_id = $_SESSION['user_id'];
        
		if (empty($this->errors)) {

        	    $sql = "UPDATE expenses_category_assigned_to_users SET expenseLimit = :amountLimit WHERE name = :categoryExpenses AND user_id='$user_id'";

		    $db = static::getDB();
		    $stmt = $db->prepare($sql);

		    $stmt->bindValue(':categoryExpenses', $this->categoryExpenses, PDO::PARAM_STR);
		    $stmt->bindValue(':amountLimit', $this->amountLimit, PDO::PARAM_STR);

		    return $stmt->execute();
        	}

		return false;
	}

    public function updateUsername() {
        $user_id = $_SESSION['user_id'];

        $sql = "UPDATE users SET username = :newUsername";

		    $db = static::getDB();
		    $stmt = $db->prepare($sql);

		    $stmt->bindValue(':newUsername', $this->newUsername, PDO::PARAM_STR);

		    return $stmt->execute();
    }

    public function updateEmail() {

        $user_id = $_SESSION['user_id'];
        $updateToken = new Token();
        $update_hashed_token = $updateToken->getHash();
        $this->activation_token = $updateToken->getValue();

        if (static::emailExists($this->newEmail)) {
            $this->errors[] = 'Podany adres email jest już zajęty!';
        }
        else {
        $sql = "UPDATE users SET email = :newEmail, activation_hash = :activation_hash, is_active = 0 WHERE id = '$user_id'";

		$db = static::getDB();
		$stmt = $db->prepare($sql);

		$stmt->bindValue(':newEmail', $this->newEmail, PDO::PARAM_STR);
        $stmt->bindValue(':activation_hash', $update_hashed_token, PDO::PARAM_STR);

		return $stmt->execute();
        }
    }

    public function updatePassword() {

        $user_id = $_SESSION['user_id'];
        $user = static::findByID($user_id);
        $oldPassword = $_POST['oldPassword'];
        
        if(password_verify($oldPassword, $user->password)){
            if (strlen($this->newPassword) < 6 || (preg_match('/.*\d+.*/i', $this->newPassword) == 0)) {
                $this->errors[] = 'Hasło powinno zawierać minimum 6 znaków i posiadać co najmniej jedną cyfrę!';
            }
            else{

            $newPassword_hash = password_hash($this->newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = :newPassword_hash WHERE id='$user_id'";

		    $db = static::getDB();
		    $stmt = $db->prepare($sql);

		    $stmt->bindValue(':newPassword_hash', $newPassword_hash, PDO::PARAM_STR);

		    return $stmt->execute();
            }
        }
        else{
            $this->errors[] = 'Niepoprawne hasło!';
        }
    
    }



}
