<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Repository\PinRepository;

/*
 * On souhaite obtenir une architecture router de type :
 * GET /events
 * GET|POST /events/create
 * GET /events/{id}
 * GET|PUT /events/{id}/edit
 * DELETE /events/{id}
 */

class PinController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: "GET")]
    /*
        #[Route('/', name: 'app_second_name')] . On pourrait avoir un deuxieme nom de route sans problème
    */
    public function index(PinRepository $pinRepository): Response
    {
        /*
            Ici on va chercher tous les pins , [] ( ensemble de critères vides, puis on met ordonner par date de
            creation descendant.
            On pourrait limiter le nombre de pin à 2 par exemple avec :
            $pins = $pinRepository->findBy([], ['createdAt' => 'DESC' ], 2 );
        */
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC' ]);
        return $this->render('pin/index.html.twig', compact('pins')
        );
    }

    #[Route('/pins/create', name: 'app_pins_create', methods: "GET|POST", priority: "10")]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $pin = new Pin;
        /*
            Contrairement à la creation d'un form avec createFormBuilder(). On appelle ici notre formulaire avec
            createForm(). Une version commentée appellant createFormBuilder() se trouve en bas.
        */
        $form = $this->createForm(PinType::class, $pin);
        /*
            Donne moi le formulaire associé à la classe Pin : en fait on a une double déclaration de Pin ici :
            La première c'est tout simplement le fait de passer l'objet $pin, la seconde
            c'est la clé 'data_class' dans PinType->configureOptions->setDefault.
            Un seul des deux serait en réalité suffisant.
        */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created');
            return $this->redirectToRoute('app_home');
            /*
                La redirection permet de ne pas pouvoir resoumettre les données !
             */
        }
        return $this->render('pin/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /*
        Ici Symfony va utiliser le ParamConvert : l'id de la route sera injecté dans la dependance demandé ( Pin $pin )
        Si il ne trouve pas l'id : erreur en dev ou page 404 en prod. Si on voulait costumiser les error pages :
        https://symfony.com/doc/current/controller/error_pages.html
        Il faut créer notre fichier error404.html.twig dans templates/bundles/TwigBundle/Exception/
    */
    #[Route('/pins/{id<\d+>}', name: 'app_pins_show', methods: "GET")]
    public function show(Pin $pin): Response
    {
        return $this->render('pin/show.html.twig', compact('pin')
        );
    }

    /*
        On pourrait très bien utiliser POST mais histoire d'être plus "correct" on va utiliser PUT : methods: "GET|PUT"
        Pour que cela marche il faut autoriser override des methodes http :
         config->package->framework.yaml->http_method_override : mettre à true
    */
    #[Route('/pins/{id<\d+>}/edit', name: 'app_pins_edit', methods: "GET|PUT")]
    public function edit(Request $request, Pin $pin,  EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
            /*
                C'est ici qu'on définit la methode en PUT. A savoir : la methode restera POST, on définit
                une variable 'method'='PUT' qui est censé "overrider" la methode POST
            */
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('success', 'Pin successfully updated');
            return $this->redirectToRoute('app_home');

        }
        return $this->render('pin/edit.html.twig', [
            'pin' =>$pin,
            'form' => $form->createView()
            /*
                Lorsqu'on appelle une methode dans le render ( ici $form->createView()),
                on ne peut pas utiliser la fonction compact()
            */
            ]);
    }

    /*
        Il est indispensable de nommer la route differemment ( avec /delete )  lorsque la methode POST est utilisée :
        #[Route('/pins/{id<\d+>}/delete', name: 'app_pins_delete', methods: "POST")]
        Ici on utilise la methode DELETE, donc on peut garder le même chemin : traduction lorsqu'on appelle
        pins/{id[0-9]} avec la methode DELETE et le nom app_pins_delete, cette fonction sera appelée, lorsqu'on appelle
        pins/{id[0-9]} avec la methode GET et le nom app_pins_show, la fonction show sera appelée
    */
    #[Route('/pins/{id<\d+>}', name: 'app_pins_delete', methods: "DELETE")]
    public function delete(Request $request, Pin $pin,  EntityManagerInterface $em): Response
    {
        /*
            La methode delete à été appelée via un formulaire ( voir templates/pin/show.html.twig
            dd($request->request->all()); === var_dump($_POST);
            On peut faire dd($request->request->all()); pour voir le token généré
        */
        if($this->isCsrfTokenValid('pin' . $pin->getId(), $request->request->get('csrf_token')))
        /*
            On a utiliser ce code : csrf_token('pin' ~ pin.id), pour générer le token
         */
        {
            $em->remove($pin);
            $em->flush();
            $this->addFlash('info', 'Pin successfully deleted');
        }
        return $this->redirectToRoute('app_home');
    }
}

/*
    VOICI LA FONCTION CREATE SANS APPEL A LA CLASSE FORMULAIRE DE PIN : PINTYPE

    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;

    #[Route('/pins/create/avec/createFormBuilder', name: 'app_pins_create_avec_createFormBuider', methods: "GET|POST")]
    public function createOld(Request $request, EntityManagerInterface $em): Response
    {
        $pin = new Pin;
        $form = $this->createFormBuilder($pin)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->getForm()
        ;
            //     Avec le FormBuilder on peut setter la methode et l'action comme cela :
            //        $this->createFormBuilder()  -> setMethod('...')
            //                                    -> setAction('...')
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
                // Lorsqu'on passe notre objet à createFormBuilder, notre objet est dejà completé :
                // $em->persist($form->getData()); === $em->persist($pin);
            $em->persist($pin);
            $em->flush();
            return $this->redirectToRoute('app_home');
        }
        return $this->render('pin/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
*/
