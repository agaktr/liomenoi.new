<?php

namespace App\Form;

use App\Entity\Apto\User;
use App\Entity\Boilerplate;
use App\Repository\Apto\UserRepository;
use Doctrine\ORM\Cache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoilerplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Column1')
            ->add('Column2')
            ->add('user',EntityType::class,[
                'class' => User::class,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->setCacheable(true)
                        ->setCacheMode(Cache::MODE_NORMAL)
                        ->setCacheRegion('region');
                },
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Boilerplate::class,
        ]);
    }
}
