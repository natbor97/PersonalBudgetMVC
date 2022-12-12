<?php

namespace App;

use App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/home/klient.dhosting.pl/ppp/budget.natalia-borkowska.profesjonalnyprogramista.pl/PHPMailer/src/PHPMailer.php';
require '/home/klient.dhosting.pl/ppp/budget.natalia-borkowska.profesjonalnyprogramista.pl/PHPMailer/src/SMTP.php';
require '/home/klient.dhosting.pl/ppp/budget.natalia-borkowska.profesjonalnyprogramista.pl/PHPMailer/src/Exception.php';


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
        $mail->setFrom('natalia.borkowska.programista@gmail.com', 'Budżet Osobisty');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject; 
        $mail->Body = $text;

        $mail->send();
    }
}
