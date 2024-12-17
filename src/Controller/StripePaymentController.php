<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\{Panier, Article};
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository, ArticleRepository};

class StripePaymentController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator; 
    private EntityManagerInterface $entityManager;

    private PanierRepository $repositoryPanier;
    private ArticleRepository $repositoryArticle;


    private array $panier = [];
    private Article $article;

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param EntityManagerInterface $manager
     * @param ArticleFavoriRepository $repositoryArticleFavori
     * @param PanierRepository $repositoryPanier
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, PanierRepository $repositoryPanier, ArticleRepository $repositoryArticle)
    {
        $this->entityManager = $entityManager; 
        $this->urlGenerator = $urlGenerator;

        $this->repositoryPanier = $repositoryPanier;
        $this->repositoryArticle = $repositoryArticle;
    }
    
    #[Route('/panier/payment', name:'panier.payment', methods:['GET'])]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    public function PayPanier(): RedirectResponse
    {
        $user = $this->getUser();

        $this->panier = $this->entityManager->getRepository(Panier::class)->findBy(["utilisateur" => $user, "isDone" => false]);

        if(!$this->panier)
        {
            $this->addFlash("error", "Impossible de procéder au paiement pour le moment, veuillez réessayer.");

            return $this->redirectToRoute("home.panier");
        }

        $paymentStripe = [];

        for ($i = 0; $i < count($this->panier); $i++) 
        {
            $paymentItem = [
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $this->panier[$i]->getArticle()->getPrix() * 100,
                    'product_data' => [
                        'name' => $this->panier[$i]->getArticle()->getNomArticle(),
                    ],
                ],
                'quantity' => $this->panier[$i]->getQuantite(),
            ];
            $paymentStripe[] = $paymentItem;
        }

        $stripeSecretKey = $_SERVER['STRIPE_SECRET_KEY'];
        Stripe::setApiKey($stripeSecretKey);
        Stripe::setApiVersion("2024-10-28.acacia");
        
        $checkout_session = Session::create([
            'customer_email' => $user->getUserIdentifier(), 
            'line_items' => [[$paymentStripe]], 
            'mode' => 'payment', 
            'success_url' => $this->urlGenerator->generate('payment_success',[], UrlGeneratorInterface::ABSOLUTE_URL), 
            'cancel_url' => $this->urlGenerator->generate('payment_error',[], UrlGeneratorInterface::ABSOLUTE_URL), 
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['AL','AD','AT','BY','BE','BA','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IE','IT','LV','LT','LU','MT','MD','MC','ME','NL','NO','PL','PT','RO','RU','SM','RS','SK','SI','ES','SE','CH','UA','GB','VA']
                ],
            // 'metadata' => ['panier' => $panier], 
            //Taxe en fonction du pays 'automatic_tax' => ['enabled'],
            ]);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('panier/success', name:'payment_success')]
    public function StripeSuccess(): Response
    {
        $user = $this->getUser();

        $this->panier = $this->repositoryPanier->findBy(["utilisateur" => $user, "isDone" => false]);

        for($i = 0; $i < count($this->panier); $i++)
        {
            $this->article = $this->repositoryArticle->findOneBy(["id" => $this->panier[$i]->getArticle()->getId()]);
            
            $this->article->setQuantiteStock($this->article->getQuantiteStock() - $this->panier[$i]->getQuantite());
            
            $this->panier[$i]->setIsDone(true);
            
            $this->entityManager->persist($this->article);
            $this->entityManager->persist($this->panier[$i]);

            $this->entityManager->flush();
        }

        $this->addFlash("success", "Votre paiement a été validé !");

        // On récupère le panier vide pour refresh l'état de l'icône. 
        $this->panier = $this->repositoryPanier->findBy(["utilisateur" => $user, "isDone" => false]);

        return $this->redirectToRoute("articles.all", ["panier" => $this->panier]);
    }

    #[Route('panier/error', name:'payment_error')]
    public function StripeError(): Response
    {
        $this->addFlash("error","Une erreur est survenue lors du paiement, veuillez réessayer.");
        return $this->redirectToRoute("home.panier");
    }
}
?>