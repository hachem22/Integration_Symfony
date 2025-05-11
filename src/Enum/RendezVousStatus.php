<?php

namespace App\Enum;

enum RendezVousStatus: string
{
    case EnAttente = 'EnAttente';
    case Accepter = 'Accepter';
    case refuser = 'refuser';
    
}