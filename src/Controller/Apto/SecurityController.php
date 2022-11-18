<?php

namespace App\Controller\Apto;

use App\Entity\Apto\User;
use App\Form\Apto\RegistrationFormType;
use App\Security\Apto\EmailVerifier;
use App\Security\Apto\SecurityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AptoAbstractController
{

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {

            $translator = new Translator('en');

            $this->addFlash('error',
                $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security')
            );
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('theme/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }


    /**
     * @Route("/api/login", name="app_api_login")
     */
    public function apiLogin(): Response
    {

        return $this->json([
            'message' => $this->getUser()->getUserIdentifier(),
            'token' => 'maybeToken',
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     * @throws Exception
     */
    public function register(Request $request,EmailVerifier $emailVerifier,ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,UserAuthenticatorInterface $userAuthenticator,SecurityAuthenticator $securityAuthenticator): Response
    {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );


                $entityManager->persist($user);

                $entityManager->flush();

                $translator = new Translator('en');

                // generate a signed url and email it to the user
                try {
                    $emailVerifier->sendEmailConfirmation('app_verify_email' , $user ,
                        (new TemplatedEmail())
                            ->from(new Address('info@symfony.blackflag.cloud' , 'Blackflag'))
                            ->to($user->getEmail())
                            ->subject($translator->trans('Please Confirm your Email',[],'register'))
                            ->htmlTemplate('theme/security/confirmation_email.html.twig')
                    );
                } catch (RfcComplianceException $e) {

                    //delete user
                    $entityManager->remove($user);
                    $entityManager->flush();

                    //add flash message and redirect to register
                    $this->addFlash('error', $translator->trans('Invalid email address',[],'register'));
                    return $this->redirectToRoute('app_register');
                }

                //login the user
                $userAuthenticator->authenticateUser($user,$securityAuthenticator,$request);

                //add flash message
                $this->addFlash('success', $translator->trans('Registration successful. Please check your email to verify your account.',[],'register'));

                return $this->redirectToRoute('app_login');
            }
        }catch (Exception $e) {
        }


        return $this->render('theme/security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify", name="app_verify")
     */
    public function verify(): Response
    {
        if ($this->getUser() && $this->getUser()->isVerified()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('theme/security/verify.html.twig');
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request,EmailVerifier $emailVerifier): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
