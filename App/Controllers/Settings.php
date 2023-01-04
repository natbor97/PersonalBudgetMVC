<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Models\User;
use \App\Flash;
use PDO;

class Settings extends Authenticated
{
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    public function settingsAction()
    {
        $userIncomeCategories = Income::getUserIncomeCategories($this->user->id);
        $userExpenseCategories = Expense::getUserExpenseCategories($this->user->id);   
        $userExpenseLimit = Expense::getUserExpenseLimit($this->user->id);   
        $userPaymentMethods = Expense::getUserPaymentMethods($this->user->id);
      
        View::renderTemplate(
            'Settings/new.html',
            [
                'userIncomeCategories' => $userIncomeCategories,
                'userExpenseCategories' => $userExpenseCategories,
                'userPaymentMethods' => $userPaymentMethods,
                'userExpenseLimit' => $userExpenseLimit,
                'user' => $this->user
            ]
        );
    }

    public function createIncomesAction()
    {
        $settings = new User($_POST);

        if ($settings->addCategoryIncome()) {

            Flash::addMessage('Dodano nową kategorię przychodów.');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Taka kategoria już istnieje!', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function createExpensesAction()
    {
        $settingsExpenses = new User($_POST);

        if ($settingsExpenses->addCategoryExpense()) {

            Flash::addMessage('Dodano nową kategorię wydatków.');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Taka kategoria już istnieje!', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function createPayAction()
    {
        $settingsPay = new User($_POST);

        if ($settingsPay->addPay()) {

            Flash::addMessage('Dodano nowy sposób płatności');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Ten sposób płatności już istnieje!', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function deleteIncomesAction()
    {
        $settingsIdRemove = new User($_POST);
        $settingsRemove = new User($_POST);
        $settingsIdRemove->removeIdCategoryIncome();
        if ($settingsRemove->removeCategoryIncome()) {

            Flash::addMessage('Wybrana kategoria przychodów została usunięta');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Wybrana kategoria przychodów nie została usunięta', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function deleteExpensesAction()
    {
        $settingsIdRemoveExpenses = new User($_POST);
        $settingsRemoveExpenses = new User($_POST);
        $settingsIdRemoveExpenses->removeIdCategoryExpense();
        if ($settingsRemoveExpenses->removeCategoryExpense()) {

            Flash::addMessage('Wybrana kategoria wydatków została usunięta');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Wybrana kategoria wydatków nie została usunięta', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function deletePayAction()
    {
        $settingsIdRemovePay = new User($_POST);
        $settingsRemovePay = new User($_POST);
        $settingsIdRemovePay->removeIdPay();
        if ($settingsRemovePay->removePay()) {

            Flash::addMessage('Wybrany sposób płatności został usunięty');
            $this->redirect('/settings/settings');
        } else {

            Flash::addMessage('Wybrany sposób płatności nie został usunięty', Flash::WARNING);
            $this->redirect('/settings/settings');
        }
    }

    public function removeUser()
    {
	$deleteAllIncomes = new User($_POST);
	$deleteAllExpenses = new User($_POST);
	$deleteAllIncomesCategory = new User($_POST);
	$deleteAllExpensesCategory = new User($_POST);
	$deleteAllPaymentCategory = new User($_POST);		
	$deleteUser = new User($_POST);
	$deleteAllIncomes->removeAllIncomes();
	$deleteAllExpenses->removeAllExpenses();
	$deleteAllIncomesCategory->removeAllIncomesCategory();
	$deleteAllExpensesCategory->removeAllExpensesCategory();
	$deleteAllPaymentCategory->removeAllPaymentCategory();
	if ($deleteUser->removeUserFromApp()){
			
	    Flash::addMessage('Twoje konto zostało usunięte.');
            $this->redirect('/logout');

        } else {

	    Flash::addMessage('Twoje konto nie zostało usunięte.', Flash::WARNING);
	    $this->redirect('/settings/settings');
        }
    }

    public function editIncomesAction()
    {
	$settingsUpdate = new User($_POST);
		
	if ($settingsUpdate->updateCategoryIncome()){
						
	    Flash::addMessage('Zmieniono nazwę wybranej kategorii.');
            $this->redirect('/settings/settings');

        } else {

	    Flash::addMessage('Nie można zmienić nazwy kategorii na taką, która już istnieje!', Flash::WARNING);
	    $this->redirect('/settings/settings');
        }
    }
	
    public function editExpensesAction()
    {
	$settingsUpdateExpenses = new User($_POST);
		
        if ($settingsUpdateExpenses->updateCategoryExpense()){
						
	    Flash::addMessage('Zmieniono nazwę wybranej kategorii.');
            $this->redirect('/settings/settings');

        } else {

	    Flash::addMessage('Nie można zmienić nazwy kategorii na taką, która już istnieje!', Flash::WARNING);
	    $this->redirect('/settings/settings');
        }
    }

    public function editPayAction()
    {
	$settingsEditPay = new User($_POST);
		
	if ($settingsEditPay->updatePay()){
						
	    Flash::addMessage('Zmieniono nazwę wybranego sposobu płatności.');
            $this->redirect('/settings/settings');

        } else {

	     Flash::addMessage('Nie można zmienić sposobu płatności na taki, który już istnieje!', Flash::WARNING);
	     $this->redirect('/settings/settings');
        }
    }

    public function editUsernameAction()
    {
        $settingsEditUsername = new User($_POST);

        if ($settingsEditUsername->updateUsername()){
						
            Flash::addMessage('Zmieniono nazwę użytkownika.');
                $this->redirect('/settings/settings');
    
            } else {
    
             Flash::addMessage('Nie zmieniono nazwy użytkownika.', Flash::WARNING);
             $this->redirect('/settings/settings');
            }
    }

    public function limitExpensesAction()
    {
	$settingsLimitExpenses = new User($_POST);
		
	if ($settingsLimitExpenses->limitCategoryExpense()){
						
	    Flash::addMessage('Limit dla wybranego wydatku został wprowadzony');
            $this->redirect('/settings/settings');

        } else {

	    Flash::addMessage('Limit dla wybranego wydatku nie został wprowadzony', Flash::WARNING);
	    $this->redirect('/settings/settings');
        }
    }
}
