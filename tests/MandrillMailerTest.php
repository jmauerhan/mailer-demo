<?php
namespace Tests;

use Src\MandrillMailer;

require_once __DIR__ . '/../vendor/autoload.php';

Class MandrillMailerTest extends \PHPUnit_Framework_TestCase
{
    private $to      = 'foo@bar.com';
    private $from    = 'bar@foo.com';
    private $subject = 'Test Subject';
    private $message = 'Test Message';
    private $apiKey  = 'C0wG3h1A5Fs5xNoLdM2S0w';

    public function testSend()
    {
        $mandrill = $this->getMockBuilder('\Mandrill')
                         ->setConstructorArgs([$this->apiKey])
                         ->getMock();
        $mailer   = new MandrillMailer($mandrill);
        $result   = $mailer->send($this->to, $this->from, $this->subject, $this->message);
        $this->assertTrue($result);
    }

    /**
     * @group integrated
     */
    public function testSendIntegrated()
    {
        $mandrill = new \Mandrill($this->apiKey);
        $mailer   = new MandrillMailer($mandrill);
        $result   = $mailer->send($this->to, $this->from, $this->subject, $this->message);
        $this->assertTrue($result);
    }
}