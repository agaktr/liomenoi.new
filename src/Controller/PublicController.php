<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use App\Service\TMDBService;
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
    public function index(TMDBService $TMDBService): Response
    {

//        $a = $TMDBService->client->getMoviesApi()->getMovie(550);

        $a = $TMDBService->client->getFindApi()->findBy('tt0111161', ['external_source' => 'imdb_id']);

var_dump($TMDBService->client->getConfigurationApi()->getConfiguration());
//        var_dump($client->getGenresApi()->getGenre('18'));
        var_dump($TMDBService->client->getGenresApi()->getGenres(['language' => 'el-GR']));
        var_dump($a);
        var_dump($TMDBService->client->getMoviesApi()->getMovie(278,['language' => 'el-GR']));


        $repository = new MovieRepository($TMDBService->client);
        /** @var Movie $movie */
        $movie = $repository->load(87421);

        var_dump($movie);
//        var_dump($movie->getTitle());
//        var_dump($movie->getImages());
//        var_dump($movie->getPosterPath());
//        var_dump($movie->getBackdropImage());
//        var_dump($movie->getBackdropPath());
//        var_dump($movie->getVideos());

        return $this->render('public/home.html.twig', [
        ]);
    }
}
