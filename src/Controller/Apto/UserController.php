<?php

namespace App\Controller\Apto;

use App\Entity\Apto\User;
use App\Form\Apto\UserFilterType;
use App\Form\Apto\UserType;
use App\Repository\Apto\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     */
    public function index(Request $request,UserRepository $userRepository): Response
    {

        $filterType = 'App\Form\Apto\\'.ucfirst($this->entityName).'FilterType';
        $form = $this->createForm(get_class(new $filterType));

        $form->handleRequest($request);

        $criteria = $form->getData() ?? [];
        $currentPage = $criteria['page'] ?? 1;
        $orderBy = [
            !empty($criteria['sortBy']) ? $criteria['sortBy'] : 'id',
            !empty($criteria['sort']) ? $criteria['sort'] : 'ASC'
        ];
        unset($criteria['sortBy'],$criteria['sort'],$criteria['page']);

        $offset = ($currentPage - 1) * self::PER_PAGE;

        $total = ${$this->entityName.'Repository'}->count($criteria);
        $entities = ${$this->entityName.'Repository'}->findBy($criteria,$orderBy,self::PER_PAGE,$offset);

        return $this->renderForm('theme/'.$this->entityName.'/index.html.twig', [
            'entities' => [
                'items'=>$entities,
                'total' => $total,
                'perPage' => self::PER_PAGE,
                'offset' => $offset,
            ],
            'form' => $form,
        ]);
    }

    /**
     * @Route("/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(UserRepository $userRepository,Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher,ValidatorInterface $validator): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $errors = [$form->getErrors(true)];

        if ($request->isMethod('POST')) {

            if (null == $form->get('plainPassword')->getData()) {
                $errors[] = [new FormError('Please enter a password')];
            }

            $errorCount = 0;
            foreach ($errors as $errorIterator) {
                foreach ($errorIterator as $error) {
                    ++$errorCount;
                    $this->addFlash('error', $error->getMessage());
                }
            }

            if (
                ($form->isSubmitted() && $form->isValid()) &&
                ($errorCount == 0)
            ) {

                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setCreated(new DateTime());
                $user->setUpdated(new DateTime());

                $userRepository->add($user, true);
                $this->addFlash('success', 'User created');

                $this->cache->flushdb();

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('theme/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('theme/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(User $user,UserRepository $userRepository,Request $request, UserPasswordHasherInterface $userPasswordHasher,ValidatorInterface $validator,EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $errors = [$form->getErrors(true)];

        if ($request->isMethod('POST')) {

            $errorCount = 0;
            foreach ($errors as $errorIterator) {
                foreach ($errorIterator as $error) {
                    ++$errorCount;
                    $this->addFlash('error', $error->getMessage());
                }
            }

            if (
                ($form->isSubmitted() && $form->isValid()) &&
                ($errorCount == 0)
            ) {

                if (null !== $form->get('plainPassword')->getData()) {

                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user ,
                            $form->get('plainPassword')->getData()
                        )
                    );
                }

                $user->setUpdated(new DateTime());

                $userRepository->add($user, true);
                $this->addFlash('success', 'User updated');

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('theme/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);

            $this->cache->flushdb();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
