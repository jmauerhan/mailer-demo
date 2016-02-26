<?php
namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';

Class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group integrated
     */
    public function testIndex()
    {
        ob_start();
        include(__DIR__ . '/../index.php');
        $output = ob_get_clean();
        $this->assertEquals('Welcome Email Sent!', $output);
    }
}