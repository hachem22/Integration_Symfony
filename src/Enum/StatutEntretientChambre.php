<?php

namespace App\Enum;

enum StatutEntretientChambre: string
{
    case en_cours = 'EN_COURS';
    case en_attente = 'EN_ATTENTE';
    case planifiée='PLANIFIE';
    case termine='TERMINE';
   
}

