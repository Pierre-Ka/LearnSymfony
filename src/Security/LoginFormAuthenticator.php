<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    /*
        Une fois que notre classe LoginFormAuthenticator est créé et setter dans le security.yalm comme
        firewalls: main:  custom_authenticator: App\Security\LoginFormAuthenticator alors elle doit implementer
        obligatoirement une methode supports(). Cette methode ( qui se trouve ici dans la classe parente :
        AbstractLoginFormAuthenticator ) a pour charge de verifier si on est dans le cas d'une authentification
        ( cad si route=/login et methode=post ) si elle retourne true alors elle appelle la methode authenticate()
     */
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        /*
            L'urlGenerator est une classe générant les urls à partir des noms de route
            ( ex : '/' avec 'app_home' )
        */
        $this->urlGenerator = $urlGenerator;
    }

    public function authenticate(Request $request): Passport
    {
        /*
            On sort l'email entré et on va le sauvegarder au niveau d'une constante Security::LAST_USERNAME
        */
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        /*
            Ici notre methode nous retourne un objet Passport qui prend en argument un objet de type UserInterface
            ( donc si notre User n'implementait pas l'interface on ne pourrait pas se connecter ), un objet de type
            PasswordCredentials qui retourne notre password, et un tableau de badges ( badges[] ) facultatifs dans
            lequel on retrouve notre CsrfTokenBadge, mais on pourrait avoir egalement PasswordUpgradeBadge,
            RememberMeBadge, ect ...
            D'ailleurs ici on rajoute RememberMeBadge
        */

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $request->getSession()->getFlashBag()->add('success',
            'Welcome '. $token->getUser()->getFullName()). '!';
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /*
        Lors de la mise en place des droits d'acces, la redirection des personnes qui n'ont pas les droits d'acces
        necessaires se fait via la methode start(). Ici on la redéfinie afin d'y incorporer un message flash.
        On override donc la methode.
     */
    public function start(Request $request, AuthenticationException $authException = null ): Response
    {

        $request->getSession()->getFlashBag()->add('error', 'You need to log in first');
        $url = $this->getLoginUrl($request);
        return new RedirectResponse($url);

    }
}
