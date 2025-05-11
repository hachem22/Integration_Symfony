<?php

namespace App\Enum;

enum ReclamationType: string {
    case SERVICE = 'Service';
    case SYSTEME = 'Système';

    public function getLabel(): string
    {
        return $this->value;
    }
}
