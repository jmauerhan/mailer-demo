<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$apiKey = 'C0wG3h1A5Fs5xNoLdM2S0w';
$mailer = new Mandrill($apiKey);

$app = new Src\App();
$app->setMailer($mailer);
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';