<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use App\Entity\Boilerplate;
use App\Form\BoilerplateType;
use App\Repository\BoilerplateRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/boilerplate")
 * @IsGranted("ROLE_ADMIN")
 */
class BoilerplateController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_boilerplate_index", methods={"GET"})
     */
    public function index(Request $request,BoilerplateRepository $boilerplateRepository): Response
    {

        $this->handleFilterForm($request);

        $total = ${$this->entityName.'Repository'}->count($this->criteria);
        $entities = ${$this->entityName.'Repository'}->findBy($this->criteria,$this->orderBy,self::PER_PAGE,$this->offset);

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
     * @Route("/new", name="app_boilerplate_new", methods={"GET", "POST"})
     */
    public function new(Request $request,BoilerplateRepository $boilerplateRepository): Response
    {
        $boilerplate = new Boilerplate();
        $form = $this->createForm(BoilerplateType::class, $boilerplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boilerplateRepository->add($boilerplate, true);

            $this->cache->flushdb();

            return $this->redirectToRoute('app_boilerplate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('boilerplate/new.html.twig', [
            'boilerplate' => $boilerplate,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_boilerplate_show", methods={"GET"})
     */
    public function show(Boilerplate $boilerplate): Response
    {
        return $this->render('boilerplate/show.html.twig', [
            'boilerplate' => $boilerplate,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_boilerplate_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request,Boilerplate $boilerplate, BoilerplateRepository $boilerplateRepository): Response
    {
        $form = $this->createForm(BoilerplateType::class, $boilerplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boilerplateRepository->add($boilerplate, true);

            return $this->redirectToRoute('app_boilerplate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('boilerplate/edit.html.twig', [
            'boilerplate' => $boilerplate,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_boilerplate_delete", methods={"POST"})
     */
    public function delete(Request $request,Boilerplate $boilerplate, BoilerplateRepository $boilerplateRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$boilerplate->getId(), $request->request->get('_token'))) {
            $boilerplateRepository->remove($boilerplate, true);

            $this->cache->flushdb();
        }

        return $this->redirectToRoute('app_boilerplate_index', [], Response::HTTP_SEE_OTHER);
    }
}
