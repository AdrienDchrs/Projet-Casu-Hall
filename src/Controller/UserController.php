<?php 

namespace App\Controller;

use App\Form\CompteType;
use App\Entity\Utilisateur;
use App\Repository\MarqueRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private MarqueRepository $repositoryMarque; 
    private CategorieRepository $repositoryCategorie;

    private array $marques; 
    private array $categories;

    /**
     * Constructeur de la classe
     *
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     */
    public function __construct(MarqueRepository $repositoryMarque,CategorieRepository $repositoryCategorie)
    {
        $this->repositoryMarque         = $repositoryMarque;
        $this->repositoryCategorie      = $repositoryCategorie;

        $this->marques = $this->repositoryMarque->findAll();
        $this->categories = $this->repositoryCategorie->findAll();
    }

    /**
     * Contrôleur permettant de modifier les informations en base d'un utilisateur
     * Deux boutons sont présents sur le formulaire : un pour modifier le mot de passe et un pour modifier les autres informations. 
     * Ces boutons sont différenciés par leur nom d'action qui est récupéré lorsque l'on clique dessus. 2 formulaires dans une même vue pour expliquer plus facilement.
     * @param Utilisateur $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/mon_compte/{id}', name:'edition.compte', methods:['GET', 'POST'])]
    #[ParamConverter('user', class: 'App\Entity\Utilisateur')]
    public function modifierUtilisateur(Utilisateur $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {

        // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
        if(!$this->getUser())
            $this->redirectToRoute('security.connexion');

        // Si l'utilisateur connecté n'est pas le même que celui dont on veut modifier le compte, on le redirige vers sa page de compte
        if($this->getUser() !== $user)
            $this->redirectToRoute('edition.compte', ['id' => $user->getId()]);

        $form = $this->createForm(CompteType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $action = $form->get('action')->getData();

            if($action === 'changePassword')
            {
                $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));

                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
            }
            else if($action === 'changePhoneNumber')
            {
                $telephoneFormat = $user->getTelephone();
                $telephoneFormat = preg_replace('/[^0-9]/', '', $telephoneFormat);

                $user->setTelephone('+' . substr($telephoneFormat, 0, 2) . ' ' . substr($telephoneFormat, 2, 1) . ' ' . substr($telephoneFormat, 3, 2) . ' ' . substr($telephoneFormat, 5, 2) . ' ' . substr($telephoneFormat, 7, 2) . ' ' . substr($telephoneFormat, 9, 2));

                $this->addFlash('success', 'Vos informations ont bien été modifiées.');
            }
            
            $manager->persist($user); 
            $manager->flush();

            return $this->redirectToRoute('edition.compte', ['id' => $user->getId()]); 
        }
        else if ($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('danger', 'Veuillez vérifier les informations saisies.');
        }
    
        return $this->render('security/compte.html.twig', ['marques' => $this->marques,'categories' => $this->categories,
                                                            'user' => $this->getUser(),'form' => $form->createView()]);
    } 
}