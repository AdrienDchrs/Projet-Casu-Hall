<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ResetPasswordRequestRepository;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    /**
     * Constructeur de la classe
     * @param object $user
     * @param \DateTimeInterface $expiresAt
     * @param string $selector
     * @param string $hashedToken
     */
    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Getters
     */
    public function getId(): ?int       {   return $this->id;   }
    public function getUser(): object   {   return $this->user; }
}
