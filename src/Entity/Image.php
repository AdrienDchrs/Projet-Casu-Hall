<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'images')]
    private $articles;

    public function getId(): ?int           { return $this->id;         }
    public function getName(): ?string      { return $this->name;       }
    public function getArticles(): ?Article { return $this->articles;   }


    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setArticle(?Article $article): self
    {
        $this->articles = $article;

        return $this;
    }
}
