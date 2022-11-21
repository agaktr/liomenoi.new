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

        $tmdbClient = new Client();
        var_dump($tmdbClient->getMoviesApi()->getMovie(550));

        return $this->render('public/home.html.twig', [
        ]);
    }
}
