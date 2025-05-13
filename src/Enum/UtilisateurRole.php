<?php

namespace App\Enum;

enum UtilisateurRole: string
{
    case Patient = 'PATIENT';
    case Medecin = 'MEDECIN';
    case Infirmier = 'INFIRMIER';
    case Pharmacien = 'PHARMACIEN';
    case Responsable = 'RESPONSABLE';
    case Administrateur = 'ADMIN';
    case FemmeDeMenage = 'FemmeDeMenage';

}