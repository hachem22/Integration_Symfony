<?php
// src/Message/SmsNotification.php
namespace App\Message;

class SmsNotification
{
    private $phoneNumber;
    private $message;

    public function __construct(string $phoneNumber, string $message)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}