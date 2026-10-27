<?php

namespace App\Config;

final class LocaleConfig
{
    public const SUPPORTED_LOCALES = ['fr', 'en', 'es', 'de', 'it'];

    // Les formulaires d'excursion (création et modification) n'exposent plus
    // que le français et l'anglais. L'italien reste une langue supportée du site
    // (SUPPORTED_LOCALES) et les traductions italiennes déjà enregistrées ne sont
    // jamais supprimées : en modification elles voyagent dans le formulaire via
    // un conteneur masqué pour ne pas être écrasées.
    public const EXCURSION_FORM_LOCALES = ['fr', 'en'];

    public const TRANSFERE_FORM_LOCALES = ['fr', 'en'];

    public const DESTINATION_FORM_LOCALES = ['fr', 'en'];

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

    public static function getExcursionFormLocales(): array
    {
        return self::EXCURSION_FORM_LOCALES;
    }

    public static function getDestinationFormLocales(): array
    {
        return self::DESTINATION_FORM_LOCALES;
    }

    private function __construct()
    {
    }
}