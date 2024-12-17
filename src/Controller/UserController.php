<?php 

namespace App\Controller;

use App\Form\CompteType;
use App\Entity\Utilisateur;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\{PanierRepository,CategorieRepository,ArticleFavoriRepository,MarqueRepository, UtilisateurRepository};

class UserController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticlesFavoris;
    private PanierRepository $repositoryPanier;
    private UtilisateurRepository $repositoryUser;

    private array $marques; 
    private array $categories;
    private array $articlesFavoris;
    private array $panier;

    private EntityManagerInterface $manager;

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param ArticleFavoriRepository $repositoryArticlesFavoris
     * @param PanierRepository $repositoryPanier 
     * @param UtilisateurRepository $repositoryUser
     * @param EntityManagerInterface $manager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, ArticleFavoriRepository $repositoryArticlesFavoris, PanierRepository $repositoryPanier, UtilisateurRepository $repositoryUser, EntityManagerInterface $manager)
    {
        $this->repositoryMarque          = $repositoryMarque;
        $this->repositoryCategorie       = $repositoryCategorie;
        $this->repositoryArticlesFavoris = $repositoryArticlesFavoris;
        $this->repositoryPanier          = $repositoryPanier;
        $this->repositoryUser            = $repositoryUser;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();

        $this->manager = $manager;
    }

    /**
     * Contrôleur permettant d'afficher les trois rubriques de paramétrages pour l'utilisateur courant
     * @param Utilisateur $user
     * @return Response
     */
    #[ParamConverter('user', class: 'App\Entity\Utilisateur')]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    #[Route('/mon_espace/{id}', name:'edition.compte', methods:['GET'])]
    public function PersonalSpace(Utilisateur $user): Response
    {
        $this->articlesFavoris = $this->repositoryArticlesFavoris->findBy(['utilisateur' => $this->getUser()]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);

        if(!$this->getUser())
            $this->redirectToRoute('security.connexion');

        if($this->getUser() !== $user)
            $this->redirectToRoute('edition.compte', ['id' => $user->getId()]);
    
        return $this->render('security/compte.html.twig', ['marques' => $this->marques,'categories' => $this->categories, 'user' => $user, 
                                                           'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier]);
    } 

    /**
     * Ce contrôleur va permettre à l'utilisateur de modifier ses informations générales comme son adresse email, sa date d'anniversaire, son numéro de téléphone.
     * @param $user
     * @param $request
     * @return Response
     */
    #[ParamConverter('user', class: 'App\Entity\Utilisateur')]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    #[Route('/mes_informations_personnelles/{id}', name:'edition.informations.personnelles', methods:['GET', 'POST'])]
    public function ModifyGeneralInformations(Utilisateur $user, Request $request): Response
    {
        $this->articlesFavoris = $this->repositoryArticlesFavoris->findBy(['utilisateur' => $user]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);

        $form = $this->createForm(CompteType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {   
            $mail = $user->getEmail();
            $adresseExistante = $this->repositoryUser->findBy(['email' => $mail]);

            if($adresseExistante)
            {
                $this->addFlash('error', 'Un compte existe déjà avec cette adresse e-mail !');
                return $this->redirectToRoute('edition.informations.personnelles', ['id' => $user->getId()]); 
            }
            else
            {
                $telephoneFormat = $user->getTelephone();
                $telephoneFormat = preg_replace('/[^0-9]/', '', $telephoneFormat);
                $user->setTelephone('+' . substr($telephoneFormat, 0, 2) . ' ' . substr($telephoneFormat, 2, 1) . ' ' . substr($telephoneFormat, 3, 2) . ' ' . substr($telephoneFormat, 5, 2) . ' ' . substr($telephoneFormat, 7, 2) . ' ' . substr($telephoneFormat, 9, 2));

                $this->manager->persist($user); 
                $this->manager->flush();

                $this->addFlash('success', 'Vos informations ont bien été modifiées.');

                return $this->redirectToRoute('edition.informations.personnelles', ['id' => $user->getId()]); 
            }
        }
        else if ($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Veuillez vérifier les informations saisies.');
        }
    
        return $this->render('security/compte-information.html.twig', ['marques' => $this->marques,'categories' => $this->categories,
                                                           'user' => $user,'form' => $form->createView(), 'articlesFavoris' => $this->articlesFavoris,
                                                           'panier' => $this->panier]);
    } 

    /**
     * Ce contrôleur va permettre à l'utilisateur de modifier son mot de passe
     * @param $user
     * @param $request
     * @param $hasher
     * @return Response
     */
    #[ParamConverter('user', class: 'App\Entity\Utilisateur')]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    #[Route('/change_password/{id}', name:'edition.change_password', methods:['GET', 'POST'])]
    public function ModifyPassword(Utilisateur $user, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $this->articlesFavoris = $this->repositoryArticlesFavoris->findBy(['utilisateur' => $user]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);

        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $this->manager->persist($user); 
            $this->manager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
        }
        else if ($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Veuillez vérifier que les mots de passe soient renforcés et identiques. ');
        }
    
        return $this->render('security/compte-password.html.twig', ['marques' => $this->marques,'categories' => $this->categories,
                                                           'user' => $user,'form' => $form->createView(), 'articlesFavoris' => $this->articlesFavoris,
                                                           'panier' => $this->panier]);
    } 

    /**
     * Ce contrôleur va permettre à l'utilisateur de récupérer ses commandes effectuées. 
     * On récupère deux états du panier, un pour le panier actuel et l'icône sur la navbar, un autre pour les commandes effectuées à afficher sur la page.
     * @param $user
     * @return Response
     */
    #[ParamConverter('user', class: 'App\Entity\Utilisateur')]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    #[Route('/consult_commands/{id}', name:'edition.consultCommands', methods:['GET'])]
    public function ConsultCommands(Utilisateur $user): Response
    {
        $this->articlesFavoris = $this->repositoryArticlesFavoris->findBy(['utilisateur' => $user]);
        
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);
        
        $panierDoneCommands = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => true]);
    
    
        return $this->render('security/compte-commande.html.twig', ['marques' => $this->marques, 'categories' => $this->categories,'user' => $user, 
                                                                    'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 'panierDoneCommands' => $panierDoneCommands]);
    } 
}