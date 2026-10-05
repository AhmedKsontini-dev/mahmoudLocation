<?php

namespace App\Tests;

use App\Entity\Excursion;
use App\Entity\ExcursionImage;
use App\Controller\Admin\ExcursionController;
use App\Form\ExcursionImageType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ExcursionImageUploadTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = self::getContainer()->get('form.factory');
    }

    public function testSupportedImageFormatsAreValid(): void
    {
        $formats = ['jpeg', 'png'];
        if (function_exists('imagewebp')) {
            $formats[] = 'webp';
        }

        foreach ($formats as $format) {
            $path = $this->createImage($format);
            try {
                $filenames = $format === 'jpeg' ? ['fixture.jpg', 'fixture.jpeg'] : ['fixture.'.$format];
                foreach ($filenames as $filename) {
                    $form = $this->formFactory->create(ExcursionImageType::class, new ExcursionImage());
                    $form->submit([
                        'imageFile' => new UploadedFile($path, $filename, null, null, true),
                    ], false);

                    self::assertTrue($form->get('imageFile')->isValid(), $filename.' should be accepted');
                }
            } finally {
                @unlink($path);
            }
        }
    }

    public function testInvalidFormatShowsSymfonyMessage(): void
    {
        $invalidPath = tempnam(sys_get_temp_dir(), 'excursion-invalid-');
        file_put_contents($invalidPath, 'not an image');

        try {
            $invalidErrors = $this->submitImage($invalidPath, 'fixture.txt');
            self::assertSame(['Veuillez uploader une image valide (JPEG, PNG, WebP).'], $invalidErrors);
        } finally {
            @unlink($invalidPath);
        }
    }

    public function testLargeFileSizeAndImageDimensionsAreAccepted(): void
    {
        $largePath = $this->createImage('jpeg');
        file_put_contents($largePath, str_repeat('x', 12 * 1024 * 1024 + 1), FILE_APPEND);

        try {
            self::assertGreaterThan(10 * 1024 * 1024, filesize($largePath));
            self::assertSame([], $this->submitImage($largePath, 'large-image.jpg'));
        } finally {
            @unlink($largePath);
        }

        foreach ([[1200, 600], [600, 1200], [512, 512], [12000, 16], [16, 12000]] as [$width, $height]) {
            $path = $this->createImage('png', $width, $height);
            try {
                self::assertSame([], $this->submitImage($path, 'dimensions.png'), sprintf('%dx%d should be accepted', $width, $height));
            } finally {
                @unlink($path);
            }
        }
    }

    public function testUploadedFileStaysAttachedToItsImageCollectionEntry(): void
    {
        $path = $this->createImage('jpeg');
        $upload = new UploadedFile($path, 'fixture.jpg', null, null, true);
        $excursion = new Excursion();
        $builder = $this->formFactory->createBuilder(FormType::class, $excursion);
        $builder->add('images', CollectionType::class, [
            'entry_type' => ExcursionImageType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
        ]);
        $form = $builder->getForm();
        $form->submit([
            'images' => [
                2 => ['imageFile' => $upload],
            ],
        ], false);

        $storedPath = null;
        try {
            $imageForm = $form->get('images')->get(2);
            self::assertInstanceOf(ExcursionImage::class, $imageForm->getData());
            self::assertSame($imageForm->getData(), $excursion->getImages()->first());
            self::assertSame($upload, $imageForm->get('imageFile')->getData());
            self::assertTrue($imageForm->get('imageFile')->isValid());

            $controller = self::getContainer()->get(ExcursionController::class);
            $uploadHandler = new \ReflectionMethod(ExcursionController::class, 'handleImageUploads');
            $uploadHandler->invoke($controller, $form, $excursion);

            $storedPath = self::getContainer()->getParameter('kernel.project_dir').'/public'.$imageForm->getData()->getImagePath();
            self::assertFileExists($storedPath);
            self::assertStringStartsWith('/uploads/excursions/', $imageForm->getData()->getImagePath());
        } finally {
            @unlink($path);
            if ($storedPath !== null) {
                @unlink($storedPath);
            }
        }
    }

    private function submitImage(string $path, string $filename): array
    {
        $form = $this->formFactory->create(ExcursionImageType::class, new ExcursionImage());
        $form->submit([
            'imageFile' => new UploadedFile($path, $filename, null, null, true),
        ], false);

        $messages = [];
        foreach ($form->get('imageFile')->getErrors(true) as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }

    private function createImage(string $format, int $width = 2, int $height = 2): string
    {
        $path = tempnam(sys_get_temp_dir(), 'excursion-image-');
        $image = imagecreatetruecolor($width, $height);

        match ($format) {
            'jpeg' => imagejpeg($image, $path),
            'png' => imagepng($image, $path),
            'webp' => imagewebp($image, $path),
        };

        imagedestroy($image);

        return $path;
    }
}