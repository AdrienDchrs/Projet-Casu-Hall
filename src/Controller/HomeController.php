<?php 

namespace App\Controller;

use App\Entity\Marque;
use App\Entity\Article;
use App\Form\MarqueType;
use App\Entity\Categorie;
use App\Form\ArticleType;
use App\Form\CategorieType;
use App\Repository\MarqueRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;

    private EntityManagerInterface $manager;

    private array $marques; 
    private array $categories;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param EntityManagerInterface $manager
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie, EntityManagerInterface $manager)
    {
        $this->repositoryMarque = $repositoryMarque;
        $this->repositoryCategorie = $repositoryCategorie;

        $this->manager = $manager;

        $this->marques = $this->repositoryMarque->findAll();
        $this->categories = $this->repositoryCategorie->findAll();
    }

    /**
     * Controleur de la page d'accueil
     * @return Response
     */
    #[Route('/', 'home.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('home.html.twig', ['marques' => $this->marques,
                                                'categories' => $this->categories,
                                                'user' => $this->getUser()]);
    } 
    
    /**
     * Contrôleur permettant à l'administrateur d'ajouter une marque dans sa boutique
     * @param Request $request
     * @return Response
     */
    #[Route('/ajouter-marque', 'home.ajouter-marque', methods:['GET', 'POST'])]
    public function ajouterMarque(Request $request): Response
    {
        $marque = new Marque();
        $form = $this->createForm(MarqueType::class, $marque);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $action = $form->get('action')->getData();

            if($action === 'add')
            {
                $marque = $form->getData();
                $this->addFlash('AddOrDeleteBrand', 'La marque a bien été ajoutée');
                $this->manager->persist($marque);
                $this->manager->flush();
            }
            else if($action === 'delete')
            {
                $idMarque = $this->repositoryMarque->findOneBy(['nomMarque' => $form->get('nomMarque')->getData()]);

                if($idMarque !== null)
                {
                    $this->addFlash('AddOrDeleteBrand', 'La marque a bien été supprimée');
                    $this->manager->remove($idMarque);
                    $this->manager->flush();
                }
                else
                {
                    $this->addFlash('errorBrand', 'La marque n\'existe pas !');
                }
            }
           
            return $this->redirectToRoute('home.ajouter-marque', ['marques' =>  $this->marques,
                                                                  'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('errorBrand', 'Erreur lors de l\'ajout de la marque');
        }

        return $this->render('ajouter-marque.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                          'user' => $this->getUser(),   'form' => $form->createView()]);
    }

    /**
     * Contrôleur permettant à l'administrateur d'ajouter une catégorie dans sa boutique
     * @param Request $request
     * @return Response
     */
    #[Route('/ajouter-categorie', 'home.ajouter-categorie', methods:['GET', 'POST'])]
    public function ajouterCatetorie(Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $action = $form->get('action')->getData();

            if($action === 'add')
            {
                $categorie = $form->getData();
                $this->addFlash('AddOrDeleteCategorie', 'La catégorie a bien été ajoutée');
                $this->manager->persist($categorie);
                $this->manager->flush();
            }
            else if($action === 'delete')
            {
                $idMarque = $this->repositoryCategorie->findOneBy(['nomCategorie' => $form->get('nomCategorie')->getData()]);

                if($idMarque !== null)
                {
                    $this->addFlash('AddOrDeleteCategorie', 'La catégorie a bien été supprimée');
                    $this->manager->remove($idMarque);
                    $this->manager->flush();
                }
                else
                {
                    $this->addFlash('errorCategorie', 'La catégorie n\'existe pas !');
                }
            }
           
            return $this->redirectToRoute('home.ajouter-categorie', ['marques' =>  $this->marques,
                                                         'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('errorBrand', 'Erreur lors de l\'ajout de la marque');
        }

        return $this->render('ajouter-categorie.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                             'user' => $this->getUser(),  'form' => $form->createView()]);
    }

    /**
     * Contrôleur permettant à l'administrateur d'ajouter un article dans sa boutique
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/ajouter-article', 'home.ajouter-article', methods:['GET', 'POST'])]
    public function ajouterArticle(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article, ['marques' => $this->marques, 'categories' => $this->categories]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $article = $form->getData();
            
            $this->addFlash('AddArticle', 'L\'article a bien été ajoutée !');

            $this->manager->persist($article);
            $this->manager->flush();

            return $this->redirectToRoute('articles.all', ['marques' =>  $this->marques,
                                                         'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('errorArticle', 'Erreur lors de l\'ajout de l\'article');
        }

        return $this->render('ajouter-article.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                           'user' => $this->getUser(), 'form' => $form->createView()]); 
    }

    /**
     * Ce contrôleur permet de visualiser la page qui explique l'histoire du style casual et de la boutique
     * @return Response
     */
    #[Route('/notre-histoire', 'home.notre-histoire', methods:['GET', 'POST'])]
    public function notreHistoire(): Response
    {
        return $this->render('notre-histoire.html.twig', ['marques' => $this->marques, 'categories' => $this->categories,
                                                          'user' => $this->getUser()]);
    }
}
?> 