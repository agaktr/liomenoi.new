<?php

namespace App\Twig;

use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function extractEntityNameByPath(string $name): string
    {

        return explode('/', $name)[0];
    }

    public function pagination($collection): string
    {

        return $this->twig->render('theme/pagination/pagination.html.twig', [
            'collection' => $collection,
        ]);
    }

    public function filters($form): string
    {

        return $this->twig->render('theme/filters/filters.html.twig', [
            'form' => $form,
        ]);
    }
}