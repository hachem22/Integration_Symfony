<?php
// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\SmsNotification;
use App\Service\TwilioService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SmsNotificationHandler implements MessageHandlerInterface
{
    private $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function __invoke(SmsNotification $smsNotification)
    {
        $this->twilioService->sendSms(
            $smsNotification->getPhoneNumber(),
            $smsNotification->getMessage()
        );
    }
}