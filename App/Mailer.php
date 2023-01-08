<?php

namespace App;

use App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\PersonalBudget\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\PersonalBudget\PHPMailer\src\SMTP.php';
require 'C:\xampp\htdocs\PersonalBudget\PHPMailer\src\Exception.php';

class Mailer
{
    public static function send($to, $subject, $text, $html)
    {
        $mail = new PHPMailer();

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true;

        $mail->Username = Config::username;
		$mail->Password = Config::password; 

        $mail->CharSet = "UTF-8";
        $mail->setFrom('...', 'Budżet Osobisty');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject; 
        $mail->Body = $text;

        $mail->send();
    }
}
