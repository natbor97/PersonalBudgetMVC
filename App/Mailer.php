<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'C:\xampp\htdocs\PersonalBudget\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\PersonalBudget\PHPMailer\src\SMTP.php';

class Mailer
{
    public static function send($to, $subject, $text, $html)
    {
        $mail = new PHPMailer();

        $mail->isSMTP();
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;

        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true;

        $mail->Username = 'natalciaa03@gmail.com'; // Podaj swój login gmail
		$mail->Password = 'uxzglmtjmtllvltz'; // Podaj swoje hasło do aplikacji

        $mail->CharSet = "UTF-8";
        $mail->setFrom('natalia.borkowska.programista@gmail.com', 'Budżet Osobisty');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = "Aktywacja konta"; 
        $mail->Body = "Witaj, Jezeli to czytasz, to znaczy, ze udalo sie poprawnie wyslac e-maila za pomoca SMTP!";

        $mail->send();
    }
}
