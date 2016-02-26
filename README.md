# mailer-demo
This is an example of a simple app to send email, written to demonstrate TDD guiding the engineer to using the Interface and Adapter pattern. The first iterations are done "test-after". When we switch to TDD, the design pattern is clearer. This makes the application much more maintainble when the third party service we built it around shuts down.

## Phase 1: Test-After Approach
### 1.1: Vendors
We do some research and select an email transaction service, Mandrill. We also choose PHPUnit for testing. We create a composer.json and run `composer install`.

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
This test of the entire application running is a functional test, not a unit test. It is slow, hard to debug, and brittle - but it's the best we can do right now.
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
######phpunit: Passing
![1](https://cloud.githubusercontent.com/assets/4204262/13345364/2e3f61b4-dc2c-11e5-9316-6684bf734004.PNG)

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

## Phase 3: TDD - Interface & Adapter
By writing the tests first and following the Outside-In approach, we will identify our collaborators during the test writing. We will create an interface for the collaborator, mock that interface, and finish the original class (consumer). This allows us to have control over the design of the collaborator's API, ensuring it fits the context in which we want to use it. Then we can continue on to creating an implementation of that interface. Because this implementation will be used to adapt another library's functionality to work within our collaborator's api, it is an Adapter. 

![2](https://github.com/jmauerhan/mailer-demo/blob/master/outside-in.inner-loop.gif)

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

##Phase 4: Handling Change by Adapters
The interface and adapter pattern that we used when practicing Outside-In TDD is much more flexible when we need to change. Imagine our application is not the one index file, but rather a full application that sends many different emails from different triggers. Welcome email, activation email, forgot password, reminders, notifications, etc. 

Let's also imagine we wrote this large application just using Mandrill's library (or a wrapper for it based on Mandrill, instead of based on our application). What happens if suddenly Mandrill decided to make significant changes, like giving us a very short timeline to either migrate to a new provider or start paying high fees? The changes we'd have to make in the codebase could be enormous, depending on how tightly coupled we were with Mandrill. 

If we instead write our large application using the interface and adapter pattern, to switch to a new provider, we only have to do the following:
* New Adapter Test
* New Adapter Implementation
* Implement new Adapter in Application (config)

###4.1: New Vendor
`composer require "mailin-api/mailin-api-php": "^1.0"`

###4.2: New Adapter Test
The new adapter will have the new provider's library as a dependency, just like the Mandrill adapter used the Mandrill library. This library requires an api url, so it's an additional property that our old adapter didn't have. Fortunately, we only have to worry about that in this one file.

######tests/SendInBlueMailerTest.php
```php
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
```

###4.3: Adapter Implementation

######src/SendInBlueMailer.php
```php
<?php
namespace Src;

require_once __DIR__ . '/../vendor/autoload.php';
use Sendinblue\Mailin as SendInBlue;

class SendInBlueMailer implements MailerInterface
{
    /** @var SendInBlue $sendInBlue */
    private $sendInBlue;

    public function __construct(SendInBlue $sendInBlue)
    {
        $this->sendInBlue = $sendInBlue;
    }

    public function send($to, $from, $subject, $message)
    {
        $email  = [
            'to'         => [$to => $to],
            'from_email' => [$from => $from],
            'subject'    => $subject,
            'text'       => $message
        ];
        $result = $this->sendInBlue->send_email($email);
        /** again for the purposes of the demo, we're going to fake a success. */
        return true;
        /** this code will work once the account is activated */
        //return $result['code'] === 'success';
    }
}
```

At this point, the isolated tests will all be passing, and the integrated test for the adapter. All we have to do is update the actual application to **use** the new adapter, and our functional integrated test will pass too.

###4.4: Use Adapter
######index.php
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$to = 'jane.doe@gmail.com';

$apiKey     = 'XgJLUbVFS1YkZyEr';
$apiUrl     = "https://api.sendinblue.com/v2.0";
$sendInBlue = new \Sendinblue\Mailin($apiUrl, $apiKey);
$mailer     = new \Src\SendInBlueMailer($sendInBlue);

$app = new Src\App();
$app->setMailer($mailer);
$sent = $app->sendWelcomeEmail($to);

echo 'Welcome Email ' . ($sent ? 'Sent' : 'Failed') . '!';
```

######phpunit: Passing
![6](https://cloud.githubusercontent.com/assets/4204262/13345849/384dc872-dc30-11e5-8c3f-bb81f593bec0.PNG)
