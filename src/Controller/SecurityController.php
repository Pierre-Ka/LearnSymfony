<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        /*
            On decommente le code suivant : ce code commenté par défault permet de dire : si l'utilisateur est connecté
            et tente d'acceder à la page de connexion alors il est redirigé à la page d'accueil
        */
        if ($this->getUser()) {
            $this->addFlash('error', 'Already logged in'); /* Ajout */
            return $this->redirectToRoute('app_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /*
        La methode logout a été défini dans security.yaml : firewalls: main: logout:  path: app_logout
        En fait ici cette methode n'a pas besoin d'avoir de contenu : Comme elle a été défini dans la config
        le firewall interceptera ce nom de route pour nous deconnecter.
        Cette methode s'effectue par default en cliquant sur un lien cad en faisant une requete GET. On a donc
        vulnérable aux failles CSRF sur cette methode. Ici on souhaite se rendre non vulnérable en utlisant un token
        2 possibilités : utiliser un token en GET et utiliser un token en POST.

        Pour utiliser la methode en POST il faut rajouter un formulaire caché generant un token au niveau du lien de
        la navigation 'logout'. Attention malgré la mise en place du token, si on modifie artificiellement le token,
        on voit que la deconnexion s'effectue toujours. Il faut rajouter un paramètre dans security.yalm
        firewalls:main:logout nommé csrf_token_generator: Symfony\Component\Security\Csrf\CsrfTokenManagerInterface

        Rappel : sans token ou avec token, on a pas besoin de contenu, Symfony se chargera de nous deconnecter.

        Maintenant on souhaite ajouter un message flash lors de la deconnexion. Mais comment faire puisque tout est géré
        par Symfony, on a donc pas la possibilité de rajouter du code ici. Pour cela on va écouter l'evenement
        Logout Event et lorsque l'evenement est déclencher on va appeler une callback affichant notre message flash
        et redirigeant vers la page d'accueil. Pour cela on fait s make:subscriber
    */
    #[Route(path: '/logout', name: 'app_logout', methods: "POST")]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
