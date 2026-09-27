<?php

namespace App\Controller\Frontend\Home;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TransfereRepository;
use App\Repository\SaisonRepository;
use App\Service\TransferePriceCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    #[Route('/{_locale}', name: 'app_home_locale', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET'])]
    public function index(TransfereRepository $transfereRepository, SaisonRepository $saisonRepository, TransferePriceCalculator $priceCalculator): Response
    {
        $why_items = [
            ['icon' => 'directions_car', 'title' => 'home.why.item1_title', 'desc' => 'home.why.item1_desc'],
            ['icon' => 'schedule', 'title' => 'home.why.item2_title', 'desc' => 'home.why.item2_desc'],
            ['icon' => 'map', 'title' => 'home.why.item3_title', 'desc' => 'home.why.item3_desc'],
            ['icon' => 'credit_card', 'title' => 'home.why.item4_title', 'desc' => 'home.why.item4_desc']
        ];

        $vehicles = [
            ['name' => 'Peugeot 208', 'cat' => 'Économique', 'badge' => 'Disponible', 'badge_color' => 'bg-tertiary text-on-tertiary', 'year' => 'Modèle 2025', 'desc' => 'Citadine polyvalente, idéale pour Tunis, Sousse et les escapades côtières.', 'seats' => '5 Places', 'bags' => '2 Valises', 'trans' => 'Manuelle', 'trans_icon' => 'settings', 'clim' => 'Clim A/C', 'price' => 110, 'extra' => 'Assurance incluse', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCy9_hZdoseTB-aV2khKjyem3a1tXjOenqf-IgftDoZmtn4htrmE86vAlmqwCRGqa73asPAKUYS3fMmctosbZQGd8D88RAcIsgLDeGGKUZOhvJRYDiUxRFfCTS5fm8OeJr-WOgBWmfJ7sa_urb4BFbzD9K4r7b3b3BqsOPdHipYj0CnDX1Y0q22QahdguZqCNQIZlrCcTGWA9W8Bb2Mmq4ZLAsBYNhOm_-h_s8oQ64KsVu3Q_2x6sOSJg'],
            ['name' => 'Renault Clio 5', 'cat' => 'Citadine Moderne', 'badge' => 'Disponible', 'badge_color' => 'bg-tertiary text-on-tertiary', 'year' => 'Modèle 2025', 'desc' => "Confort routier optimal, faible consommation d'essence et écran tactile GPS.", 'seats' => '5 Places', 'bags' => '2 Valises', 'trans' => 'Manuelle', 'trans_icon' => 'settings', 'clim' => 'Clim A/C', 'price' => 115, 'extra' => 'Km illimité', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAZ11hWfrRmN2xmN-rX8EZ7jyUFWZXOpB_yjlUTcGegjhrnSBYeseaRcs29GBrZHXBX6LIWOHbdWeRct77emTUGsKPh--qucs04EPchXwb7XPh6qDjH3KN1ynGMZG1m1zfZB8eEfwSoGvrxfiJG5LcvyXTB98QQil3e4MoaXaLBEjuQitiD08yG1WXZRwvhwSM0jfhXkH_-NUA4VGuQuW-FycptekBdeKSqNMmYO-3M2DrCdoMfKvmztw'],
            ['name' => 'Volkswagen Golf 8', 'cat' => 'Compacte Premium', 'badge' => 'Automatique', 'badge_color' => 'bg-secondary text-on-secondary', 'year' => 'Modèle 2026', 'desc' => 'Boîte automatique DSG, insonorisation de premier ordre et aides à la conduite.', 'seats' => '5 Places', 'bags' => '3 Valises', 'trans' => 'Auto DSG', 'trans_icon' => 'smart_toy', 'clim' => 'Clim Auto', 'price' => 160, 'extra' => 'Toutes options', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDI79c9ezWIK22X4vlzXaW0O0qx4fXqMp6rBdOSSmLEInnEFHu06_BxjBDyNAIcVIuYmGg4yY-Lay7IXooLSHj7VEdv-V4-wao5Q5lNdO6mJMstlMtI0f8XMByKBpM_GMYAZ2jb5wjecTX_73rHeS1XsacX_SREy1yU60x5Myc8S2MasupLXgwr_KDrNUaMpywPw-nosJyscSft9LNIDbGAvNmK61rhxvbgdc4CwFmBjg_Mw33UiApDeg'],
            ['name' => 'Dacia Duster 4x4', 'cat' => 'SUV Aventure', 'badge' => 'Recommandé Sud', 'badge_color' => 'bg-tertiary text-on-tertiary', 'year' => 'Modèle 2025', 'desc' => 'Garde au sol surélevée, spacieux pour les familles et adapté aux pistes du Sud.', 'seats' => '5 Places', 'bags' => '4 Valises', 'trans' => 'Transmission 4x4', 'trans_icon' => 'terrain', 'clim' => 'Clim A/C', 'price' => 180, 'extra' => 'Prêt pour le désert', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPHmuXdi5jVXvR6FXbvgDofpieZeq55LHyrcCQ5rQWJNDdH5m-qeuFianvSAMSIo6yCLr4ztBLrngApF-93HvIaXMkR6IsBF3lodvKpoWXnHXhjCdP64TDcY2hQ8dGh61uvXO5QUTZN0xcTofTCMN30Q_Jb6smH3JvnOTGAiZR1X5Q8Oub97Qo3rvlwb0AkHAs8SuiizDBmXPnNGdSUKnInpNZhc_jq8D-x8arRrqBunb9pJIb30pW3A']
        ];

        $transferts = $transfereRepository->findBy(['actif' => true], ['id' => 'DESC'], 6);

        $excursions = [
            ['name' => 'Sidi Bou Saïd & Carthage', 'duration' => '1/2 Journée ou 1 Jour', 'desc' => 'Visitez les thermes d\'Antonin, les ruines puniques de Carthage et flânez dans les ruelles bleues et blanches de Sidi Bou Saïd.', 'info' => '4h à 7h', 'info_icon' => 'schedule', 'info2' => 'Guide privé inclus', 'info2_icon' => 'person', 'price' => 85, 'unesco' => false, 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDIFvgbEBp5xHkipa4K5xyE5DyZoDXY3t4aUjtEzazRI_rOMCCLuKN_kIxFA0jKjDPJY64X1Q5Hi-1aTWWzaxRKMQlwcm21b9uSuHx_TRyIGnAeysCMSgIZHauqIjtPH96ZDUnzM4LgoNzNkR9o5tlREJ4VRapxR2NstAx6ZUn_iZkSwzs9-iA0ILldDMxsZvzT1yYL9S3meS1Dy4LfHHyI-WbYw_CU5V2Pcmqe0hvxZzDS9ituASQkw'],
            ['name' => 'Sahara & Douz (Porte du Désert)', 'duration' => '2 Jours / 1 Nuit', 'desc' => 'Traversée du lac salé Chott el-Jérid, coucher de soleil à dos de dromadaire et nuit magique sous tente berbère au campement.', 'info' => 'Tente + Repas', 'info_icon' => 'bed', 'info2' => '4x4 Inclus', 'info2_icon' => 'terrain', 'price' => 340, 'unesco' => false, 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAa4FwMoPi1c-k3ICU5oktObMAsC-iUFw7q0bhazR8KF9EwDxrL-n67GSr1CxGFrmh5W1GuwlRh7f5NZo-XwABZ_8VBU1sUDoZndV_WOS8rglqKLhkSvFjGQ-8C3jSUkcynOjcl4vmPZfiOofSqXGdzOgNpq7LmZHRZ2FQzf9cuVSu7FQIaax5P_Rl9mslN5XXL-S4EMrDG8cPJHGB9d1DJEaK1_t2m0-SMVlcF2c0lBvoUlAYlPt1IKQ'],
            ['name' => 'El Jem & Monastir', 'duration' => '1 Journée Complète', 'desc' => 'Explorez le plus grand amphithéâtre romain du monde préservé à El Jem, puis visite du mausolée de Bourguiba et du Ribat de Monastir.', 'info' => '8 heures', 'info_icon' => 'schedule', 'info2' => 'Déjeuner inclus', 'info2_icon' => 'restaurant', 'price' => 145, 'unesco' => true, 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDY9dvJSYYdVjtyEvDpFPUAvdeHOkAr_ZonAmq0Wa5LxStK0fs2-oytt9Ke-h5j5Wpjy-Uc8-2FPSccTWtKey_dcfbxjORPBYFIpHXARzgpfHZ02ZW-Yxws0Iv89oBqbFyYnFHTwhycFkRrdg-QNWPQDjJnjy4BVca9Keoj7K7Dv1aoqTgmfTcWEFAIb0CXTwKlrIfhStkXi-NXPOnc0GsOrXL5C7fsm5Rdo5liN-ntDTZMYbScSNtBBg'],
            ['name' => 'Djerba & Tataouine (Ksour)', 'duration' => '1 Journée', 'desc' => "Découvrez les habitations troglodytiques de Matmata, les greniers fortifiés (Ksour) de Tataouine et l'île des potiers de Guellala.", 'info' => 'Lieux Star Wars', 'info_icon' => 'movie', 'info2' => '4x4 climatisé', 'info2_icon' => 'local_taxi', 'price' => 165, 'unesco' => false, 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBNe2PNmhhiyjlVz2uZ9ly1DN8HOjfdVCxr7VQddwlYhrPfDW5vOPi7WbQ8Zn0aZ09Sgmy9OF5afgBuXPzhBGFLAP0T_EGD4TQJV54zgmjLdrgytdTGCdAGe-eVBKNqxH1vtOrh5wSF9Ffp47kpkI6uLCsK8US6y2I9w0P0bxFUgnJGxMOTN_fwJf-b9TsoLWwmCOyzt-IDYCZGkx04laM75MYPiGkpDJLCMinCRo2KSdDKlXsVzvnxdw']
        ];

        $steps = [
            ['num' => 1, 'title' => 'home.how.step1_title', 'desc' => 'home.how.step1_desc', 'check' => 'Comparateur transparent'],
            ['num' => 2, 'title' => 'home.how.step2_title', 'desc' => 'home.how.step2_desc', 'check' => "Zéro paiement d'avance requis"],
            ['num' => 3, 'title' => 'home.how.step3_title', 'desc' => 'home.how.step3_desc', 'check' => 'Assistance téléphonique 24/7']
        ];

        $destinations = [
            ['name' => 'Tunis & Carthage', 'tag' => 'Capitale & Affaires', 'desc' => 'Aéroport TUN, Berges du Lac, Médina classée UNESCO et banlieue nord.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDluNNrjCQWJlWKhLvER1AEgEh1Qa3-BolMOkDSKzEnegenZmFSNXMRN9dO-251gPDuOfUrUi772A-ICqumcfM_1Qe8jbJE4-Sp_P1AexLhT_xz5ctMJqRH1zXutmwewpzPrWLZjhhpg7pb2DjbdQjlA2UyObXwQ8yzXdB_nYtK8pE27mC9flA0JJ9EXDOD4e5yFR1ugfdpofo0M7RlilO_dkzXtTT7peld1B3JG02D3lC4NCBV8YoI2w'],
            ['name' => 'Sousse & Port El Kantaoui', 'tag' => 'Perle du Sahel', 'desc' => 'Marina, plages de sable fin, golf de championnat et remparts historiques.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAthhtHkIdLkhPyF4OT1uKcnLU9NtpWd4pNXakzzwJ03UO2wU5Muk7InTqbtrELzokf1Avpk0qJT2GQ5QTSnwVpfHSNB98UlEHtY5lEC-WjT6GtY-b2hWjSNkMl7tEFD36w5Wlknp8qpm2eT9ckPq2vridCGAGzfu1lKK_9RzdRGOcOrGP0c3t-__JwFZZW8vONfSRh0t5RpmnCFJQqL9m0d2R-iUUkenQ55vXPUCdafPEgbjXvWsSNhA'],
            ['name' => 'Hammamet & Yasmine', 'tag' => 'Cap Bon & Balnéaire', 'desc' => 'Stations balnéaires, resorts de détente et artisanat potier de Nabeul.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuClae7JD7XJUm1LiGS2yoPkfITp159KmfXilTL5Ds7-a2H8LdfE6Eake5mn4dxCNVg76Qqxh_iv77USUZExuHR4QmvEkqgDVUjL63xzVOHmQ2FZFDG865VizG6ABboEfWbQhyBTJQH_DWzYYgVx9_sB1AKeCzXudey0oiAMnyw1gzjgcmW4bAicjrymZvxdOnwfSYrqP_POKS-jvHGHgg4FIQR3PVYJfAlFBq3FTWfQhNgezE1y2JcXig'],
            ['name' => 'Monastir & Skanes', 'tag' => 'Côte du Sahel', 'desc' => 'Aéroport MIR, Ribat médiéval et liaison directe vers Sousse et Mahdia.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBwuPsCIAPscIh9RXi8t3_ycqdpHA0P2125xKZHWLhGzRkPj4s4Y9CrfAkNqyb03SQWPAXBLpiFcyIu-NmwOByaNgXSlfiOHIIM60e0qi9bz6xM3OCL4UVgwGFto-W0JpOAXCeZi6LJY54SjpL3Eom84Js5psUv-v_oW_NbAoQuLkQC828teXMdYit4joX5SSevlF1TtFs_05O4YU1A0uTxJHq8TqKiye32RUCFhrI9lLdfGWRDitN_lQ'],
            ['name' => 'Djerba la Douce', 'tag' => 'Île Paradisiaque', 'desc' => 'Aéroport DJE, Houmt Souk, street art à Djerbahood et plages de sable blanc.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDRRmiiOHH-kdOfPl6yj3uemBeg5S5m5J7KFZSfaPdpIz5b7ae8OAhQswSRnP2n4mA2wBbvxQ7n7Y5m8fDktWmffBe9-44vTgkErmFRYYbFJXPgzWFDU_ZNFhOykprmeScBa-tHMn7Z0iTmTftGGEqD5ZgmACGmK6P5DEc5st_tM5doFDFCQf18SZ-XpO0PhPCOKvF0siOB5e4DeW0yl0CPX-Yk2k80cbelObHkM_cRIeNsiff_0iWQWA'],
            ['name' => 'Tozeur & Chott el-Jérid', 'tag' => 'Grand Sud & Oasis', 'desc' => 'Palmeraies géantes, canyons de Chebika, décors de cinéma et mirages salés.', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDYWiAI3fj15Cqr-Jn4qcrOsx1IZkhrn3pTLTbzHpwckNQnYfbyMoNn5Phbuu3o-ni1Q_Azy23df0c8qK1E7ms2G0AORI5cha7TElZ-MVGiQNSh_arkbYGkS_8H5SrVnJZtc3EKWgth_EYbUIc5vTGeuIjgmxH-mY_5p9VlEbp7Dnx3yOMilmltTp-sw7Jmy1_d9nCyzdTR-nTfx_D2-EsRF0CGCyxGFgJxDsuCiPFRWK53AzVMbtj0jA']
        ];

        $reviews = [
            ['initials' => 'TL', 'color' => 'bg-primary-fixed text-primary', 'tag' => 'Location Voiture', 'text' => '"Excellent service, voiture très propre et transfert aéroport ponctuel. Le chauffeur nous attendait à Tunis-Carthage avec une pancarte. Je recommande vivement !"', 'name' => 'Thomas L.', 'origin' => 'France 🇫🇷 • Voyage familial'],
            ['initials' => 'SM', 'color' => 'bg-secondary-fixed text-secondary', 'tag' => 'Navette VIP', 'text' => '"Smooth car rental experience. The Peugeot 208 was spotless and the handover at the airport took less than 5 minutes. Best mobility service in Tunisia."', 'name' => 'Sarah M.', 'origin' => 'Royaume-Uni 🇬🇧 • Séjour Hammamet'],
            ['initials' => 'KB', 'color' => 'bg-tertiary-fixed text-tertiary', 'tag' => 'Transfert Inter-Villes', 'text' => '"Service irréprochable pour notre séjour à Sousse et Hammamet. Véhicule climatisé très confortable et équipe très arrangeante par WhatsApp. Chapeau bas !"', 'name' => 'Karim B.', 'origin' => "Algérie 🇩🇿 • Vacances d'été"]
        ];

        return $this->render('Frontend/Home/index.html.twig', [
            'why_items' => $why_items,
            'vehicles' => $vehicles,
            'transferts' => $transferts,
            'activeSeason' => $saisonRepository->findActive(),
            'transfertPrices' => array_reduce($transferts, function (array $prices, $transfere) use ($priceCalculator): array {
                $prices[$transfere->getId()] = $priceCalculator->getPrixPourDate($transfere);
                return $prices;
            }, []),
            'excursions' => $excursions,
            'steps' => $steps,
            'destinations' => $destinations,
            'reviews' => $reviews,
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView()
        ]);
    }
}
