<?php

namespace App\Message;

class UserCreationEmailMessage
{
    private $email;
    private $name;
    private $plainPassword;

    public function __construct(string $email, string $name, string $plainPassword)
    {
        $this->email = $email;
        $this->name = $name;
        $this->plainPassword = $plainPassword;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }
}
