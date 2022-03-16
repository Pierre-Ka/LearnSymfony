<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
/* use Symfony\Component\Mime\Email;*/

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /*
        Le formulaire ici comporte l'email et deux champs non mappés : plainPassword et agreeTerms
        Si on voulait recuperer un de ces deux champs :
        dd($form->get('plainPassword')->getData()); ou dd(form['plainPassword']->getData());
        Pour hasher le password on utilise UserPasswordHasherInterface->hashPassword(). Cette methode accepte comme
        premier agrument le $user afin de determiner quel encoder utiliser. ( ici : encoder : App\Entity\User : auto dans security.yaml ).
        On pourrait définir un champ 'password' dans notre formulaire et le mapper lorsqu'on passe le formulaire ce
        champ prendra le password en clair mais avant le flush() on pourrait le rehasher. Cela marcherait.
        On préfère simplement ici passer un champ non mappé plainPassword pour qu'il n'y ait pas d'ambiguité.

        Si le formulaire est rendu tel quel on a une erreur : il existe des champs obligatoire en bdd (firstName,
        lastName ) qui n'ont pas été setter donc $em->flush() ne peut pas fonctionner. Il faut créer la possibilité
        de les rajouter.
    */
    #[Route('/register', name: 'app_register')]
    public function register(/*MailerInterface $mailer,*/ Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator,
                             EntityManagerInterface $entityManager): Response
    {
        // Importation de la classe Email et injection de MailerInterface $mailer
        // ESSAI DE MAIL NOT WORKING
//        $email = (new Email())
//            ->from('ikanhiumalam@gmail.com')
//            ->to('ikanhiu@outlook.fr')
//            ->subject('Time for Symfony Mailer!')
//            ->text('Sending emails is fun again!')
//            ->html('<p>See Twig integration for better HTML integration!</p>');
//        $mailer->send($email);

        /*
            Si l'utilisateur est connecté
            et tente d'acceder à la page de connexion alors il est redirigé à la page d'accueil
        */
        if ($this->getUser()) {
            $this->addFlash('error', 'Already logged in'); /* Ajout */
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    /*
                        VARIABLES D'ENV ET PARAMETRE DU CONTAINER
                        ->from(new Address('noreply@panterest.com', 'panterest'))
                        Ici on definit des variables d'environnement dans .env :
                        MAIL_FROM_ADRESS=noreply@panterest.com
M                       MAIL_FROM_NAME="panterest"
                        puis on peut les appelées comme cela :
                        ->from(new Address( $_ENV['MAIL_FROM_ADRESS'], $_ENV['MAIL_FROM_NAME']))
                        Simplement ces variables ne changent pas en fonction de l'environnement ( prod ou dev )
                        donc pour être correct il faut définir des paramètres du container dans services.yaml
                    */
                    ->from(new Address( $this->getParameter('app.mail_from_adress'),
                                        $this->getParameter('app.mail_from_name')
                                        ))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    /* On change l'adresse vu que l'on a crée un dossier template/emails/registration/ */
                    ->htmlTemplate('emails/registration/confirmation.html.twig')
            );
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_home');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
