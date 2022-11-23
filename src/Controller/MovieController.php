<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/movie")
 */
class MovieController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_movie_index", methods={"GET"})
     */
    public function index(Request $request,MovieRepository $movieRepository): Response
    {


//        var_dump($request->query->all());

        $this->isApi = true;

        $movies = $movieRepository->findBy(['fetched'=>true],[ 'id' => 'ASC'], 48,0);

        $moviesResponse = [];
        foreach ($movies as $movie) {
            $moviesResponse[$movie->getId()] = [
                'id' => $movie->getId(),
                'title' => $movie->getTitle(),
                'originalTitle' => $movie->getOriginalTitle(),
                'runtime' => $movie->getRuntime(),
                'overview' => $movie->getOverview(),
                'poster' => $movie->getPoster(),
                'backdrop' => $movie->getBackdrop(),
                'release_date' => $movie->getReleaseDate()->format('Y-m-d'),
                'genres' => $movie->getCategories(),
            ];
        }

        return $this->render('movie/index.html.twig', [
            'movies' => $moviesResponse,
            'resp' =>$request->query->all()
        ]);
    }



    /**
     * @Route("/new", name="app_movie_new", methods={"GET", "POST"})
     */
    public function new(Request $request, MovieRepository $movieRepository): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movieRepository->add($movie, true);

            return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_movie_show", methods={"GET"})
     */
    public function show(Movie $movie): Response
    {

        $this->isApi = true;


        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_movie_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Movie $movie, MovieRepository $movieRepository): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movieRepository->add($movie, true);

            return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_movie_delete", methods={"POST"})
     */
    public function delete(Request $request, Movie $movie, MovieRepository $movieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $movieRepository->remove($movie, true);
        }

        return $this->redirectToRoute('app_movie_index', [], Response::HTTP_SEE_OTHER);
    }
}
