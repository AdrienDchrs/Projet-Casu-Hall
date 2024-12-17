<?php 

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CookieService 
{
    
    private EntityManagerInterface $manager;

    /**
     * Constructeur de la classe
     * @param MarqueRepository $repositoryMarque
     * @param CategorieRepository $repositoryCategorie
     * @param UtilisateurRepository $repositoryUtilisaeur
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /** 
     * Fonction privée qui permet de récupérer les favoris d'un utilisateur non connecté
     * @param Request $request sert à établir une requête HTTP pour récupérer les cookies de la session.
     */
    public function getCookieUserNotConnected(Request $request, $cookieName, $array)
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