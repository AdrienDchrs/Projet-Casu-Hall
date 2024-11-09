<?php

namespace App\Entity;

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
    #[Assert\PositiveOrZero()]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?bool $isDone = null;


    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'id_article', referencedColumnName: 'id', nullable: false)]
    private ?Article $article = null;

    public function getId(): ?int                   {   return $this->id;           }
    public function getPrix(): ?float               {   return $this->prix;         }
    public function getQuantite(): ?int             {   return $this->quantite;     }
    public function getIsDone(): ?bool              {   return $this->isDone;       }
    public function getUtilisateur(): ?Utilisateur  {   return $this->utilisateur;  }
    public function getArticle(): ?Article          {   return $this->article;      }

    
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

    public function setIsDone()
    {
        return $this->isDone = !$this->isDone;
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
}
