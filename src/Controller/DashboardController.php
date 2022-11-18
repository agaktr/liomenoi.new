<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/dashboard")
 * @IsGranted("ROLE_USER")
 */
class DashboardController extends AptoAbstractController
{

    /**
     * @Route("/", name="app_dashboard")
     */
    public function index(): Response
    {


        if ($this->getUser() && !$this->getUser()->isVerified()) {
            return $this->redirectToRoute('app_verify');
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    /**
     * @Route("/settings", name="app_dashboard_settings")
     */
    public function settings(Request $request,UserPasswordHasherInterface $userPasswordHasher,ValidatorInterface $validator,EntityManagerInterface $entityManager): Response
    {

        //if POST request then we are updating the user
        if ($request->getMethod() === 'POST'){

            try {
                $user = $this->getUser();

                //user password
                if (!empty($request->get('password'))){

                    if ($request->get('password') !== $request->get('repeatPassword')){
                        $this->addFlash('error', 'Passwords do not match');
                        throw new Exception('Passwords do not match');
                    }

                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $request->get('password')
                        )
                    );
                }
                $user->setEmail($request->get('email'));

                $entityManager->flush();

                $this->addFlash('success', 'Settings saved');

            }catch (Exception $e) {
            }
        }


        return $this->render('dashboard/settings.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
