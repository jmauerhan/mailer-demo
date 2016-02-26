<?php
namespace Src;

interface MailerInterface
{
    /**
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $message
     * @return boolean
     */
    public function send($to, $from, $subject, $message);
}