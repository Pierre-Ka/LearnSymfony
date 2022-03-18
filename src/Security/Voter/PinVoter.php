<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PinVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html

        /*
            Si notre attribut fait parti de ce tableau et le sujet est un objet de type
            App\Entity\Pin alors true
         */
        return $attribute === 'PIN_CREATE' || (in_array($attribute, ['PIN_EDIT', 'PIN_CREATE', 'PIN_DELETE'])
            && $subject instanceof \App\Entity\Pin) ;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        /*
            Ce code permet de dire qu'on utilisateur non connecté tentant d'acceder en utilisant get par exemple
            aura un accès denied.
         */
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'PIN_CREATE':
                /* Vu que l'on a un return, on a pas besoin du break ; */
                return $user->isVerified();
            case 'PIN_EDIT':
                return $user->isVerified() && $user == $subject->getUser();
            case 'PIN_DELETE':
                return $user->isVerified() && $user == $subject->getUser();
                /* Ici on voit que les conditions appelées sont similaires.
                    On aurait pu appelé un attribut 'PIN_MANAGE' au lieu de
                    deux ( PIN_EDIT et PIN_DELETE )
                */
        }
        return false;
    }
}
