<?php

namespace App\Form\EventListener;

use App\Config\LocaleConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Aligne les entrées soumises d'une collection de traductions sur l'ordre réel
 * de la collection de l'entité, en se basant sur le champ caché « locale ».
 *
 * Doctrine ne garantit pas l'ordre de chargement d'une OneToMany sans ORDER BY :
 * les lignes (parent, locale) peuvent arriver dans un ordre différent d'une
 * requête à l'autre. Comme CollectionType apparie les entrées du POST par index
 * numérique, un payload [fr, en, it] posté sur une collection chargée [en, fr]
 * ferait passer la locale « fr » à l'entité « en » et réciproquement — deux
 * traductions existantes qui échangent leurs locales provoquent immanquablement
 * une erreur 1062 sur la contrainte UNIQUE (parent_id, locale).
 *
 * Ce listener réordonne le tableau soumis pour que l'entrée de chaque locale
 * atterrisse sur l'entité portant déjà cette locale ; les entrées de locales
 * absentes de la collection (nouvelles traductions ajoutées via le prototype)
 * sont conservées en fin de liste.
 *
 * Les onglets ouverts avant l'apparition du champ caché « locale » postent des
 * entrées sans locale : sans identification, ces écritures null déclenchent
 * « Expected argument of type "string", "null" given at property path "locale" ».
 * Le listener réattribue alors les locales par position, dans l'ordre réel de
 * la collection (page d'édition) ou l'ordre des locales du formulaire
 * fr, en, it (nouvelle entrée prototypée) — le DOM legacy rendait les panneaux
 * dans ce même ordre.
 */
final class AlignSubmittedTranslationsByLocaleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Priorité haute : l'alignement doit précéder tout autre traitement de
        // la soumission (contraintes dépendant de la locale dans les children).
        return [FormEvents::PRE_SUBMIT => ['onPreSubmit', 1024]];
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        if (!\is_array($data) || [] === $data) {
            return;
        }

        // L'entité propriétaire peut être null à ce stade (nouvelle entrée
        // en cours de soumission : l'objet n'est instancié qu'en fin de submit
        // via empty_data). Le rattrapage utilise alors l'ordre des locales du
        // formulaire, et l'alignement devient une simple normalisation dense.
        $owner = $event->getForm()->getParent()?->getData();
        $existing = [];
        if (\is_object($owner) && method_exists($owner, 'getTranslations')) {
            $collection = $owner->getTranslations();
            if (null !== $collection) {
                $existing = $collection;
            }
        }

        // Uniquement des entrées nommées : sinon on ne touche à rien.
        $entries = [];
        foreach ($data as $key => $entry) {
            if (!\is_array($entry)) {
                return;
            }
            $entries[$key] = $entry;
        }

        // Rattache les locales manquantes (payloads d'onglets périmés) avant
        // l'alignement, pour que chaque entrée porte une locale valide.
        $entries = $this->attributeMissingLocales($entries, $existing);

        $available = [];
        foreach ($entries as $key => $entry) {
            // Les entrées sans locale (irréattribuables : doublon ou payload
            // corrompu) sont écartées de la soumission — une création sans
            // identité violerait la contrainte UNIQUE (parent_id, locale).
            if (!isset($entry['locale']) || !\is_string($entry['locale']) || '' === $entry['locale']) {
                continue;
            }
            $available[$entry['locale']][] = $key;
        }

        $ordered = [];
        $assignedLocales = [];
        foreach ($existing as $translation) {
            $locale = $translation->getLocale();
            if (isset($available[$locale]) && [] !== $available[$locale]) {
                $key = array_shift($available[$locale]);
                $ordered[] = $entries[$key];
                $assignedLocales[$locale] = true;
                continue;
            }
            // Aucune entrée soumise pour cette locale : la liste dense s'arrête
            // ici, les children excédentaires seront retirés par allow_delete
            // (comportement standard d'une collection soumise plus courte).
            break;
        }

        // Locales non encore présentes dans la collection (nouvelles traductions
        // ajoutées via le prototype) : à la suite, dans l'ordre soumis. Une locale
        // déjà portée par une entité existante ne peut pas être ajoutée deux
        // fois : l'entrée supplémentaire (doublon malveillant ou corrompu) est écartée.
        foreach ($available as $locale => $keys) {
            if (isset($assignedLocales[$locale])) {
                continue;
            }
            foreach ($keys as $key) {
                $ordered[] = $entries[$key];
            }
        }

        $event->setData(array_values($ordered));
    }

    /**
     * Attribue une locale aux entrées qui n'en portent pas (onglet ouvert avant
     * l'ajout du champ caché). Les locales déjà présentes sont préservées ; une
     * locale déjà utilisée n'est jamais réattribuée (pas de doublon possible).
     *
     * @param array<int|string, array<string, mixed>> $entries
     * @param iterable<object> $existing Traductions existantes de l'entité propriétaire
     * @return array<int|string, array<string, mixed>>
     */
    private function attributeMissingLocales(array $entries, iterable $existing): array
    {
        $claimed = [];
        $missingKeys = [];
        foreach ($entries as $key => $entry) {
            $locale = $entry['locale'] ?? null;
            if (\is_string($locale) && '' !== $locale) {
                $claimed[$locale] = true;
            } else {
                $missingKeys[] = $key;
            }
        }

        if ([] === $missingKeys) {
            return $entries;
        }

        // Pool de rattrapage : ordre réel de la collection chargée (celui qui
        // a servi à rendre l'ancien formulaire), sinon ordre des locales du
        // formulaire d'excursion (celui du prototype legacy).
        $pool = [];
        foreach ($existing as $translation) {
            if (method_exists($translation, 'getLocale')) {
                $pool[] = $translation->getLocale();
            }
        }
        if ([] === $pool) {
            $pool = LocaleConfig::getExcursionFormLocales();
        }

        foreach ($pool as $locale) {
            if ([] === $missingKeys) {
                break;
            }
            if (isset($claimed[$locale])) {
                continue;
            }
            $key = array_shift($missingKeys);
            $entries[$key]['locale'] = $locale;
            $claimed[$locale] = true;
        }

        return $entries;
    }
}
