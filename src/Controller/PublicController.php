<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tmdb\Client;


class PublicController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {

        $options['api_token'] = '45a50e7d0f3e99b4e902a1973184aa69';
        $tmdbClient = new Client($options);
        var_dump($tmdbClient->getMoviesApi()->getMovie(550));


        return $this->render('public/home.html.twig', [
        ]);
    }
}
