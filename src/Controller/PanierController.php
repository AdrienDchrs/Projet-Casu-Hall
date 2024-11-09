<?php 

namespace App\Controller;

use App\Entity\{Article,Panier};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Cookie,Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository};

class PanierController extends AbstractController
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

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param EntityManagerInterface $manager
     * @param ArticleFavoriRepository $repositoryArticleFavori
     * @param PanierRepository $repositoryPanier
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;
        $this->repositoryPanier = $repositoryPanier;

        $this->manager = $manager;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories = $this->repositoryCategorie->findAll();
    }


    /**
     * Ce controleur va permettre d'afficher la page panier en fonction de l'utilisateur. 
     * @param Request $request
     * @return Response
     */
    #[Route('/panier', 'home.panier', methods:['GET', 'POST'])]
    public function GetPanier(Request $request): Response
    {
        $user = $this->getUser();
        
        $quantityArt = 0;
        
        if($user)
        {    
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser()]);

            foreach($this->panier as $panier)
            {
                $quantityArt += $panier->getQuantite();
            }
        }
        else 
        {
            $this->emptyUserFavoris = $this->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);
        } 

        
        return $this->render('panier.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 'user' => $this->getUser(), 
                                                  'panier' => $this->panier, 'articlesFavoris' => $this->articlesFavoris, 'emptyUserFavoris' => $this->emptyUserFavoris,
                                                  'emptyUserPanier' => $this->emptyUserPanier, 'quantityArt' => $quantityArt]);
    }

    /**
     * L'objectif de ce paramètre chemin va nous permettre de savoir ou nous rediriger en cas de panier déjà inclus
     * On peut ajouter au panier à différents endroits du site mais il faut savoir à quelle page ramener l'utilisateur. 
     */
    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/panier/ajouter/{id}', 'article.ajouterPanier', methods:['GET', 'POST'])]
    public function AddArticleToPanier(Article $article, Request $request):Response
    {
        $user = $this->getUser();
        $actualPath = $request->headers->get('referer');

        if($user)
        {
            $isAdd = $this->repositoryPanier->findBy(['article' => $article->getId(), 'utilisateur' => $user]);
            if($isAdd)
            {
                $this->addFlash('warning', 'L\'article est déjà dans votre panier !');

                if (strpos($actualPath, 'articles/detail') !== false)
                    $response = $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
                else if (strpos($actualPath, 'articlesFavoris') !== false)
                    $response = $this->redirectToRoute('articles-favoris', ['id' => $article->getId()]);
            }
            else
            {
                $panier = new Panier();
                $panier->setPrix($article->getPrix());
                $panier->setQuantite(1);
                $panier->setIsDone(0);
                $panier->setUtilisateur($user);
                $panier->setArticle($article);
        
                $this->manager->persist($panier);
                $this->manager->flush();
        
                $this->addFlash('success', 'L\'article a bien été ajouté dans votre panier.');
            }
        }
        else
        {
            $arrPanier = json_decode($request->cookies->get('panier', '[]'), true);

            if(!in_array($article->getId(), $arrPanier))
            {
                $arrPanier[] = $article->getId();

                $this->addFlash('success', 'L\'article a bien été ajouté à votre panier !');

                if (strpos($actualPath, 'articles/detail') !== false)
                    $response = $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
                else if (strpos($actualPath, 'articlesFavoris') !== false)
                    $response = $this->redirectToRoute('articles-favoris', ['id' => $article->getId()]);

                $response->headers->setCookie(new Cookie('panier', json_encode($arrPanier), time() + 3600, '/'));
                
                return $response;
            }
            else
            {
                $this->addFlash('warning', 'L\'article est déjà dans votre panier!');
            }
        }
        
        $actualPath = $request->headers->get('referer'); 

        if (strpos($actualPath, 'articles/detail') !== false)
            return $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
        else if (strpos($actualPath, 'articlesFavoris') !== false)
            return $this->redirectToRoute('articles-favoris', ['id' => $article->getId()]);
    }

    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/panier/decrement/{id}', 'panier.decrement', methods:['GET', 'POST'])]
    public function decrementQuantityArticle(Article $article, Request $request)
    {
        $user = $this->getUser();
        if($user)
        {
            $existPanier = $this->repositoryPanier->findOneBy(['utilisateur' => $user,'article' => $article]);
            if($existPanier)
            {
                if($existPanier->getQuantite() > 1)
                {
                    $prix = $existPanier->getPrix() - $existPanier->getArticle()->getPrix();
                    $quantite = $existPanier->getQuantite() - 1;

                    $existPanier->setPrix($prix);
                    $existPanier->setQuantite($quantite);
                        
                    $this->manager->persist($existPanier);
                    $this->manager->flush();
                }
            }
            else 
            {
                $this->addFlash("error", "L'article n'est pas dans le panier pour le moment. Veuillez réessayer.");
            }
        }
        return $this->redirectToRoute('home.panier');
    }

    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/panier/increment/{id}', 'panier.increment', methods:['GET', 'POST'])]
    public function incrementQuantityArticle(Article $article, Request $request)
    {
        $user = $this->getUser();

        if($user)
        {
            $existPanier = $this->repositoryPanier->findOneBy(['utilisateur' => $user, 'article' => $article]);
            if($existPanier)
            { 
                if($existPanier->getQuantite() < $existPanier->getArticle()->getQuantiteStock())
                {
                    $prix = $existPanier->getPrix() + $existPanier->getArticle()->getPrix();
                    $quantite = $existPanier->getQuantite() + 1;
                    $existPanier->setQuantite($quantite);
                    $existPanier->setPrix($prix);

                    $this->manager->persist($existPanier);
                    $this->manager->flush();
                }
            }
            else 
            {
                $this->addFlash("error", "L'article n'est pas dans le panier pour le moment. Veuillez réessayer.");
            }
        }
        return $this->redirectToRoute('home.panier');
    }

    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/panier/supprimer/{id}', 'article.supprimerArticlePanier', methods:['GET', 'POST'])]
    public function DeleteArticleToPanier(Request $request, Article $article):Response
    {
        $user = $this->getUser();

        if($user)
        {
            $this->panier = $this->repositoryPanier->findBy(['article' => $article->getId(), 'utilisateur' => $user]);
            
            if(!$this->panier)
            {
                $this->addFlash('error', 'L\'article n\'est pas dans votre panier !');
                return $this->redirectToRoute('home.panier', ['id' => $article->getId()]);
            }
            else
            {    
                $this->addFlash('success', 'L\'article a bien été supprimé de votre panier.');
                $this->manager->remove($this->panier[0]);
                $this->manager->flush();
                
                return $this->redirectToRoute('home.panier', ['id' => $article->getId()]);
            }
        }
        else 
        {
            $panier = json_decode($request->cookies->get('panier', '[]'), true);

            if(($key = array_search($article->getId(), $panier)) !== null)
            {
                unset($panier[$key]);

                $response = $this->redirectToRoute('home.panier');
                $response->headers->setCookie(new Cookie('panier', json_encode(array_values($panier)), time() + 3600, '/'));

                $this->addFlash('success', 'L\'article a bien été supprimé du panier.');

                return $response;
            }
            else 
            {
                $this->addFlash('error', 'L\'article n\'est pas dans votre panier.');
            }
        }
        return $this->redirectToRoute('home.panier');
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

?>