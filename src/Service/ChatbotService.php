<?php

namespace App\Service;

use App\Repository\SymptomeServiceRepository;

class ChatbotService
{
    private $repository;

    public function __construct(SymptomeServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    private function nettoyerTexte(string $texte): string
    {
        // Convertir en minuscules
        $texte = mb_strtolower($texte, 'UTF-8');

        // Supprimer les accents
        $texte = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);

        // Supprimer la ponctuation
        $texte = preg_replace('/[^\w\s]/u', '', $texte);

        return trim($texte);
    }

    public function analyserSymptomes(string $message): string
    {
        // Nettoyer le message pour normaliser la recherche
        $message = $this->nettoyerTexte($message);

        // Séparer les mots
        $mots = explode(" ", $message);

        // Liste des mots inutiles (stop words)
        $stopWords = ['jai', 'je', 'suis', 'de', 'au', 'et', 'le', 'la', 'les', 'un', 'une'];

        // Filtrer les mots inutiles
        $symptomesFiltres = array_diff($mots, $stopWords);

        // Vérifier la phrase entière d'abord
        $phrase = implode(" ", $symptomesFiltres);
        $results = $this->repository->findBySymptome($phrase);

        if (count($results) > 0) {
            return "Vous devriez consulter le service : " . $results[0]->getService();
        }

        // Vérifier les mots un par un si aucune correspondance trouvée
        foreach ($symptomesFiltres as $mot) {
            $results = $this->repository->findBySymptome($mot);
            if (count($results) > 0) {
                return "Vous devriez consulter le service : " . $results[0]->getService();
            }
        }

        return "Je ne trouve pas de service correspondant à vos symptômes. Veuillez consulter un médecin généraliste.";
    }
}