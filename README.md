# mailer-demo
This is an example of a simple app to send email, written to demonstrate TDD guiding the engineer to using the Interface and Adapter pattern. The first iterations are done "test-after". When we switch to TDD, the design pattern is clearer. This makes the application much more maintainble when the third party service we built it around shuts down.

## Phase 1: Test-After Approach
### 1.1: Vendors
We do some research and select an email transaction service, Mandrill. We also choose PHPUnit for testing. We create a composer.json and run composer install.

######composer.json
```json
{
  "require": {
    "mandrill/mandrill": "1.0.*"
  },
  "require-dev": {
    "phpunit/phpunit": "5.0.*"
  },
  "autoload": {
    "psr-4": {
      "": ""
    }
  }
}
```

###1.2: Basic Script
We create a simple script to execute from the command line:
######index.php
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$app  = new Src\App();
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';
```

Next, following Mandrill's API Documentation, we create the App class to send the email:
######src/App.php
```php
<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';

use Mandrill;

Class App
{
    /** @var string $from */
    private $from = 'app@example.com';
    /** @var string $subject */
    private $subject = 'Welcome to the App!';
    /** @var string $message */
    private $message = 'This is a welcome email!';
    /** @var string $apiKey */
    private $apiKey = 'C0wG3h1A5Fs5xNoLdM2S0w';
    /** @var Mandrill $mailer */
    private $mailer;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->mailer = new Mandrill($this->apiKey);
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

###1.3: Add some tests
At this point, we can only do **integrated** tests - tests that actually depend on both our code working and Mandrill's service working correctly. This kind of test is slow, harder to debug, and brittle.

######tests/IndexTest.php
```php
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
```

######tests/AppTest.php
```php
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
```
###phpunit:
![1](https://cloud.githubusercontent.com/assets/4204262/13345364/2e3f61b4-dc2c-11e5-9316-6684bf734004.PNG)

## Phase 2: Use Dependency Injection to allow an Isolated Test
###2.1 Move Mailer Dependency 
By moving the creation of the Mailer object outside of the App class, we can now test the App class in isolation, with a Test Double. This is the "Tell, Don't Ask" Principle - Give the App the object it needs, don't let it look for or create it.

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

######phpunit
![2](https://cloud.githubusercontent.com/assets/4204262/13345367/36153fee-dc2c-11e5-919f-b887e891cd44.PNG)
