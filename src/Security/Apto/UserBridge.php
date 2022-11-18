<?php

namespace App\Security\Apto;

use App\Entity\Apto\User;
use App\Repository\Apto\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this email")
 */
class UserBridge implements OAuthAwareUserProviderInterface
{

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher,MailerInterface $mailer){

        $this->em = $entityManager;
        $this->passwordHasher = $userPasswordHasher;
        $this->mailer = $mailer;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {


        $user = $this->em->getRepository(User::class)->findOneBy(['email'=>$response->getEmail()]);

        //when the user is registering
        //TODO change email from address to be brought from config
        if (null === $user) {

            $user = new User();
            $user->setUsername($response->getEmail());
            $user->setIsVerified(true);

            $password = $this->randomPassword();
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $user->setEmail($response->getEmail());

            $this->em->persist($user);
            $this->em->flush();

            //send social email
            $email = (new TemplatedEmail())
                ->from('info@wedmyway.gr')
                ->to(new Address($user->getEmail()))
                ->subject('Thanks for signing up!')

                // path of the Twig template to render
                ->htmlTemplate('theme/security/registration_email_social.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'username' => $user->getUserIdentifier(),
                    'password' => $password,
                ])
            ;
            $this->mailer->send($email);
        }

        return $user;
    }

    private function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

}
