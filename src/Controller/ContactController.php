<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\MarqueRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
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
     * Formulaire de contact qui donne la possibilité d'envoyer un mail à l'adresse professionnelle du projet, le tout sur MailTrap. 
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/contact', 'home.contact', methods:['GET', 'POST'])]
    public function contact(Request $request, MailerInterface $mailer): Response
    { 
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $contact = $form->getData();

            $this->manager->persist($contact);
            $this->manager->flush();

            $email = (new TemplatedEmail())
            ->from($contact->getEmailContact())  
            ->to('casuhall.contact@gmail.com')
            ->subject($contact->getObjet())
            ->htmlTemplate('emailContact/email.html.twig')
               ->context(['contact' => $contact]);

            $mailer->send($email);  

            $this->addFlash('successfull', 'Votre demande a été envoyé avec succès !');

            return $this->redirectToRoute('home.contact', ['marques' =>  $this->marques,
                                                         'categories' =>  $this->categories]);
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('errorMail', 'Une erreur est survenue lors de l\'envoi du mail !');
        }

        return $this->render('emailContact/contact.html.twig', ['marques' => $this->marques, 'categories' => $this->categories, 
                                                                'user' => $this->getUser(),   'form' => $form->createView()]);
    }
}
