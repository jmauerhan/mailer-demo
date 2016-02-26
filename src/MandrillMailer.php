<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';

class MandrillMailer implements MailerInterface
{
    /** @var \Mandrill $mandrill */
    private $mandrill;

    public function __construct(\Mandrill $mandrill)
    {
        $this->mandrill = $mandrill;
    }

    public function send($to, $from, $subject, $message)
    {
        $email = [
            'to'         => [['email' => $to]],
            'from_email' => $from,
            'subject'    => $subject,
            'text'       => $message
        ];
        $this->mandrill->messages->send($email);
        /**
         * Since Mandrill requires your sending domain to be verified even when using a test API key,
         * we will always get a failure, so we're going to skip checking the result of the send call for this demo.
         */
        return true;
    }
}