<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/*
    Une fois la partie public, la connexion, l'enregistrement et le reset password fini,
    On s'occupe de faire un page Account ou l'on pourra changer nos mishibushi de compte.
    Pour cela : s make:controller :: Account
    On pourrait definir une pré-route pour toute la classe :
    #[Route('/account')]
 */
/*
    ici on va s'interresser aux droits d'acces. Une première manière de faire cela est de définir dans
    security.yaml : access_control:  { path: ^/account, roles: ROLE_USER }. Un utilisateur non connecté qui tente
    d'acceder à ces pages sera redirigées automatiquement sur un path defini dans la methode start() de
    l'AbstractLoginFormAuthenticator. On va redefinir cette methode dans LoginFormAuthenticator afin d'y incorporer
    un message Flash.
    Une deuxieme manière de faire consiste a incorporer :
    $this->denyAccessUnlessGranted('ROLE_USER');
    A chacune des methodes.

    Ici on choisir d'utiliser l'annotation isGranted
 */
#[isGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account', methods: "GET")]
    public function show(): Response
    {
        return $this->render('account/show.html.twig');
    }

    #[Route('/account/edit', name: 'app_account_edit', methods: "GET|PATCH")]
    /*
        L'attribut 'IS_AUTHENTICATED_FULLY' signifie que l'utilisateur ne s'est pas connecté via son cookie
        (donc automatiquement ) mais via le login ( plus de sécurité ).
     */
    #[isGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'Account updated successfully!');
            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/account/change-password', name: 'app_account_change_password', methods: "GET|PATCH")]
    #[isGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /*
            Dans le cas de changement de password d'un utilisateur connecté, on va réutiliser le formulaire soumis lors
            d'un oubli de password (lien Forget Password ?), mais on va rajouter en premier une entrée pour taper le
            password actuel. Lorsqu'on ajoute cette entrée dans notre formulaire, on va définir une option pour que par
            défault, ce champ ne s'affiche pas. Il faudra setter l'option 'current_password_is_required' à true pour
            que ce champ s'affiche.
         */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class, null, [
                'current_password_is_required' => true,
                'method' => 'PATCH'
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form['plainPassword']->getData())
            );
            $em->flush();
            $this->addFlash('success', 'Password updated successfully!');
            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
