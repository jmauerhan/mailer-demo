##Phase 4: Handling Change by Adapters
The interface and adapter pattern that we used when practicing Outside-In TDD is much more flexible when we need to change. Imagine our application is not the one index file, but rather a full application that sends many different emails from different triggers. Welcome email, activation email, forgot password, reminders, notifications, etc. 

Let's also imagine we wrote this large application just using Mandrill's library (or a wrapper for it based on Mandrill, instead of based on our application). What happens if suddenly Mandrill decided to make significant changes, like giving us a very short timeline to either migrate to a new provider or start paying high fees? The changes we'd have to make in the codebase could be enormous, depending on how tightly coupled we were with Mandrill. 

![1](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/announcement.PNG)
![2](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/reactions.PNG)
![3](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/reactions-2.PNG)
![4](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/reactions-3.PNG)

If we instead write our large application using the interface and adapter pattern, to switch to a new provider, we only have to do the following:
* New Adapter Test
* New Adapter Implementation
* Implement new Adapter in Application (config)

![5](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/abstraction.PNG)
![6](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/wellfactored.PNG)
![7](https://github.com/jmauerhan/mailer-demo/blob/master/docs/img/code.PNG)

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

Nav:
* [Phase 3: TDD - Interface & Adapter](docs/phase-3.md)
* [Home](readme.md)
