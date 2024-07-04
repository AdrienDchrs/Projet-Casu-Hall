<?php

namespace App\Controller;

use App\Entity\Marque; 
use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ModifierArticleType;
use App\Repository\MarqueRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArticleController extends AbstractController
{
    private PaginatorInterface $paginator;
    private MarqueRepository $repositoryMarque; 
    private ArticleRepository $repositoryArticle;
    private CategorieRepository $repositoryCategorie;

    private array $marques; 
    private array $articles;
    private array $categories;

    private EntityManagerInterface $manager;

    /**
     * Constructeur
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param ArticleRepository $repositoryArticle
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $manager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, ArticleRepository $repositoryArticle, PaginatorInterface $paginator, EntityManagerInterface $manager)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticle = $repositoryArticle;

        $this->marques = $this->repositoryMarque->findAll();
        $this->articles = $this->repositoryArticle->findAll();
        $this->categories = $this->repositoryCategorie->findAll();

        $this->paginator = $paginator;

        $this->manager = $manager;
    }

    /**
     * Affichage de tous les articles sans aucuns filtres
     * On passe par une pagination dans le cas où nous avons plus de 12 articles
     * @param Request $request
     * @return Response
     */
    #[Route('/articles', 'articles.all', methods:['GET'])]
    public function allArticles(Request $request): Response
    {

        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer, 'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $this->getUser(), 
                                                             'titre' => 'Tous les articles disponibles']);
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
    public function articlesMarque(Marque $marque, Request $request): Response
    {
        $titre =  'Nos articles de la marque ' . $marque->getNomMarque();

        $this->articles = $this->repositoryArticle->findBy(['idMarque' => $marque->getIdMarque()]);

        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $this->getUser(), 'titre' => $titre]);
    }

    /**
     * On affiche les articles selon l'identifiant de la catégorie passée en paramètre de l'url. 
     * On passe par une pagination dans le cas où nous avons plus de 12 articles de cette catégorie
     * @param Request $request
     * @param Categorie $categorie
     * @return Response
     */
    #[Route('/articles/categorie/{id}', 'articles.articleCategorie', methods:['GET', 'POST'])]
    #[ParamConverter('Categorie', class: 'App\Entity\Categorie')]
    public function articlesCategorie(Categorie $categorie, Request $request): Response
    {
        $titre =  'Nos ' .$categorie->getNomCategorie();

        $this->articles = $this->repositoryArticle->findBy(['idCategorie' => $categorie->getIdCategorie()]);

        $articlesPaginer = $this->paginator->paginate($this->articles,$request->query->getInt('page', 1),12);

        return $this->render('articles/articles.html.twig', ['articles' => $articlesPaginer,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $this->getUser(), 'titre' => $titre]);
    }

    /**
     * On affiche un détail de l'article au clic sur ce dernier, on y passe un identifiant d'article en paramètre de l'url
     * La méthode renvoyerTexte() permet de renvoyer un descriptif de l'état de l'article selon sa note
     * Par la suite, pourquoi pas mettre en base s'il y a d'autres textes à afficher selon la note
     * @param Article $article
     * @return Response
     */
    #[Route('/articles/detail/{id}', 'articles.articleDetail', methods:['GET', 'POST'])]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    public function articleDetail(Article $article): Response
    {
        $textes = $this->renvoyerTexte($article->getNote());
        return $this->render('articles/article-detail.html.twig', ['articles' => $article,'marques' => $this->marques,
                                                             'categories' => $this->categories,'user' => $this->getUser(),
                                                            'textes' => $textes]);
    }

    /**
     * Suppression d'un article en passant par son identifiant
     * @param Article $article
     * @return Response
     */
    #[Route('/articles/supprimer/{id}', 'article.delete', methods:['GET', 'POST'])]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    public function delete(Article $article): Response
    {
        if(!$article)
        {
            $this->addFlash('error', 'L\'article n\'existe pas !');
            return $this->redirectToRoute('articles.all');
        }

        $this->articles = $this->repositoryArticle->findBy(['id' => $article->getId()]);

        $this->manager->remove($this->articles[0]); 
        $this->manager->flush();

        $this->addFlash('success', 'L\'article a bien été supprimé !');

        return $this->redirectToRoute('articles.all', ['articles' => $this->articles]);
    }

    /**
     * Modification d'un article en passant son identifiant. On passe par un formulaire de modification
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    #[Route('/articles/modifier/{id}', 'article.modifier', methods:['GET', 'POST'])]
    #[ParamConverter('Article', class: 'App\Entity\Article')]
    public function modifier(Article $article, Request $request): Response
    {
        $form = $this->createForm(ModifierArticleType::class, $article, ['marques' => $this->marques, 'categories' => $this->categories]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $articleModifie = $form->getData();

            $this->manager->persist($articleModifie);
            $this->manager->flush();
            
            $this->addFlash('success', 'L\'article a bien été modifié !');
            
            return $this->redirectToRoute('articles.articleDetail', ['id' => $article->getId()]);

        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Erreur de saisies lors de la modification de l\'article !');
        }

        return $this->render('articles/modifier-article.html.twig', ['articles' => $this->articles,'marques' => $this->marques,
                                                                     'categories' => $this->categories, 'form' => $form->createView(), 
                                                                     'user' => $this->getUser()]); 
    }

    /**
     * Selon la note, je renvoie un texte correspondant. Un peu lourd mais fait l'affaire pour le moment.
     * @param [int] $note
     * @return array
     */
    private function renvoyerTexte($note): array
    {
        switch($note)
        {
            case 1:
                return ['Très mauvais état','Trous et déchirures importantes, irréparables.','Taches étendues et incrustées, multiples et très visibles.', 
                        'Usure extrême, tissu effiloché ou détérioré.','Vêtement inutilisable tel quel.'];
            case 2:
                return ['Mauvais état','Trous et déchirures mineures mais nombreuses.','Tâches significatives et nombreuses, difficiles à enlever.',
                        'Usure générale prononcée, couleur ternie.','Réparations nécessaires pour être porté.' ];
            case 3:
                return ['Etat médiocre','Quelques déchirures ou trous modérés, réparables.','Plusieurs taches visibles mais réparables avec un bon nettoyage.', 
                        'Usure notable, tissu et coutures affaiblis.','Nécessite des soins pour améliorer son apparence.'];
            case 4:
                return ['État passable','Trous ou déchirures très mineures, peu nombreux.','Quelques taches légères peuvent nécessiter un nettoyage.',
                        'Usure perceptible mais pas critique, légèrement défraîchi.','Peut être porté mais avec des imperfections visibles.'];
            case 5:
                return ['État correct','Aucune déchirure majeure, quelques accrocs mineurs.','Tâches très légères et localisées, partiellement effaçables.',
                        'Usure modérée, couleur légèrement passée.','Aspect général acceptable avec quelques défauts visibles.'];
            case 6:
                return ['Bon état','Très peu de défauts, aucune déchirure significative.','Tâches très légères, pas immédiatement visibles.',
                        'Usure légère, tissu encore en bon état.','Porté avec soin, apparence générale satisfaisante.'];
            case 7:
                return ['Très bon état','Aucun trou ni déchirure.','Taches minimes, quasiment invisibles.','Usure très légère, tissu en bon état.',
                        'Aspect général très soigné, peut être porté sans souci.'];
            case 8:
                return ['Excellent état','Aucun défaut structurel, ni trou ni déchirure.','Aucune tâche, apparence propre.','Usure presque inexistante, tissu quasi neuf.',
                        'Aspect général impeccable, porté très peu de fois.'];
            case 9:
                return ['Comme neuf','Aucune usure perceptible, aucun défaut.','Aucun signe d\'utilisation, tissu impeccable.','État quasi identique à celui du neuf, sans étiquette.',
                        'Aspect général comme sorti du magasin.'];
            case 10:
                return ['Neuf','Jamais porté, état parfait.','Étiquette encore attachée.','Aucun signe d\'usure ou de détérioration.','Aspect général totalement neuf, sans aucun défaut.'];
        }
    }
}
