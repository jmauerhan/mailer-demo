## Phase 2: Use Dependency Injection to allow an Isolated Test
###2.1: Move Mailer Dependency 
By moving the creation of the Mailer object outside of the App class, we can now test the App class in isolation, with a Test Double.

######index.php
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$apiKey = 'C0wG3h1A5Fs5xNoLdM2S0w';
$mailer = new Mandrill($apiKey);

$app = new Src\App();
$app->setMailer($mailer);
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';
```

######src/App.php
Use a setter to inject the Mailer. 
```php
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
```

######tests/AppTest.php
Add an isolated test, using a test double, to test the `sendWelcomeEmail()` method in isolation. We can also test the Mailer injection using a test double.
```php
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
```

######phpunit: Passing
![2](https://cloud.githubusercontent.com/assets/4204262/13345367/36153fee-dc2c-11e5-919f-b887e891cd44.PNG)

Nav:
* back: [Phase 1: Test-After Approach](phase-1.md)
* next: [Phase 3: TDD - Interface & Adapter](phase-3.md)
* [Home](/readme.md)
