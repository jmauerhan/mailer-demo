<?php
namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';
use Src\App;

Class AppTest extends \PHPUnit_Framework_TestCase
{
    private $to = 'foo@bar.com';

    /**
     * @group integrated
     */
    public function testSendWelcomeEmail()
    {
        $app    = new App();
        $result = $app->sendWelcomeEmail($this->to);
        $this->assertTrue($result);
    }
}