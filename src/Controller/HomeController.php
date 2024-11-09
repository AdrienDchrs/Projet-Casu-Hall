<?php 

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository};

class HomeController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private PanierRepository $repositoryPanier;
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;

    private EntityManagerInterface $manager;

    private array $marques; 
    private array $categories;
    private array $articlesFavoris;
    private array $articles;
    private array $panier;

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier  = [];

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param RepositoryArticlesFavoris $repositoryArticlesFavoris
     * @param EntityManagerInterface $manager
     * @param ArticleRepository $repositoryArticle
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;

        $this->manager = $manager;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();
    }

    /**
     * Controleur de la page d'accueil
     * @return Response
     */
    #[Route('/', 'home.index', methods: ['GET'])]
    public function Index(Request $request): Response
    {
        $user = $this->getUser();
        
        $this->articlesFavoris = [];
        $this->panier = [];
    
        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user]); 
        }
        else 
        {
            $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }
    
        
        return $this->render('home.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                'user' => $user,  'articlesFavoris' => $this->articlesFavoris,
                                                'emptyUserFavoris' => $this->emptyUserFavoris, 'panier' => $this->panier, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /**
     * Ce contrôleur permet de visualiser la page qui explique l'histoire du style casual et de la boutique
     * @return Response
     */
    #[Route('/notre-histoire', 'home.notre-histoire', methods:['GET', 'POST'])]
    public function OurHistory(Request $request): Response
    {
        $user = $this->getUser();
        $this->articlesFavoris = [];
        $this->panier = [];

        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user]);
        }
        else
        {
            $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }

        return $this->render('notre-histoire.html.twig', ['marques' => $this->marques, 'categories' => $this->categories,
                                                          'user' => $this->getUser(), 'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 
                                                          'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

     /** 
     * Fonction privée qui permet de récupérer les favoris d'un utilisateur non connecté
     * @param Request sert à établir une requête HTTP pour récupérer les cookies de la session.
     * @param cookieName adapte la récupération de cookie en fonction du nom donné à la création
     * @param array pour inclure tous les cookie dans not
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
?> 