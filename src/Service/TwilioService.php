<?php
// src/Service/TwilioService.php
namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $twilioSid;
    private $twilioToken;
    private $twilioFromNumber;

    public function __construct(string $twilioSid, string $twilioToken, string $twilioFromNumber)
    {
        $this->twilioSid = $twilioSid;
        $this->twilioToken = $twilioToken;
        $this->twilioFromNumber = $twilioFromNumber;
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $client = new Client($this->twilioSid, $this->twilioToken);
            $client->messages->create(
                $to,
                [
                    'from' => $this->twilioFromNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            // Log l'erreur ou gérer l'exception selon votre système
            return false;
        }
    }
}