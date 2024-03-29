<?php

/**
 * Front controller
 * PHP version 8.1.10
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'new']);

$router->add('{controller}/{action}');

$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);

$router->add('addExpense/getExpenseCategoryLimit/', ['controller' => 'addExpense', 'action' => 'getExpenseCategoryLimit']);
$router->add('{controller}/{id:\d+}/{action}'); 

$router->dispatch($_SERVER['QUERY_STRING']);
