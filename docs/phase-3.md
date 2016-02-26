## Phase 3: TDD - Interface & Adapter
By writing the tests first and following the Outside-In approach, we will identify our collaborators during the test writing. We will create an interface for the collaborator, mock that interface, and finish the original class (consumer). This allows us to have control over the design of the collaborator's API, ensuring it fits the context in which we want to use it. Then we can continue on to creating an implementation of that interface. Because this implementation will be used to adapt another library's functionality to work within our collaborator's api, it is an Adapter. 

######Outside-In Workflow:
![2](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/outside-in.inner-loop.gif)

###3.1: Test First:
We'll start over, with the AppTest first. We can write two isolated tests, and no longer need an integrated test here. 

######tests/AppTest.php
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
```

###3.2 Interface
######src/MailerInterface.php
```php
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
```

###3.3 Write App class to pass Test 
######src/App.php
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
```

######phpunit: Our new isolated tests will all be passing at this point
![3](https://cloud.githubusercontent.com/assets/4204262/13345585/ee5f1d62-dc2d-11e5-9f51-152dfa4e6e55.PNG)

###3.4 Implementation (Adapter)
Now we can create an implementation of the MailerInterface, that will adapt the Mandrill library to work with the api we want for our app. The Mandrill library comes with a lot of functionlity we don't need for our app, and the methods aren't named the way we want, or using the arguments we want. So we can use the adapter to make Mandrill work for our Interface that we designed.

Again, we'll start with the tests first. We can do one isolated test for our Adapter's method, and one integrated test to ensure our Adapter is communicating with Mandrill properly. (Since we don't control the Mandrill library and cannot write contract tests for it.)

######tests/MandrillMailer.php
```php
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
```

######src/MandrillMailer.php
Now we can create the actual class, and get our unit tests passing. 
```php
<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';

class MandrillMailer implements MailerInterface
{
    /** @var \Mandrill $mandrill */
    private $mandrill;

    public function __construct(\Mandrill $mandrill)
    {
        $this->mandrill = $mandrill;
    }

    public function send($to, $from, $subject, $message)
    {
        $email = [
            'to'         => [['email' => $to]],
            'from_email' => $from,
            'subject'    => $subject,
            'text'       => $message
        ];
        $this->mandrill->messages->send($email);
        /**
         * Since Mandrill requires your sending domain to be verified even when using a test API key,
         * we will always get a failure, so we're going to skip checking the result of the send call for this demo.
         */
        return true;
    }
}
```

We still need the index.php and IndexTest. The functional IndexTest we wrote before can be used, and we'll write an index.php that creates Mandrill, MandrillMailer, and App, and injects the dependencies into their consumers. 

######index.php
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$apiKey   = 'C0wG3h1A5Fs5xNoLdM2S0w';
$mandrill = new Mandrill($apiKey);
$mailer   = new \Src\MandrillMailer($mandrill);

$app = new Src\App();
$app->setMailer($mailer);
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';
```
######phpunit: Isolated and Integrated Tests Passing:
![4](https://cloud.githubusercontent.com/assets/4204262/13345733/2ba1d34e-dc2f-11e5-81a1-699cd19d9beb.PNG)
