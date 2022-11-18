<?php

namespace App\Controller\Apto;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AptoAbstractController
{
    /**
     * @Route("/locale", name="app_locale")
     */
    public function index(Request $request): Response
    {

        //get local from post request body
        $locale = json_decode($request->getContent(), true)['locale'];

        //save locale in session
        $request->getSession()->set('_locale', $locale);

        //return locale from session
        return new JsonResponse($request->getSession()->get('_locale'));
    }
}
