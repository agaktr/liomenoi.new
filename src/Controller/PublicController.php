<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;


class PublicController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {

        $ed = new EventDispatcher();

        $options = [

            'api_token' => '45a50e7d0f3e99b4e902a1973184aa69',
            'event_dispatcher' => [
                'adapter' => $ed
            ],
            // We make use of PSR-17 and PSR-18 auto discovery to automatically guess these, but preferably set these explicitly.
//            'http' => [
//                'client' => null,
//                'request_factory' => null,
//                'response_factory' => null,
//                'stream_factory' => null,
//                'uri_factory' => null,
//            ]
        ];

        $tmdbClient = new Client($options);
        var_dump($tmdbClient->getMoviesApi()->getMovie(550));


        return $this->render('public/home.html.twig', [
        ]);
    }
}
