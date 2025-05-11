<?php

// src/Message/Nour.php

namespace App\Message;

class Nour
{
    private string $email;
    private string $name;
    private string $plainPassword;

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
