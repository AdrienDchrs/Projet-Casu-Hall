<?php

namespace App\Controller;

use App\Entity\{Article, Utilisateur};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Cookie,Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository};

class ArticleFavoriController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private PanierRepository $repositoryPanier;
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;

    private array $marques; 
    private array $categories;
    private array $articlesFavoris;
    private array $panier;

    private array $emptyUserFavoris = [];
    private array $emptyUserPanier = [];

    private EntityManagerInterface $manager;

    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;

        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);
        $this->categories  = $this->repositoryCategorie->findAll();

        $this->manager = $manager;
    }

    #[Route('/articlesFavoris', name:'articles-favoris', methods:['GET', 'POST'])]
    public function GetArticlesFavoris(Request $request): Response
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

        return $this->render('articles/articles-favoris.html.twig', ['marques' => $this->marques,'categories' => $this->categories,
                                                                     'user' => $this->getUser(),'articlesFavoris' => $this->articlesFavoris, 
                                                                     'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                                     'emptyUserPanier' => $this->emptyUserPanier]);
    }

     /**
     * Permet de supprimer un article en favori. On passe par l'identifiant de l'article en paramètre de l'url
     * @param Article $article 
     * @param Utilisateur $utilisateur
     * @return Response
     */
    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/articles/supprimerFavori/{id}', 'article.supprimerFavori', methods:['GET', 'POST'])]
    public function DeleteArticleFavori(Request $request, Article $article): Response
    {
        $user = $this->getUser();
        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['article' => $article->getId(), 'utilisateur' => $user]); 
        
            if(!$this->articlesFavoris)
            {
                $this->addFlash('error', 'L\'article n\'est pas dans vos favoris !');
                return $this->redirectToRoute('articles-favoris', ['id' => $article->getId()]);
            }
            else
            {    
                $this->addFlash('success', 'L\'article a bien été supprimé de vos favoris !');
                $this->manager->remove($this->articlesFavoris[0]);
                $this->manager->flush();
            }
        }
        else
        {
            $favoris = json_decode($request->cookies->get('favoris', '[]'), true);
            
            if (($key = array_search($article->getId(), $favoris)) !== false) 
            {
                // On va détruire la partie du cookie et le recréer en fonction de ce qu'il nous reste. 
                unset($favoris[$key]);

                $response = $this->redirectToRoute('articles-favoris');
                $response->headers->setCookie(new Cookie('favoris', json_encode(array_values($favoris)), time() + 3600, '/'));

                $this->addFlash('success', 'L\'article a bien été supprimé de vos favoris.');
                
                return $response;
            } 
            else 
            {
                $this->addFlash('error', 'L\'article n\'est pas dans vos favoris.');
            }
        }
        return $this->redirectToRoute('articles-favoris', ['id' => $article->getId()]);
    }

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