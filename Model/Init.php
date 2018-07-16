<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//Load Composer's autoloader
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require 'SendMail.php';

define('FROM_EMAIL', 'no-reply@safeworkplacedocs.com');