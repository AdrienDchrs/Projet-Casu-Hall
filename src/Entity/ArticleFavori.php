<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleFavoriRepository;

#[ORM\Entity(repositoryClass: ArticleFavoriRepository::class)]
class ArticleFavori
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'id_article', referencedColumnName: 'id', nullable: false)]
    private ?Article $article = null;

    public function getId(): ?int                   { return $this->id;             }
    public function getUtilisateur(): ?Utilisateur  { return $this->utilisateur;    }
    public function getArticle(): ?Article          { return $this->article;        }

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
