<?php

namespace App\Form;

use App\Entity\Rencontre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RencontreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heureDebut')
            ->add('heureFin')
            ->add('statut')
            ->add('scoreEquipeA')
            ->add('scoreEquipeB')
            ->add('meteo')
            ->add('coteEquipeA')
            ->add('coteEquipeB')
            ->add('equipeA')
            ->add('equipeB')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rencontre::class,
        ]);
    }
}
