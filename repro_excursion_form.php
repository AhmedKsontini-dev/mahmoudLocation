<?php

use App\Entity\Excursion;
use App\Form\ExcursionType;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

// Force the dev database even in test env (test DB may be empty)
$dbUrl = 'mysql://root:@127.0.0.1:3306/mahmoudlocation?serverVersion=16&charset=utf8';
$_SERVER['DATABASE_URL'] = $dbUrl;
$_ENV['DATABASE_URL'] = $dbUrl;
putenv('DATABASE_URL='.$dbUrl);

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new App\Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer()->get('test.service_container');

try {
    $formFactory = $container->get('form.factory');
    $excursion = new Excursion();
    foreach (\App\Config\LocaleConfig::getSupportedLocales() as $locale) {
        $trans = new \App\Entity\ExcursionTranslation();
        $trans->setLocale($locale);
        $excursion->addTranslation($trans);
    }
    $form = $formFactory->create(ExcursionType::class, $excursion, ['is_new' => true]);
    echo "FORM BUILD OK\n";

    $view = $form->createView();
    echo 'VIEW CHILDREN: '.implode(', ', array_keys($view->children))."\n";

    // Provide a fake HTTP request/session so app.request / app.flashes work in CLI
    $request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/admin/excursions/new');
    $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));
    $container->get('request_stack')->push($request);

    // Render only the body block (same FormView that AbstractController::render() passes to Twig)
    $template = $container->get('twig')->load('Backend/Excursion/form.html.twig');
    $html = $template->renderBlock('body', [
        'form' => $view,
        'excursion' => $excursion,
        'title' => 'Nouvelle excursion',
    ]);
    echo "BODY BLOCK RENDER OK, length=".strlen($html)."\n";

    // Verify data mapping of the "featured" checkbox (getter isFeatured() / setter setFeatured())
    $excursion->setFeatured(true);
    $readForm = $formFactory->create(ExcursionType::class, $excursion, ['is_new' => true]);
    echo 'read: getData(featured) after setFeatured(true): '.var_export($readForm->get('featured')->getData(), true)."\n";

    $excursion->setFeatured(false);
    $submitForm = $formFactory->create(ExcursionType::class, $excursion, ['is_new' => true]);
    $submitForm->submit(['featured' => '1'], false);
    echo 'write (checked): entity isFeatured() = '.var_export($excursion->isFeatured(), true)."\n";

    $excursion->setFeatured(true);
    $submitForm2 = $formFactory->create(ExcursionType::class, $excursion, ['is_new' => true]);
    $submitForm2->submit(['featured' => null], false);
    echo 'write (unchecked): entity isFeatured() = '.var_export($excursion->isFeatured(), true)."\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: ".get_class($e)."\n";
    echo "MESSAGE: ".$e->getMessage()."\n";
    echo "FILE: ".$e->getFile()." LINE: ".$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}
