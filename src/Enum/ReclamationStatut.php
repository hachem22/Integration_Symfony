<?php
namespace App\Enum;

enum ReclamationStatut: string {
    case EN_ATTENTE = 'en attente';
    case EN_COURS = 'en cours';
    case RESOLUE = 'résolue';
    case REJETE = 'rejeté';
    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'en attente' => self::EN_ATTENTE,
            'en cours' => self::EN_COURS,
            'résolue' => self::RESOLUE,
            'rejeté' => self::REJETE,
            default => throw new \InvalidArgumentException("Statut invalide : $value"),
        };
    }
    
}
