<?php

namespace App\Enum;

enum TypeEntretient: string
{
    case  NETTOYAGE ='NETTOYAGE';
    case  DESINFECTION='DESINFECTION';
    case  MAINTENANCE='MAINTENANCE';
    case  REPARATION='REPARATION';
    case  INSPECTION='INSPECTION';
    case  RENOVATION='RENOVATION';
    case  AUTRE='AUTRE';
    
}