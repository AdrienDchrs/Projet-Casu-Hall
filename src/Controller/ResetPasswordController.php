<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Component\Mime\Address;
use App\Form\ChangePasswordFormType;
use App\Repository\MarqueRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private UtilisateurRepository $repositoryUtilisateur;


    private array $marques; 
    private array $categories;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param UtilisateurRepository $repositoryUtilisateur
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie,UtilisateurRepository $repositoryUtilisateur, private ResetPasswordHelperInterface $resetPasswordHelper,private EntityManagerInterface $entityManager)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;
        $this->repositoryUtilisateur    = $repositoryUtilisateur;

        $this->marques = $repositoryMarque->findAll();
        $this->categories = $repositoryCategorie->findAll();
    }

    /**
     * Contrôleur permettant d'afficher le formulaire de demande de réinitialisation de mot de passe. (email demandé seulement)
     * @param Request $request
     * @param MailerInterface $mailer
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'marques' =>  $this->marques,
            'categories' =>  $this->categories,
            'requestForm' => $form->createView()
        ]);
    }

    /**
     * Ce contrôleur sert à vérifier le mail afin d'envoyer une demande de réinitialisation de mot de passe.
     * Génère un faux token si aucun token n'est trouvé dans la session. Ceci empêche les utilisateurs de voir s'ils ont un compte en fonction du temps de réponse de la page.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        
        if (null === ($resetToken = $this->getTokenObjectFromSession())) 
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        
        return $this->render('reset_password/check_email.html.twig', ['marques' =>  $this->marques, 'categories' =>  $this->categories,
                                                                      'resetToken' => $resetToken]);
    }

    /**
     * Valide et traite le jeton de réinitialisation de mot de passe, s'il est valide.
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param TranslatorInterface $translator
     * @param string|null $token
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {
        if($token) 
        {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
        }

        $token = $this->getTokenFromSession();

        if (null === $token) 
        {
            throw $this->createNotFoundException('Aucun jeton de mot de passe de réinitialisation trouvé dans l\'URL ou dans la session.');
        }

        try 
        {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } 
        catch (ResetPasswordExceptionInterface $e) 
        {
            $this->addFlash('reset_password_error', sprintf('%s - %s',$translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $this->resetPasswordHelper->removeResetRequest($token);

            $user->setPassword($passwordHasher->hashPassword($user,$form->get('plainPassword')->getData()));

            $this->entityManager->persist($user); 
            $this->entityManager->flush();

            $this->addflash("successPasswordReset", "Votre mot de passe a été réinitialisé avec succès !");

            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('security.connexion', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
        }

        return $this->render('reset_password/reset.html.twig', ['resetForm' => $form->createView(), 'marques' =>  $this->marques,
                                                                'categories' =>  $this->categories, 'user' => $user,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $emailFormData]);

        // Do not reveal whether a user account was found or not.
        if (!$user) 
        {
            $this->addFlash('reset_password_error', 'Aucun compte n\'a été trouvé pour cette adresse email');

            return $this->redirectToRoute('app_forgot_password_request', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
        }

        try 
        {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } 
        catch (ResetPasswordExceptionInterface $e) 
        {
            $this->addFlash('reset_password_error', 'Erreur pendant la génération du token de réinitialisation de mot de passe');

            return $this->redirectToRoute('app_forgot_password_request', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
        }

        $email = (new TemplatedEmail())
            ->from(new Address('casuhall.contact@gmail.com', 'Casuhall Contact'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'userEmail' => $user->getEmail()
            ]);

        $mailer->send($email);

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
    }
}
