<?php

namespace App\Form;

use App\Entity\Pin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
/* *********** VICH *****************/
use Vich\UploaderBundle\Form\Type\VichImageType;
/**************************************************/


class PinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           /*
                On peut setter la methode et l'action comme cela :
                    -> setMethod('...')
                    -> setAction('...')
           */
           /* *********** VICH *****************/
           ->add('imageFile', VichImageType::class, [
                           'label' => 'Image (JPG or PNG file)',
                           'required' => false,
                           'allow_delete' => true,
                           /*'delete_label' => 'Delete ?',*/
                           /*'download_label' => '...',*/
                           'download_uri' => false,
                           /*'image_uri' => true,*/
                           'imagine_pattern' => 'mon_filtre_square_miniature_petite',
                           /*'asset_helper' => true,*/
                       ]) /*;*/
           /*
                Il faut retirer le point-virgule de la fin.
                L'option 'imagine_pattern' necessite le bundle LiipImagineBundle : il faut d'abord définir un filtre
                LiipImagine pour pouvoir l'utiliser.
                On rajoute une option 'label'. L'option 'required' => true necessite la mise en place d'un @Assert
                pour une verification coté serveur. L'option 'allow_delete' a true permet de rajouter une petite case
                à cocher pour supprimer l'image ayant comme label 'delete_label'. 'download_label' et 'download_uri'
                => true, permettent d'avoir un lien pour télécharger l'image, 'image_uri' va utiliser le storage,
                et 'asset_helper' genere le lien de telechargement via la fonction asset() twig.
                use Symfony\Component\Validator\Constraints\File\Image;
                Il est possible de définir les contraintes egalement dans les options :
                             'constraints' => [
                                new Image(['max_size' => '10M' ])
                                    ]
                Maintenant on souhaite rendre l'image obligatoire pour les updates mais facultative à la création.
                Pour cela on doit savoir si c'est l'edition ou la création.

                $isEdit = $options['method'] === 'PUT' ; ou $isEdit = $pin && $pin->getId();
                $imageFileConstraints = [] ;
                if ($isEdit) {
                    $imageFileConstraints[] = nos contraintes ici ;
                }
            /**************************************************/
            ->add('title')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pin::class,
        ]);
    }
}

