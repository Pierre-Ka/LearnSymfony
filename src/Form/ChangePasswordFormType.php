<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
    Ce formulaire sera utilisé dans 2 situations : lorsque l'utilisateur a oublier son password, dans le cas là,
    il y accedera avec un lien token envoyé sur sa boite mail et aussi lorsqu'un utilisateur connecté souhaite changer
    de password. Pour ce dernier cas, on lui demandera d'entrer son password actuel avant de pouvoir le modifier. Pour
    cela on va rajouter une entrée et ajouter une option ( methode configureOptions() ) 'current_password_is_required'
    par défault à false. Ainsi pour afficher cette entrée, il faudra setter cette option à true.
 */
class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($options['current_password_is_required']) {
            $builder
                -> add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'attr' => [
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your current password',
                    ]),
                    new UserPassword(['message' => 'Invalid current password']),
                ]]);
        }
        $builder
            -> add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your current password',
                    ]),
                    new UserPassword(),
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
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
                    'label' => 'New password',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Repeat Password',
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_password_is_required' => false
        ]);
        /*
            Pour notre option, on peut définir les types autorisés :
            $resolver->addAllowedTypes('current_password_is_required', ['bool', 'string']);
         */
        $resolver->addAllowedTypes('current_password_is_required', 'bool');
    }
}
