<?php
namespace App\Service;

use Google\Cloud\Translate\V2\TranslateClient;

class TranslationService
{
    private $translate;

    public function __construct(string $apiKey)
    {
        $this->translate = new TranslateClient([
            'key' => $apiKey
        ]);
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        $result = $this->translate->translate($text, [
            'target' => $targetLanguage
        ]);

        return $result['text'];
    }

    public function detectLanguage(string $text): string
    {
        $result = $this->translate->detectLanguage($text);
        return $result['languageCode'];
    }
}