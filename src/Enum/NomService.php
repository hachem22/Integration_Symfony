<?php

namespace App\Enum;

enum NomService: string
{
    case Cardiologue = 'Cardiologie';
    case Dermatologue = 'Dermatologie';
    case Généraliste = 'Généraliste';
    case Neurologie = 'Neurologie';
    case Ophtalmologue = 'Ophtalmologie';
    case ORL = 'Oto-Rhino-Laryngologie';
    case Pédiatre = 'Pédiatrie';
    case Psychiatre = 'Psychiatrie';
    case Radiologue = 'Radiologie';
    case Rhumatologue = 'Rhumatologie';
    
    public function getNom(): string
    {
        return $this->value;
    } 
    
}
