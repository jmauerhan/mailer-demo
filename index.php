<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$apiKey     = 'XgJLUbVFS1YkZyEr';
$apiUrl     = "https://api.sendinblue.com/v2.0";
$sendInBlue = new \Sendinblue\Mailin($apiUrl, $apiKey);
$mailer     = new \Src\SendInBlueMailer($sendInBlue);

$app = new Src\App();
$app->setMailer($mailer);
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';