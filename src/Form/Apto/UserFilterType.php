<?php

namespace App\Form\Apto;

use App\Entity\Apto\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('username',null,[
                'required' => false,
            ])
            ->add('email',null,[
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Role' => '',
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
            ])
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Sort By' => '',
                    'id' => 'ID',
                    'Username' => 'username',
                    'Email' => 'email',
                ],
            ])
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Sort' => '',
                    'ASC' => 'ASC',
                    'DESC' => 'DESC',
                ],
            ])
            ->add('page', HiddenType::class, [
                'data' => 1,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
