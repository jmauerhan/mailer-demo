<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';

use Mandrill;

Class App
{
    /** @var string $from */
    private $from = 'app@example.com';
    /** @var string $subject */
    private $subject = 'Welcome to the App!';
    /** @var string $message */
    private $message = 'This is a welcome email!';
    /** @var string $apiKey */
    private $apiKey = 'C0wG3h1A5Fs5xNoLdM2S0w';
    /** @var Mandrill $mailer */
    private $mailer;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->mailer = new Mandrill($this->apiKey);
    }

    /**
     * @param $to
     * @return string
     */
    public function sendWelcomeEmail($to)
    {
        $email = [
            'to'         => [['email' => $to]],
            'from_email' => $this->from,
            'subject'    => $this->subject,
            'text'       => $this->message
        ];
        /**
         * Since Mandrill requires your sending domain to be verified even when using a test API key,
         * we will always get a failure, so we're going to skip checking the result of the send for this demo.
         */
        $this->mailer->messages->send($email);
        return 'Message sent!';
    }
}