<?php 

namespace App\EntityListener;

use App\Entity\Utilisateur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{
    private UserPasswordHasherInterface $hasher;

    /**
     * Constructeur de la classe
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function prePersist(Utilisateur $user)
    {
        $this->encoderPassword($user);
    }

    public function preUpdate(Utilisateur $user)
    {
        $this->encoderPassword($user);  
    }

    /**
     * Cette fonction permet d'encoder le mot de passe de l'utilisateur
     * 
     * @param Utilisateur $user
     * @return void
     */
    public function encoderPassword(Utilisateur $user)
    {
        if($user->getPlainPassword() === null)
            return; 

        $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
    }
}

?> 