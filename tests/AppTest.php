<?php
namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use Src\App;

Class AppTest extends \PHPUnit_Framework_TestCase
{
    private $to = 'foo@bar.com';

    public function testSetMailer()
    {
        $mailer = $this->getMockBuilder('Mandrill')
                       ->setConstructorArgs(['api-key'])
                       ->getMock();
        $app    = new App();
        $return = $app->setMailer($mailer);
        $this->assertEquals($return, $app);
    }

    public function testSendWelcomeEmail()
    {
        $mailer = $this->getMockBuilder('Mandrill')
                       ->setConstructorArgs(['api-key'])
                       ->getMock();
        $app    = new App();
        $app->setMailer($mailer);
        $result = $app->sendWelcomeEmail($this->to);
        $this->assertTrue($result);
    }

    /**
     * @group integrated
     */
    public function testSendWelcomeEmailIntegrated()
    {
        $apiKey = 'C0wG3h1A5Fs5xNoLdM2S0w';
        $mailer = new \Mandrill($apiKey);
        $app    = new App();
        $app->setMailer($mailer);
        $result = $app->sendWelcomeEmail($this->to);
        $this->assertTrue($result);
    }
}