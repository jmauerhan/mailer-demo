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