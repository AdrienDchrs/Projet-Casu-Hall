<?php 

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\InscriptionType;
use App\Repository\MarqueRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private UtilisateurRepository $repositoryUtilisateur;


    private array $marques; 
    private array $categories;

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param UtilisateurRepository $repositoryUtilisateur
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie,UtilisateurRepository $repositoryUtilisateur)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;
        $this->repositoryUtilisateur    = $repositoryUtilisateur;

        $this->marques = $this->repositoryMarque->findAll();
        $this->categories = $this->repositoryCategorie->findAll();
    }

    /**
     * Contrôleur permettant d'afficher le formulaire de connexion
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/connexion', 'security.connexion', methods:['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {

        return $this->render('security/connexion.html.twig', ['marques' => $this->marques,
                                                     'categories' => $this->categories, 
                                                     'errors' => $authenticationUtils->getLastAuthenticationError()]);
    }

    /**
     * Contrôleur permettant de gérer la déconnexion
     * @return void
     */
    #[Route('/deconnexion', 'security.deconnexion', methods:['GET', 'POST'])]
    public function deconnexion()
    {
        // Rien à faire ici, c'est géré par le firewall
    }
    
    /**
     * Contrôleur permettant de gérer l'inscription. Une adaptation est faite pour plusieurs points.
     * On adapte le format du téléphone, à ré-adapter pour les numéros internationaux.
     * On affecte directement le role d'utilisateur
     * Il y a une gestion de mot de passe renforcée, grâce à un validator. 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/inscription', 'security.inscription', methods:['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $manager): Response
    {
        $user = new Utilisateur(); 
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            
            $telephoneFormat = $user->getTelephone();
            $telephoneFormat = preg_replace('/[^0-9]/', '', $telephoneFormat);

            $user->setTelephone('+' . substr($telephoneFormat, 0, 2) . ' ' . substr($telephoneFormat, 2, 1) . ' ' . substr($telephoneFormat, 3, 2) . ' ' . substr($telephoneFormat, 5, 2) . ' ' . substr($telephoneFormat, 7, 2) . ' ' . substr($telephoneFormat, 9, 2));            
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Inscription avec succès !');

            return $this->redirectToRoute('security.connexion', ['marques' =>  $this->marques, 'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid()) 
        {
            $mail = $form->getData()->getEmail();
            
            $adresseExistante = $this->repositoryUtilisateur->findBy(['email' => $mail]);

            if($adresseExistante)
            {
                $this->addFlash('erreur', 'Vous avez déjà un compte !');
            }
            else
            {
                $this->addFlash('erreur', 'Erreur lors de l\'inscription ! Veuillez réessayer.');
            }
        }

        return $this->render('security/inscription.html.twig', ['marques' =>  $this->marques, 'categories' =>  $this->categories, 'form' => $form->createView()]);
    }
}

?> 