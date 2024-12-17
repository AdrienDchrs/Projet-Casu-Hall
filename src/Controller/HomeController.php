<?php 

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository};
use App\Service\CookieService;

class HomeController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private PanierRepository $repositoryPanier;
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;

    private array $marques; 
    private array $categories;
    private array $panier = [];
    private array $articlesFavoris = [];

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier  = [];

    private CookieService $cookieService;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param RepositoryArticlesFavoris $repositoryArticlesFavoris
     * @param EntityManagerInterface $manager
     * @param ArticleRepository $repositoryArticle
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier, CookieService $cookieService)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();

        $this->cookieService = $cookieService;
    }

    /**
     * Controleur de la page d'accueil
     * @return Response
     */
    #[Route('/', 'home.index', methods: ['GET'])]
    public function Index(Request $request): Response
    {
        $user = $this->getUser();
        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);
        }
        else 
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }
    
        
        return $this->render('home.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                'user' => $user,  'articlesFavoris' => $this->articlesFavoris,
                                                'emptyUserFavoris' => $this->emptyUserFavoris, 'panier' => $this->panier, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /**
     * Ce contrôleur permet de visualiser la page qui explique l'histoire du style casual et de la boutique
     * @return Response
     */
    #[Route('/notre-histoire', 'home.notre-histoire', methods:['GET'])]
    public function OurHistory(Request $request): Response
    {
        $user = $this->getUser();
        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);
        }
        else
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }

        return $this->render('notre-histoire.html.twig', ['marques' => $this->marques, 'categories' => $this->categories,
                                                          'user' => $this->getUser(), 'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 
                                                          'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /**
     * Ce contrôleur nous sert à changer de langue
     */
    #[Route('/change-locale/{locale}', 'home.change_locale', methods:['GET', 'POST'])]
    public function ChangeLocale(Request $request, $locale): Response
    {
        $user = $this->getUser();
        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);
        }
        else
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }

        $request->getSession()->set('_locale',$locale);
        
        return $this->redirect($request->headers->get('referer'));
    }
}
?> 