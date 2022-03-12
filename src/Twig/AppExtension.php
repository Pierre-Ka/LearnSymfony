<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/*
    Le but est ici de créer une fonction Twig
    Porter votre attention sur les raccourcis d'écriture aborder en bas
 */

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        /*
            Ici on crée notre fonction Twig, on l'appelle Pluralize et lorsque qu'elle est appelée, elle appelle
            elle-même la callback doSomething()
         */
        return [
            new TwigFunction('pluralize', [$this, 'doSomething']),
        ];
    }

    public function doSomething(int $count,string $singular, ?string $plurial = null) : string
    {
        /*
            Notre fonction pluralize à pour but de mettre Pin au singulier s'il n'y a qu'un seul Pin
            et Pins au pluriel, si il y a 0 ou plusieurs Pins
        */
        $str = $count === 1 ? $singular : $plurial;
        return "$count $str";
            /*
                Il faut utiliser les doubles cotes car si simple cote, on ne peut pas faire d'interpolation
            */

        /*
            Maintenant on va rendre le pluriel optionnel : on met ? devant string :
             public function doSomething(int $count,string $singular, ?string $plurial = null) : string
            et on definit une valeur
            par default à null puis on rajoute cette ligne au tout debut :
            $plurial = $plurial ?? $singular . 's';
            // Si on a un pluriel on va l'utiliser sinon on prend le singulier et on rajoute un 's'

            Derniere chose :
            $plurial = $plurial ?? $singular . 's'; peut s'écrire
            $plurial ??= $singular . 's'; => Si le pluriel existe on le prend et le met dans pluriel sinon le reste
        */
    }
}
