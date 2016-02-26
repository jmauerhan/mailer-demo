# mailer-demo
This is an example of a simple app to send email, written to demonstrate TDD guiding the engineer to using the Interface and Adapter pattern. The first iterations are done "test-after". When we switch to TDD, the design pattern is clearer. This makes the application much more maintainble when the third party service we built it around shuts down.

## Step 1: Vendors
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

##Step 2: Basic Script
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
