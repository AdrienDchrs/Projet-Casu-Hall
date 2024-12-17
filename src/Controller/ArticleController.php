<?php

namespace App\Controller;

use App\Model\SearchData;
use App\Service\CookieService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\{ModifierArticleType,SearchType};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Cookie,Request,Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\{Marque, Article, Categorie, Utilisateur, ArticleFavori};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\{Security,IsGranted};
use App\Repository\{MarqueRepository,PanierRepository,ArticleRepository,CategorieRepository, ArticleFavoriRepository};

class ArticleController extends AbstractController
{
    private PaginatorInterface $paginator;
    private PanierRepository $repositoryPanier;
    private MarqueRepository $repositoryMarque; 
    private ArticleRepository $repositoryArticle;
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;

    private array $marques = []; 
    private array $articles = [];
    private array $categories = [];
    private array $articlesFavoris = [];
    private array $panier = [];

    // Utilisé pour les utilisateurs non connectés.
    private array $emptyUserFavoris = [];
    private array $emptyUserPanier = [];

    private EntityManagerInterface $manager;

    private CookieService $cookieService;

    /**
     * Constructeur
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param ArticleRepository $repositoryArticle
     * @param ArticleFavoriRepository $repositoryArticleFavori
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $manager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, ArticleRepository $repositoryArticle, PaginatorInterface $paginator, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, PanierRepository $repositoryPanier, CookieService $cookieService)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryArticle = $repositoryArticle;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;

        $this->articles         = $this->repositoryArticle->findAll();
        $this->categories       = $this->repositoryCategorie->findAll();
        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);

        $this->paginator = $paginator;
        $this->manager = $manager;

        $this->cookieService = $cookieService;
    }

    /**
     * Affichage de tous les articles sans aucuns filtres
     * On passe par une pagination dans le cas où nous avons plus de 12 articles
     * @param Request $request
     * @return Response
     */
    #[Route('/articles', 'articles.all', methods:['GET'])]
    public function GetAllArticles(Request $request): Response
    {
        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);
        
        $user = $this->getUser();
        if($user)
        {    
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);
        }
        else 
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);
        }

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $searchData->page = $request->query->getInt('page',1);

            $articlesPaginer = $this->repositoryArticle->findBySearch($searchData);

            return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer, 'marques' => $this->marques, 'categories' => $this->categories, 'user' => $user, 
                'nomMarque' => 'Tous les vêtements disponibles', 'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 
                'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
        }

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer, 'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $user, 'nomMarque' => 'Tous les vêtements disponibles', 
                                                             'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 
                                                             'emptyUserFavoris' => $this->emptyUserFavoris, 'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
    }

    /**
     * On affiche les articles selon l'identifiant de la marque passée en paramètre de l'url.
     * On passe par une pagination dans le cas où nous avons plus de 12 articles
     * @param Request $request
     * @param Marque $marque
     * @return Response
     */
    #[Route('/articles/marque/{id}', 'articles.articleMarque', methods:['GET', 'POST'])]
    #[ParamConverter('Marque', class: 'App\Entity\Marque')]
    public function GetArticlesByMarque(Marque $marque, Request $request): Response
    {
        $user = $this->getUser();

        $this->articles = $this->repositoryArticle->findBy(['idMarque' => $marque->getIdMarque()]);
        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);

        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);
        }
        else 
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);
        }

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $searchData->page = $request->query->getInt('page',1);

            $articlesPaginer = $this->repositoryArticle->findBySearch($searchData,$marque->getIdMarque(),"marque");

            return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
            'categories' => $this->categories,'user' => $user, 'nomMarque' => 'Nos articles de la marque ' .$marque->getNomMarque(), 
            'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
            'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
        }

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $user, 'nomMarque' => 'Nos articles de la marque ' .$marque->getNomMarque(), 
                                                             'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                             'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
                                                             
    }

    /**
     * On affiche les articles selon l'identifiant de la catégorie passée en paramètre de l'url. 
     * On passe par une pagination dans le cas où nous avons plus de 12 articles de cette catégorie
     * @param Request $request
     * @param Categorie $categorie
     * @return Response
     */
    #[ParamConverter('Categorie', class: 'App\Entity\Categorie')]
    #[Route('/articles/categorie/{id}', 'articles.articleCategorie', methods:['GET', 'POST'])]
    public function GetArticlesByCategorie(Categorie $categorie, Request $request): Response
    {
        $user = $this->getUser();

        $titre =  'Nos ' .$categorie->getNomCategorie();

        $this->articles = $this->repositoryArticle->findBy(['idCategorie' => $categorie->getIdCategorie()]);
        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);   

        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);
        }
        else 
        {
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);
        }

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $searchData->page = $request->query->getInt('page',1);

            $articlesPaginer = $this->repositoryArticle->findBySearch($searchData,$categorie->getIdCategorie(),"categorie");

            return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $user, 'nomMarque' => $titre, 
                                                             'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                             'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
        }

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $user, 'nomMarque' => $titre, 
                                                             'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                             'emptyUserPanier' => $this->emptyUserPanier, 'form' => $form->createView()]);
    }

    /**
     * On affiche un détail de l'article au clic sur ce dernier, on y passe un identifiant d'article en paramètre de l'url
     * La méthode renvoyerTexte() permet de renvoyer un descriptif de l'état de l'article selon sa note
     * Par la suite, pourquoi pas mettre en base s'il y a d'autres textes à afficher selon la note
     * @param Article $article
     * @return Response
     */
    #[Route('/articles/detail/{id}', 'article.articleDetail', methods:['GET', 'POST'])]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    public function GetDetailArticles(Request $request, Article $article): Response
    {
        $user = $this->getUser();

        $textes = $this->ReturnText($article->getNote());

        if($user)
        {
            $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
            $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $user, "isDone" => false]);
        }
        else
        { 
            $this->emptyUserFavoris = $this->cookieService->getCookieUserNotConnected($request,'favoris',$this->emptyUserFavoris);
            $this->emptyUserPanier = $this->cookieService->getCookieUserNotConnected($request,'panier',$this->emptyUserPanier);  
        }

        $nomMarque = $this->repositoryArticle->find($article->getId())->getIdMarque()->getNomMarque();

        return $this->render('articles/article-detail.html.twig', ['articles' => $article,'marques' => $this->marques,
                                                                   'categories' => $this->categories,'user' => $user,
                                                                   'textes' => $textes, 'articlesFavoris' => $this->articlesFavoris, 
                                                                   'nomMarque' => $nomMarque, 'panier' => $this->panier, 'emptyUserFavoris' => $this->emptyUserFavoris, 
                                                                   'emptyUserPanier' => $this->emptyUserPanier]);
    }

    /**
     * Modification d'un article en passant son identifiant. On passe par un formulaire de modification
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Security('is_granted("ROLE_ADMIN")')]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    #[Route('/articles/modifier/{id}', 'article.modifier', methods:['GET', 'POST'])]
    public function ModifyArticle(Article $article, Request $request): Response
    {
        $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);

        $form = $this->createForm(ModifierArticleType::class, $article, ['marques' => $this->marques, 'categories' => $this->categories]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $articleModifie = $form->getData();

            $this->manager->persist($articleModifie);
            $this->manager->flush();
            
            $this->addFlash('success', 'L\'article a été mis à jour avec succès !');
            
            return $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Erreur(s) de saisie(s) lors de la mise à jour de l\'article ! Veuillez réessayer.');
        }

        return $this->render('articles/modifier-article.html.twig', ['articles' => $this->articles,'marques' => $this->marques,
                                                                     'categories' => $this->categories, 'form' => $form->createView(), 
                                                                     'user' => $this->getUser(), 'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier]); 
    }

    /**
     * Suppression d'un article en passant par son identifiant
     * @param Article $article
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Security('is_granted("ROLE_ADMIN")')]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    #[Route('/articles/supprimer/{id}', 'article.delete', methods:['GET', 'POST'])]
    public function DeleteArticle(Article $article): Response
    {
        if(!$article)
        {
            $this->addFlash('error', 'L\'article n\'existe pas !');
            return $this->redirectToRoute('articles.all');
        }

        $this->articles = $this->repositoryArticle->findBy(['id' => $article->getId()]);

        $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['article' => $article->getId()]);

        if($this->articlesFavoris > 0)
        {
            for($i = 0; $i < count($this->articlesFavoris); $i++)
            {
                $this->manager->remove($this->articlesFavoris[$i]);
            }
        }

        $this->manager->remove($this->articles[0]); 
        $this->manager->flush();

        $this->addFlash('success', 'L\'article a bien été supprimé !');

        return $this->redirectToRoute('articles.all', ['articles' => $this->articles]);
    }

     /**
     * Permet d'ajouter un article en favori. On passe par l'identifiant de l'article en paramètre de l'url
     * @param Article $article 
     * @param Utilisateur $utilisateur
     * @return Response
     */
    #[ParamConverter('article', class: 'App\Entity\Article')]
    #[Route('/articles/favori/{id}', 'article.favori', methods:['GET', 'POST'])]
    public function AddFavori(Article $article, Request $request): Response
    {
        $user = $this->getUser();

        // Je récupère un url de type : http://127.0.0.1:8000/panier/ ou http://127.0.0.1:8000/articles/detail
        $chemin = $request->headers->get('referer'); 

        if($user)
        {
            $bEstFavori = $this->repositoryArticleFavori->findBy(['article' => $article->getId(), 'utilisateur' => $user]);

            if($bEstFavori)
            {
                $this->addFlash('warning', 'L\'article est déjà dans vos favoris !');
                
                if(strpos($chemin, 'panier'))
                    $response = $this->redirectToRoute('home.panier', ['id' => $article->getId()]);
                else if(strpos($chemin, 'detail'))
                    $response = $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
            }
            else
            {
                $articleFavori = new ArticleFavori();
                $articleFavori->setUtilisateur($this->getUser());
                $articleFavori->setArticle($article);
        
                $this->manager->persist($articleFavori);
                $this->manager->flush();
        
                $this->addFlash('success', 'L\'article a bien été ajouté à vos favoris !');

                $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $user]);
                $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);            
            }
        }
        else 
        {
            $arrFavoris = json_decode($request->cookies->get('favoris', '[]'), true);

            if(!in_array($article->getId(), $arrFavoris))
            {
                $arrFavoris[] = $article->getId();

                $this->addFlash('success', 'L\'article a bien été ajouté à vos favoris !');

                $chemin = $request->headers->get('referer'); 

                if(strpos($chemin, 'panier'))
                    $response = $this->redirectToRoute('home.panier', ['id' => $article->getId()]);
                else if(strpos($chemin, 'detail'))
                    $response = $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);

                $response->headers->setCookie(new Cookie('favoris', json_encode($arrFavoris), time() + 3600, '/'));
                
                

                return $response;
            }
            else
            {
                $this->addFlash('warning', 'L\'article est déjà dans vos favoris !');
            }
        }

        if(strpos($chemin, 'panier'))
            return $this->redirectToRoute('home.panier', ['id' => $article->getId()]);
        else if(strpos($chemin, 'detail'))
            return $this->redirectToRoute('article.articleDetail', ['id' => $article->getId()]);
    
        return $this->redirectToRoute('home.index', ['id' => $article->getId()]);
    }

    /**
     * Selon la note, je renvoie un texte correspondant. Un peu lourd mais fait l'affaire pour le moment.
     * @param [int] $note
     * @return array
     */
    private function ReturnText($note): array
    {
        switch($note)
        {
            case 1:
                return ['Très mauvais état','Trous et déchirures plus ou moins importantes, irréparables.','Tâches étendues et incrustées, multiples et très visibles.', 
                        'Usure extrême, tissu effiloché ou détérioré.','Vêtement inutilisable sans réparation majeure.'];
            case 2:
                return ['Mauvais état','Trous et déchirures mineures mais nombreuses.','Tâches visibles et persistantes, difficiles à enlever.',
                        'Usure générale marquée, couleur ternie.','Réparations nécessaires avant de pouvoir être porté.' ];
            case 3:
                return ['État passable','Quelques déchirures mineures, réparables.','Plusieurs tâches légères nécessitant un bon nettoyage.',
                        'Usure perceptible mais pas critique, légèrement défraîchi.','Peut être porté mais avec des imperfections visibles.'];
            case 4:
                return ['Bon état','Très peu de défauts, aucune déchirure significative.','Tâches très légères, pas immédiatement visibles.',
                        'Usure légère, tissu encore en bon état.','Porté avec soin, apparence générale satisfaisante.'];
            case 5:
                return ['Très bon état voir neuf','Aucun défaut structurel, ni trou ni déchirure.','Aucune tâche visible, aspect presque neuf.','Usure quasi inexistante, tissu presque neuf.',
                        'Aspect général très soigné, peut être porté sans souci.'];
        }
    }
}
