<?php

namespace App\Enum;

enum ReclamationType: string {
    case SERVICE = 'SERVICE';
    case SYSTEME = 'SYSTEME';

    public function getLabel(): string
    {
        return $this->value;
    }
}
