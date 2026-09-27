<?php

namespace App\Config;

final class LocaleConfig
{
    public const SUPPORTED_LOCALES = ['fr', 'en', 'es', 'de', 'it'];

    public const LABELS = [
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español',
        'de' => 'Deutsch',
        'it' => 'Italiano',
    ];

    public static function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    private function __construct()
    {
    }
}
