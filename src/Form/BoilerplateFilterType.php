<?php

namespace App\Form;

use App\Entity\Apto\User;
use App\Repository\Apto\UserRepository;
use Doctrine\ORM\Cache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoilerplateFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('Column1',null,[
                'required' => false,
            ])
            ->add('Column2',null,[
                'required' => false,
            ])
            ->add('user',EntityType::class,[
                'class' => User::class,
                'required' => false,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->setCacheable(true)
                        ->setCacheMode(Cache::MODE_NORMAL)
                        ->setCacheRegion('region');
                },
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
            ->add('search', SubmitType::class, [
                'label' => 'search',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
