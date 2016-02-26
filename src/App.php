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

    /** @var MailerInterface $mailer */
    private $mailer;

    /**
     * @param MailerInterface $mailer
     * @return $this
     */
    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @param $to
     * @return boolean
     */
    public function sendWelcomeEmail($to)
    {
        return $this->mailer->send($to, $this->from, $this->subject, $this->message);
    }
}