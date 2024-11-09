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
use App\Repository\{MarqueRepository,PanierRepository,CategorieRepository,ArticleFavoriRepository};

class StripePaymentController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator; 
    private EntityManagerInterface $entityManager;

    private PanierRepository $repositoryPanier;


    private array $panier = [];

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param EntityManagerInterface $manager
     * @param ArticleFavoriRepository $repositoryArticleFavori
     * @param PanierRepository $repositoryPanier
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, PanierRepository $repositoryPanier)
    {
        $this->entityManager = $entityManager; 
        $this->urlGenerator = $urlGenerator;

        $this->repositoryPanier = $repositoryPanier;
    }
    
    #[Route('/panier/payment', name:'panier.payment', methods:['GET'])]
    #[Security('is_granted("ROLE_USER") or is_granted("ROLE_ADMIN")')]
    public function PayPanier(): RedirectResponse
    {
        $user = $this->getUser();

        $panier = $this->entityManager->getRepository(Panier::class)->findBy(["utilisateur" => $user]);

        if(!$panier)
        {
            $this->addFlash("error", "Impossible de procéder au paiement pour le moment, veuillez réessayer.");

            return $this->redirectToRoute("home.panier");
        }

        $paymentStripe = []; // Assurez-vous que ce tableau est initialisé avant la boucle

        for ($i = 0; $i < count($panier); $i++) 
        {
            $paymentItem = [
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $panier[$i]->getArticle()->getPrix() * 100,
                    'product_data' => [
                        'name' => $panier[$i]->getArticle()->getNomArticle(),
                    ],
                ],
                'quantity' => $panier[$i]->getQuantite(),
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

        // Une fois que le paiement est validé, il faut décréménter la quantité et vider le panier. 

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('panier/success', name:'payment_success')]
    public function StripeSuccess(): Response
    {
        return $this->redirectToRoute("home.panier");
    }

    #[Route('panier/error', name:'payment_error')]
    public function StripeError(): Response
    {
        return $this->redirectToRoute("home.panier");
    }
}
?>