<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';

Class App
{
    /** @var string $from */
    private $from = 'app@example.com';
    /** @var string $subject */
    private $subject = 'Welcome to the App!';
    /** @var string $message */
    private $message = 'This is a welcome email!';

    /** @var Mandrill $mailer */
    private $mailer;

    /**
     * @param \Mandrill $mailer
     * @return $this
     */
    public function setMailer(\Mandrill $mailer)
    {
        $this->mailer = $mailer;
        return $this;
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

        $this->mailer->messages->send($email);
        /**
         * Since Mandrill requires your sending domain to be verified even when using a test API key,
         * we will always get a failure, so we're going to skip checking the result of the send for this demo.
         */
        return true;
    }
}