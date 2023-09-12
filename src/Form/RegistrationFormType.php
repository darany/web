<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('prenom', null, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prénom',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('email', null, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un e-mail',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moin {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
