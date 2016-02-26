<?php
namespace Tests;

use Src\SendInBlueMailer;
use Sendinblue\Mailin as SendInBlue;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class SendInBlueMailerTest
 * @package Tests
 */
Class SendInBlueMailerTest extends \PHPUnit_Framework_TestCase
{
    private $to      = 'foo@bar.com';
    private $from    = 'bar@foo.com';
    private $subject = 'Test Subject';
    private $message = 'Test Message';
    private $apiKey  = 'XgJLUbVFS1YkZyEr';
    private $apiUrl  = "https://api.sendinblue.com/v2.0";

    public function testSend()
    {
        $sendInBlue = $this->getMockBuilder('Sendinblue\Mailin')
                           ->setConstructorArgs([$this->apiUrl, $this->apiKey])
                           ->getMock();
        $mailer     = new SendInBlueMailer($sendInBlue);
        $result     = $mailer->send($this->to, $this->from, $this->subject, $this->message);
        $this->assertTrue($result);
    }

    /**
     * @group integrated
     */
    public function testSendIntegrated()
    {
        $sendInBlue = new SendInBlue($this->apiUrl, $this->apiKey);
        $mailer     = new SendInBlueMailer($sendInBlue);
        $result     = $mailer->send($this->to, $this->from, $this->subject, $this->message);
        $this->assertTrue($result);
    }
}