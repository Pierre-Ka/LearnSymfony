<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
    Ici on souhaite afficher un message flash lors de la deconnexion. Le problème s'est que la deconnexion est gérée
    automatiquement par Symfony, on a donc pas la possibilité de rajouter du code dans la fonction logout() du
    SecurityController. Pour cela on va écouter l'evenement Logout.
    On fait s make:subscriber et on appelle notre event subscriber : LogoutEventSubscriber puis
    on choisit dans la liste l'evenement auquel on veut souscrire : Symfony\Component\Security\Http\Event\LogoutEvent.
    A partir de là, cette classe est crée automatiquement
 */
/*
    Un subscriber hérite toujours de EventSubscriberInterface
 */
class LogoutEventSubscriber implements EventSubscriberInterface
{
    /* Nouveauté PHP 8 , le code ci-dessous peut s'écrire :
        public function __construct( private UrlGeneratorInterface $urlGenerator)
        {
        }
    */
    private UrlGeneratorInterface $urlGenerator;
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }


    /*
        On definit la callback à appelée : ici onLogoutEvent().
    */
    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }

    public function onLogoutEvent(LogoutEvent $event)
    {
        /*
            Ici c'est à nous de définir le contenu. Via l'event on peut recuperer la request et setter la Response
            ( voir la classe LogoutEvent ).
            On va chercher à transmettre une url. On doit donc injecter quelque chose en rapport avec les url :
            s debug:autowiring url. On va choisir Symfony\Component\Routing\Generator\UrlGeneratorInterface et
            l'injecter dans cette classe.
        */
    $event->getRequest()->getSession()->getFlashBag()->add(
        'success',
        'Bye Bye ' . $event->getToken()->getUser()->getFullName() . '. Hope to see you soon !'
    );
    $event->setResponse( new RedirectResponse( $this->urlGenerator->generate('app_home')));
    }
}
