<?php

namespace App\Service;

class SwearWordFilter
{
    private $swearWords;

    public function __construct()
    {
        $this->swearWords = [
            // English
            'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'dick', 'piss', 'cunt', 'whore', 'slut',
            'damn', 'hell', 'crap', 'douche', 'wanker', 'prick', 'twat', 'motherfucker', 'bullshit',
            'arse', 'arsehole', 'bollocks', 'bugger', 'bloody', 'cock', 'dickhead', 'pissed', 'piss off',
            'shitty', 'son of a bitch', 'wank', 'fucker', 'fucking', 'fuck off', 'shithead', 'shitface',
        
            // French
            'merde', 'putain', 'connard', 'salope', 'enculé', 'nique', 'bite', 'couille', 'chatte', 'pute',
            'con', 'connasse', 'enculer', 'niquer', 'ta gueule', 'fils de pute', 'bouffon', 'trou du cul',
            'salaud', 'batard', 'enfoiré', 'pétasse', 'branleur', 'cul', 'foutre', 'gueule', 'nique ta mère',
            'pédé', 'pd', 'tapette', 'encule', 'enfoiré', 'foutre', 'niquer', 'niquez', 'niquée',
        
            // Spanish
            'mierda', 'puta', 'cabrón', 'joder', 'coño', 'pendejo', 'gilipollas', 'hijo de puta', 'maricón',
            'zorra', 'chingar', 'polla', 'culo', 'verga', 'estúpido', 'idiota', 'imbécil', 'malparido',
            'puto', 'puta madre', 'chinga tu madre', 'pendejada', 'carajo', 'cojones', 'hostia', 'jodido',
            'maldito', 'mamón', 'marica', 'pendeja', 'pinche', 'putada', 'puto', 'tonto', 'verguenza',
        
            // German
            'scheiße', 'arschloch', 'fick', 'hure', 'wichser', 'mist', 'verdammt', 'schlampe', 'fotze',
            'schwanz', 'arsch', 'kacke', 'blödmann', 'depp', 'dummkopf', 'ficker', 'hurensohn', 'mistkerl',
            'pimmel', 'sau', 'schwachkopf', 'spast', 'trottel', 'vollidiot', 'wichser', 'fotzen', 'ficken',
            'scheiß', 'scheisse', 'arschgeige', 'fotzenkind', 'hurenkind', 'missgeburt', 'schwuchtel', 'spasti',
        
        ];
    }

    public function containsSwearWords(string $text): bool
    {
        foreach ($this->swearWords as $word) {
            if (stripos($text, $word) !== false) {
                return true; // Swear word found
            }
        }

        return false; 
    }
}