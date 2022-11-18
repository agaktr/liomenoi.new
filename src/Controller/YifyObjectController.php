<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use App\Entity\YifyObject;
use App\Form\YifyObjectType;
use App\Repository\BoilerplateRepository;
use App\Repository\YifyObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/yify")
 */
class YifyObjectController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_yify_object_index", methods={"GET"})
     */
    public function index(Request $request,YifyObjectRepository $yifyObjectRepository): Response
    {

        $this->entityName = 'yifyObject';

        $this->handleFilterForm($request);

        $total = $yifyObjectRepository->count($this->criteria);
        $entities = $yifyObjectRepository->findBy($this->criteria,$this->orderBy,self::PER_PAGE,$this->offset);

        return $this->renderForm($this->entityName.'/index.html.twig', [
            'entities' => [
                'items'=>$entities,
                'total' => $total,
                'perPage' => self::PER_PAGE,
                'offset' => $this->offset,
            ],
            'form' => $this->filterForm,
        ]);
    }

    /**
     * @Route("/new", name="app_yify_object_new", methods={"GET", "POST"})
     */
    public function new(Request $request, YifyObjectRepository $yifyObjectRepository): Response
    {
        $yifyObject = new YifyObject();
        $form = $this->createForm(YifyObjectType::class, $yifyObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $yifyObjectRepository->add($yifyObject, true);

            return $this->redirectToRoute('app_yify_object_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('yify_object/new.html.twig', [
            'yify_object' => $yifyObject,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_yify_object_show", methods={"GET"})
     */
    public function show(YifyObject $yifyObject): Response
    {
        return $this->render('yify_object/show.html.twig', [
            'yify_object' => $yifyObject,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_yify_object_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, YifyObject $yifyObject, YifyObjectRepository $yifyObjectRepository): Response
    {
        $form = $this->createForm(YifyObjectType::class, $yifyObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $yifyObjectRepository->add($yifyObject, true);

            return $this->redirectToRoute('app_yify_object_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('yify_object/edit.html.twig', [
            'yify_object' => $yifyObject,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_yify_object_delete", methods={"POST"})
     */
    public function delete(Request $request, YifyObject $yifyObject, YifyObjectRepository $yifyObjectRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$yifyObject->getId(), $request->request->get('_token'))) {
            $yifyObjectRepository->remove($yifyObject, true);
        }

        return $this->redirectToRoute('app_yify_object_index', [], Response::HTTP_SEE_OTHER);
    }
}
