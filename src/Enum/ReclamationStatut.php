<?php
namespace App\Enum;

enum ReclamationStatut: string {
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case RESOLUE = 'résolue';
    case REJETE = 'Rejeté';
    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'en_attente' => self::EN_ATTENTE,
            'en_cours' => self::EN_COURS,
            'résolue' => self::RESOLUE,
            'Rejeté' => self::REJETE,
            default => throw new \InvalidArgumentException("Statut invalide : $value"),
        };
    }
    
}
