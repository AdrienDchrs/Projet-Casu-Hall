<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\CookieService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleFavoriRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository};

class ContactController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;
    private PanierRepository $repositoryPanier;

    private EntityManagerInterface $manager;

    private array $marques; 
    private array $categories;
    private array $articlesFavoris = [];
    private array $panier = [];

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier = [];

    private CookieService $cookieService;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param RepositoryArticlesFavoris $repositoryArticlesFavoris
     * @param EntityManagerInterface $manager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier,CookieService $cookieService)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;
        $this->repositoryArticleFavori  = $repositoryArticleFavori;
        $this->repositoryPanier         = $repositoryPanier;

        $this->manager = $manager;
        
        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();

        $this->cookieService = $cookieService;
    }

    /**
     * Formulaire de contact qui donne la possibilité d'envoyer un mail à l'adresse professionnelle du projet, le tout sur MailTrap. 
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/contact', 'home.contact', methods:['GET', 'POST'])]
    public function SupportContact(Request $request, MailerInterface $mailer): Response
    {
        $user = $this->getUser();

        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);
        }
        else 
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier  = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);
        }

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $contact = $form->getData();

            $this->manager->persist($contact);
            $this->manager->flush();

            $email = (new TemplatedEmail())
            ->from($contact->getEmailContact())  
            ->to('casuhall.contact@gmail.com')
            ->subject($contact->getObjet())
            ->htmlTemplate('emailContact/email.html.twig')
            ->context(['contact' => $contact]);

            $mailer->send($email);  

            $this->addFlash('success', 'Votre demande a été envoyé avec succès !');

            return $this->redirectToRoute('home.contact');
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('success', 'Une erreur est survenue lors de l\'envoi du mail !');
        }

        return $this->render('contacts/contact.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                                'user' => $this->getUser(),   'form' => $form->createView(),
                                                                'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 
                                                                'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }
}
