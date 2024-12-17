<?php 

namespace App\Controller;

use App\Form\InscriptionType;
use App\Service\CookieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request,Response};
use App\Entity\{Panier,Article,Utilisateur,ArticleFavori};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\{MarqueRepository,CategorieRepository,UtilisateurRepository};

class SecurityController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;
    private UtilisateurRepository $repositoryUtilisateur;

    private array $marques; 
    private array $categories;

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier = [];

    private EntityManagerInterface $manager;

    private CookieService $cookieService;

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param UtilisateurRepository $repositoryUtilisaeur
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie,UtilisateurRepository $repositoryUtilisateur, EntityManagerInterface $manager, CookieService $cookieService)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;
        $this->repositoryUtilisateur    = $repositoryUtilisateur;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();

        $this->manager = $manager;

        $this->cookieService = $cookieService;
    }

    /**
     * Contrôleur permettant d'afficher le formulaire de connexion
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/connexion', 'security.connexion', methods:['GET', 'POST'])]
    public function connexion(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier  = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        if ($this->getUser()) 
            return $this->redirectToRoute('post_connexion');
        
        return $this->render('security/connexion.html.twig', ['marques' => $this->marques,'categories' => $this->categories, 
                                                              'errors' => $authenticationUtils->getLastAuthenticationError(), 
                                                              'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    #[Route('/post-connexion', name: 'post_connexion', methods: ['GET', 'POST'])]
    public function PostConnexion(Request $request): Response
    {
        $user = $this->getUser();
        
        if($user) 
        {
            $cookie = json_decode($request->cookies->get('favoris', '[]'), true);
                
            if (!empty($cookie)) 
            {
                $this->emptyUserFavoris = $this->manager->getRepository(Article::class)->findBy(['id' => $cookie]);

                $arrayExist = $this->manager->getRepository(ArticleFavori::class)->findBy(['article' => $cookie, 'utilisateur' => $user]);

                if(empty($arrayExist))
                {
                    foreach ($this->emptyUserFavoris as $article) 
                    {
                        $object = new ArticleFavori();
                        $object->setUtilisateur($this->getUser());
                        $object->setArticle($article);
                
                        $this->manager->persist($object);
                    }
                    $this->manager->flush(); 
                }
                
                $response = $this->redirectToRoute('post_connexion');
                $response->headers->clearCookie('favoris', '/', null);
                    
                return $response;
            }

            $cookie = json_decode($request->cookies->get('panier', '[]'), true);
                
            if (!empty($cookie)) 
            {
                $this->emptyUserPanier = $this->manager->getRepository(Article::class)->findBy(['id' => $cookie]);

                $arrayExist = $this->manager->getRepository(Panier::class)->findBy(['article' => $cookie, 'utilisateur' => $user, 'isDone' => false]);

                if(empty($arrayExist))
                {

                    foreach ($this->emptyUserPanier as $article) 
                    {
                        $panier = new Panier();
                        $panier->setPrix($article->getPrix());
                        $panier->setQuantite(1);
                        $panier->setIsDone(0);
                        $panier->setUtilisateur($user);
                        $panier->setArticle($article);

                        $randomIdCommande = $this->generateIdentifier(10);
                        $panier->setIdCommande($randomIdCommande);

                        $this->manager->persist($panier);
                    }
                    $this->manager->flush(); 
                }
               
                $response = $this->redirectToRoute('home.index');
                $response->headers->clearCookie('panier', '/', null);
                return $response;
            }
        }          
        return $this->redirectToRoute('home.index');
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
     * @return Response
     */
    #[Route('/inscription', 'security.inscription', methods:['GET','POST'])]
    public function Inscription(Request $request): Response
    {
        $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
        $this->emptyUserPanier  = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);

        $user = new Utilisateur(); 
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);

        $user->setRoles(['ROLE_USER']);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            
            $telephoneFormat = $user->getTelephone();
            if($telephoneFormat != null)
            {
                $telephoneFormat = preg_replace('/[^0-9]/', '', $telephoneFormat);
                $user->setTelephone('+' . substr($telephoneFormat, 0, 2) . ' ' . substr($telephoneFormat, 2, 1) . ' ' . substr($telephoneFormat, 3, 2) . ' ' . substr($telephoneFormat, 5, 2) . ' ' . substr($telephoneFormat, 7, 2) . ' ' . substr($telephoneFormat, 9, 2));            
            }

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', 'Inscription avec succès !');

            return $this->redirectToRoute('security.connexion', ['marques' =>  $this->marques, 'categories' =>  $this->categories, 'emptyUserFavoris' => $this->emptyUserFavoris,
                                                                 'emptyUserPanier' => $this->emptyUserPanier]);
        }
        else if($form->isSubmitted() && !$form->isValid()) 
        {
            // $errors = $form->getErrors(true, false);
            // foreach ($errors as $error) {
            //     dump(
            //         'Champ : ' . $error->getOrigin()->getName(),
            //         'Erreur : ' . $error->getMessage()
            //     );
            // }
            // dd($form->getData());

            $mail = $form->getData()->getEmail();
            
            $adresseExistante = $this->repositoryUtilisateur->findBy(['email' => $mail]);

            if($adresseExistante)
                $this->addFlash('error', 'Vous avez déjà un compte !');
            else
                $this->addFlash('error', 'Erreur lors de l\'inscription ! Veuillez réessayer.');
        }

        return $this->render('security/inscription.html.twig', ['marques' =>  $this->marques, 'categories' =>  $this->categories, 'form' => $form->createView(), 
                                                                'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /** 
     * Fonction privée qui permet de générer un identifiant de commande de façon aléatoire
     * @param $length déterminant la taille de l'identifiant
     */
    private function generateIdentifier($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for($i = 0; $i < $length; $i++)
            $randomString .= $characters[random_int(0,$charactersLength - 1)];

        return $randomString;
    }
}

?> 