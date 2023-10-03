<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Validator\Constraints\DateGreaterThanOrEqual;

use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RencontreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipeA', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-select',
                ]
            ])
            ->add('equipeB', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-select',
                ]
            ])
            ->add('heureDebut', DateTimeType::class)
            ->add('heureFin', DateTimeType::class)
            ->add('coteEquipeA', NumberType::class)
            ->add('coteEquipeB', NumberType::class)
            ->add('meteo', TextType::class, [
                'constraints' => new Length(['max' => 255]),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rencontre::class,
            'constraints' => [
                new DateGreaterThanOrEqual()
            ]
        ]);
    }
}
