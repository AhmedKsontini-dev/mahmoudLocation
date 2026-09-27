<?php

use App\Form\ExcursionType;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

try {
    $formFactory = $container->get('form.factory');
    $form = $formFactory->create(ExcursionType::class);
    $view = $form->createView();
    echo "FORM BUILD OK\n";

    $twig = $container->get('twig');
    $html = $twig->render('Backend/Excursion/form.html.twig', ['form' => $view]);
    echo "TEMPLATE RENDER OK, length=".strlen($html)."\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: ".get_class($e)."\n";
    echo "MESSAGE: ".$e->getMessage()."\n";
    echo "FILE: ".$e->getFile()." LINE: ".$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}
