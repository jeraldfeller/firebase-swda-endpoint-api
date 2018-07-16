<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 7/16/2018
 * Time: 3:20 PM
 */
class SendMail
{
    public function sendNotification($data){
        $email = new PHPMailer();
        $email->isSMTP(false);
        $email->From      = FROM_EMAIL;
        $email->FromName  = FROM_EMAIL;
        $email->Subject   = $data['subject'];
        $email->Body      = $data['message'];
        $email->AddAddress( $data['emailTo'] );
        $return = $email->Send();
        return $return;
    }
}