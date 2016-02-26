<?php
namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use Src\App;

Class AppTest extends \PHPUnit_Framework_TestCase
{
    private $to = 'foo@bar.com';

    public function testSetMailer()
    {
        $mailer = $this->getMock('Src\MailerInterface');
        $app    = new App();
        $return = $app->setMailer($mailer);
        $this->assertEquals($return, $app);
    }

    public function testSendWelcomeEmail()
    {
        $mailer = $this->getMock('Src\MailerInterface');
        $mailer->method('send')
               ->willReturn(true);

        $app = new App();
        $app->setMailer($mailer);
        $result = $app->sendWelcomeEmail($this->to);
        $this->assertTrue($result);
    }
}