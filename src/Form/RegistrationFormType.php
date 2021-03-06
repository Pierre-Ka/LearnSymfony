<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
    On pourrait créer une propriété plainPassword dans la classe User ( sans le mapper en bdd ).
    Le password sera alors stocké en clair, il faudra setter la methode eraseCredentials
    { $this->plainPassword = null ;} et appeler cette methode juste après avoir hasher le password et l'avoir
    sauvegarder.
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)  /* Ajout */
            ->add('lastName', TextType::class)   /* Ajout */
            ->add('email', EmailType::class)
            /*
                Pour les champs non mappés 'agreeTerms' et 'plainPassword' on n'a pas besoin de definir de contraintes
                en classe ( de toute façon comme ils sont non mappés on ne pourrait pas. Mais on pourrait définir les
                contraintes directement dans le formulaire ( cela est prérempli pour nous dans ce cas )
            */
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,          /* Permet de ne pas avoir de correspondance en classe */
                'label' => 'I consent to the privacy policy and terms of services ',
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
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
