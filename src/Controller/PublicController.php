<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PublicController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {

        return $this->render('public/home.html.twig', [
        ]);
    }
}
