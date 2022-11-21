<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Model\Movie;
use Tmdb\Repository\MovieRepository;


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

        $client = new Client($options);

        /**
         * Required event listeners and events to be registered with the PSR-14 Event Dispatcher.
         */
        $requestListener = new RequestListener($client->getHttpClient(), $ed);
        $ed->addListener(RequestEvent::class, $requestListener);

        $apiTokenListener = new ApiTokenRequestListener($client->getToken());
        $ed->addListener(BeforeRequestEvent::class, $apiTokenListener);

        $acceptJsonListener = new AcceptJsonRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $acceptJsonListener);

        $jsonContentTypeListener = new ContentTypeJsonRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $jsonContentTypeListener);

        $userAgentListener = new UserAgentRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $userAgentListener);

        $a = $client->getFindApi()->findBy('tt0111161', ['external_source' => 'imdb_id']);


        var_dump($client->getGenresApi()->getGenre('18'));
        var_dump($a);
        var_dump($client->getMoviesApi()->getMovie(550));


        $repository = new MovieRepository($client);
        /** @var Movie $movie */
        $movie = $repository->load(87421);

        var_dump($movie->getTitle());
        var_dump($movie->getPosterImage());
        var_dump($movie->getPosterPath());
        var_dump($movie->getBackdropImage());
        var_dump($movie->getBackdropPath());
        var_dump($movie->getVideos());

        return $this->render('public/home.html.twig', [
        ]);
    }
}
