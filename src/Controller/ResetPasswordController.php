<?php

namespace App\Controller;

use Symfony\Component\Mime\Address;
use App\Entity\{Article,Utilisateur};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\{ChangePasswordFormType,ResetPasswordRequestFormType};
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\{Request,Response,RedirectResponse};
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use App\Repository\{PanierRepository,MarqueRepository,CategorieRepository,UtilisateurRepository,ArticleFavoriRepository};

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private UtilisateurRepository $repositoryUtilisateur;
    private ArticleFavoriRepository $repositoryArticleFavori;
    private PanierRepository $repositoryPanier;


    private array $marques; 
    private array $categories;
    private array $articlesFavoris;
    private array $panier;

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier = [];

    private EntityManagerInterface $manager;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param UtilisateurRepository $repositoryUtilisateur
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     * @param RepositoryArticlesFavoris $repositoryArticlesFavoris
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie,UtilisateurRepository $repositoryUtilisateur, private ResetPasswordHelperInterface $resetPasswordHelper,private EntityManagerInterface $entityManager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier, EntityManagerInterface $manager)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;
        $this->repositoryUtilisateur    = $repositoryUtilisateur;
        $this->repositoryArticleFavori  = $repositoryArticleFavori;
        $this->repositoryPanier         = $repositoryPanier;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $repositoryCategorie->findAll();

        $this->manager = $manager;
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
        $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
            return $this->processSendingPasswordResetEmail($form->get('email')->getData(),$mailer,$translator, $request);

        return $this->render('reset_password/request.html.twig', ['marques' =>  $this->marques,'categories' =>  $this->categories,'requestForm' => $form->createView(), 
                                                                  'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /**
     * Ce contrôleur sert à vérifier le mail afin d'envoyer une demande de réinitialisation de mot de passe.
     * Génère un faux token si aucun token n'est trouvé dans la session. Ceci empêche les utilisateurs de voir s'ils ont un compte en fonction du temps de réponse de la page.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(Request $request): Response
    {   
        $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        if (null === ($resetToken = $this->getTokenObjectFromSession())) 
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        
        return $this->render('reset_password/check_email.html.twig', ['marques' =>  $this->marques, 'categories' =>  $this->categories,'resetToken' => $resetToken,
                                                                      'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
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
        $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        if($token) 
        {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password', ['marques' =>  $this->marques,'categories' =>  $this->categories, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                                 'emptyUserPanier' => $this->emptyUserPanier]);
        }

        $token = $this->getTokenFromSession();

        if (null === $token) throw $this->createNotFoundException('Aucun jeton de mot de passe de réinitialisation trouvé dans l\'URL ou dans la session.');

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

            $this->addflash("success", "Votre mot de passe a été réinitialisé avec succès !");

            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('security.connexion', ['marques' =>  $this->marques,'categories' =>  $this->categories,
                                                                 'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
        }

        return $this->render('reset_password/reset.html.twig', ['resetForm' => $form->createView(), 'marques' =>  $this->marques,
                                                                'categories' =>  $this->categories, 'user' => $user, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                                'emptyUserPanier' => $this->emptyUserPanier]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator, Request $request): RedirectResponse
    {
        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $emailFormData]);

        $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        // Do not reveal whether a user account was found or not.
        if (!$user) 
        {
            $this->addFlash('reset_password_error', 'Aucun compte n\'a été trouvé pour cette adresse email');

            return $this->redirectToRoute('app_forgot_password_request', ['marques' =>  $this->marques,'categories' =>  $this->categories, 
                                                                          'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUser' => $this->emptyUserPanier]);
        }

        try 
        {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } 
        catch (ResetPasswordExceptionInterface $e) 
        {
            $this->addFlash('reset_password_error', 'Erreur pendant la génération du token de réinitialisation de mot de passe');

            return $this->redirectToRoute('app_forgot_password_request', ['marques' =>  $this->marques,'categories' =>  $this->categories, 
                                                                          'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
        }

        $email = (new TemplatedEmail())
            ->from(new Address('casuhall.contact@gmail.com', 'Casuhall Contact'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context(['resetToken' => $resetToken,'userEmail' => $user->getEmail()]);

        $mailer->send($email);

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email', ['marques' =>  $this->marques,'categories' =>  $this->categories,'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                          'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /** 
     * Fonction privée qui permet de récupérer les favoris d'un utilisateur non connecté
     * @param Request $request sert à établir une requête HTTP pour récupérer les cookies de la session.
     */
    private function getCookieUserNotConnected(Request $request, $cookieName, $array)
    {

        $cookie = json_decode($request->cookies->get($cookieName, '[]'), true);
            
        if(!empty($cookie))
            $array = $this->manager->getRepository(Article::class)->findBy(['id' => $cookie]);
        else 
            $array = [];

        return $array; 
    }
}
