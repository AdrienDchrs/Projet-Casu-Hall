<?php 

namespace App\Controller;

use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\{Marque,Article,Categorie,Image};
use App\Form\{MarqueType,ArticleType,CategorieType};
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\{Request,Response};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\{MarqueRepository,PanierRepository,ArticleRepository,CategorieRepository,ArticleFavoriRepository};

class AdminDataController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private PanierRepository $repositoryPanier;
    private ArticleRepository $repositoryArticle;
    private CategorieRepository $repositoryCategorie;
    private ArticleFavoriRepository $repositoryArticleFavori;

    private EntityManagerInterface $manager;
    private TranslatorInterface $translator;

    private array $marques; 
    private array $categories;
    private array $articlesFavoris;
    private array $articles;
    private array $panier;


    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param RepositoryArticlesFavoris $repositoryArticlesFavoris
     * @param EntityManagerInterface $manager
     * @param ArticleRepository $repositoryArticle
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager, ArticleFavoriRepository $repositoryArticleFavori, ArticleRepository $repositoryArticle, PanierRepository $repositoryPanier, TranslatorInterface $translator)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryArticle = $repositoryArticle;
        $this->repositoryCategorie = $repositoryCategorie;
        $this->repositoryArticleFavori = $repositoryArticleFavori;

        $this->manager = $manager;

        $this->categories = $this->repositoryCategorie->findAll();
        $this->marques = $this->repositoryMarque->findBy([], ['nomMarque' => 'ASC']);

        $this->translator = $translator;
    }

    /**
     * Contrôleur permettant à l'administrateur d'ajouter une marque dans sa boutique
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Security('is_granted("ROLE_ADMIN")')]
    #[Route('/ajouter-marque', 'home.ajouter-marque', methods:['GET', 'POST'])]
    public function addMarque(Request $request): Response
    {
        $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);

        $marque = new Marque();
        $form = $this->createForm(MarqueType::class, $marque);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $action = $form->get('action')->getData();

            $marque = $this->repositoryMarque->findOneBy(['nomMarque' => $form->get('nomMarque')->getData()]);

            if($action === 'add')
            {
                if($marque)
                {
                    $message = $this->translator->trans("Votre marque existe déjà, veuillez réessayer.");
                    $this->addFlash("warning", $message);
                }
                else
                {                    
                    $marque = $form->getData();
                    $this->manager->persist($marque);
                    $this->manager->flush();

                    $this->addFlash('success', 'La marque a bien été ajoutée.');
                }

            }
            else if($action === 'delete')
            {
                if($marque)
                {
                    $this->DeleteLinkedArticles('idMarque',$marque);

                    $this->manager->remove($marque);
                    $this->manager->flush();
                    
                    $this->addFlash('success', 'La marque a bien été supprimée.');
                }
                else
                {
                    $this->addFlash('error', 'La marque n\'existe pas !');
                }
            }
           
            return $this->redirectToRoute('home.ajouter-marque', ['marques' =>  $this->marques,'categories' =>  $this->categories, 
                                                                  'articlesFavoris' => $this->articlesFavoris,'panier' => $this->panier]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('errorBrand', 'Erreur lors de l\'ajout de la marque !');
        }

        return $this->render('ajouter-marque.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                          'user' => $this->getUser(),   'form' => $form->createView(), 
                                                          'articlesFavoris' => $this->articlesFavoris, 
                                                          'panier' => $this->panier]);
    }

    /**
     * Contrôleur permettant à l'administrateur d'ajouter une catégorie dans sa boutique
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Security('is_granted("ROLE_ADMIN")')]
    #[Route('/ajouter-categorie', 'home.ajouter-categorie', methods:['GET', 'POST'])]
    public function addCategorie(Request $request): Response
    {
        $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);

        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $action = $form->get('action')->getData();
            $categorie = $this->repositoryCategorie->findOneBy(['nomCategorie' => $form->get('nomCategorie')->getData()]);

            if($action === 'add')
            {
                if($categorie)
                {
                    $this->addFlash('warning', 'La catégorie existe déja, veuillez réessayer.');
                }
                else 
                {
                    $categorie = $form->getData();
                    $this->manager->persist($categorie);
                    $this->manager->flush();

                    $this->addFlash('success', 'La catégorie a bien été ajoutée.');
                }
                
            }
            else if($action === 'delete')
            {
                if($categorie)
                {
                    $this->DeleteLinkedArticles('idCategorie',$categorie);

                    $this->addFlash('success', 'La catégorie a bien été supprimée.');
                    $this->manager->remove($categorie);
                    $this->manager->flush();
                }
                else
                {
                    $this->addFlash('error', 'La catégorie n\'existe pas !');
                }
            }
           
            return $this->redirectToRoute('home.ajouter-categorie', ['marques' =>  $this->marques,'categories' =>  $this->categories,
                                                                     'articlesFavoris' => $this->articlesFavoris,'panier' => $this->panier]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Erreur lors de l\'ajout de la marque.');
        }

        return $this->render('ajouter-categorie.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                             'user' => $this->getUser(),  'form' => $form->createView(),
                                                             'articlesFavoris' => $this->articlesFavoris, 'panier' => $this->panier]);
    }

    /**
     * Contrôleur permettant à l'administrateur d'ajouter un article dans sa boutique
     *
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Security('is_granted("ROLE_ADMIN")')]
    #[Route('/ajouter-article', 'home.ajouter-article', methods:['GET', 'POST'])]
    public function addArticle(Request $request, ImageService $imageService): Response
    {
        $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['utilisateur' => $this->getUser()]);
        $this->panier = $this->repositoryPanier->findBy(['utilisateur' => $this->getUser(), "isDone" => false]);

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article, ['marques' => $this->marques, 'categories' => $this->categories]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $lstImages = $form->get('images')->getData();
            $folder = 'articles';
            
            foreach($lstImages as $image)
            {   
                $fichier = $imageService->add($image, $folder, 300, 300); 
                
                $imgEntity = new Image();
                $imgEntity->setName($fichier);
                $article->addImage($imgEntity);
            }

            
            $article = $form->getData();
            
            $this->manager->persist($article);
            $this->manager->flush();
            
            $this->addFlash('success', 'L\'article a bien été ajoutée.');

            return $this->redirectToRoute('articles.all', ['marques' =>  $this->marques,'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('error', 'Erreur lors de l\'ajout de l\'article !');
        }

        return $this->render('/articles/ajouter-article.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                           'user' => $this->getUser(), 'form' => $form->createView(),
                                                           'articlesFavoris' => $this->articlesFavoris,'panier' => $this->panier]); 
    }

    /**
     * Va permettre de supprimer les articles ainsi que les articles favoris correspondant à la catégorie ou à la marque supprimée.
     *
     * @param [type] $article
     * @return void
     */
    private function DeleteLinkedArticles($param, $id)
    {
        $this->articles = $this->repositoryArticle->findBy([$param => $id]); 

        if($this->articles > 0)
        {
            for($i = 0; $i < count($this->articles); $i++)
            {
                $this->articlesFavoris = $this->repositoryArticleFavori->findBy(['article' => $this->articles[$i]]);
                $this->panier = $this->repositoryPanier->findBy(['article' => $this->articles[$i]]);
                

                if($this->articlesFavoris > 0)
                    for($j = 0; $j < count($this->articlesFavoris); $j++)
                        $this->manager->remove($this->articlesFavoris[$j]);

                if($this->panier > 0)
                    for($j = 0; $j < count($this->panier); $j++)
                        $this->manager->remove($this->panier[$j]);

                $this->manager->remove($this->articles[$i]);
            }
        }
    }
}