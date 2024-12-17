<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM; 
use App\Repository\PanierRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    private ?string $idCommande = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\PositiveOrZero()]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?bool $isDone = null;

    #[Assert\NotNull()]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'id_article', referencedColumnName: 'id', nullable: false)]
    private ?Article $article = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int                       {   return $this->id;           }
    public function getIdCommande(): ?string            {   return $this->idCommande;   }
    public function getPrix(): ?float                   {   return $this->prix;         }
    public function getQuantite(): ?int                 {   return $this->quantite;     }
    public function getIsDone(): ?bool                  {   return $this->isDone;       }
    public function getUtilisateur(): ?Utilisateur      {   return $this->utilisateur;  }
    public function getArticle(): ?Article              {   return $this->article;      }
    public function getCreatedAt(): ?DateTimeImmutable  {   return $this->createdAt;    }

    
    public function setIdCommande($idCommande): static 
    {
        $this->idCommande = $idCommande;
        
        return $this;
    }

    public function setPrix($prix): static 
    {
        $this->prix = $prix;
        
        return $this;
    }

    public function setQuantite($quantite): static 
    {
        $this->quantite = $quantite;
        
        return $this;
    }

    public function setIsDone(bool $isDone): static
    {
        $this->isDone = $isDone; 

        return $this;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
