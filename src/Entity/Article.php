<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[Vich\Uploadable]
#[UniqueEntity('nomArticle')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 4, max:50)]
    private ?string $nomArticle = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\PositiveOrZero()]
    private ?float $prix = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 5)]
    #[Assert\Length(min: 1, max:5)]
    private ?string $taille = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\PositiveOrZero()]
    private ?int $quantite_stock = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    private ?int $note = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id_categorie', nullable: false)]
    private ?Categorie $idCategorie = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(targetEntity: Marque::class)]    
    #[ORM\JoinColumn(name: 'id_marque', referencedColumnName: 'id_marque', nullable: false)]
    private ?Marque $idMarque = null;

    #[ORM\OneToMany(mappedBy: 'articles', targetEntity: Image::class, orphanRemoval: true, cascade:['persist'])]
    private $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }
    
    /**
     * Getters et Setters
     */
    public function getId(): ?int                   { return $this->id;             }
    public function getNomArticle(): ?string        { return $this->nomArticle;     }
    public function getPrix(): ?float               { return $this->prix;           }
    public function getTaille(): ?string            { return $this->taille;         }       
    public function getIdCategorie(): ?Categorie    { return $this->idCategorie;    }
    public function getIdMarque(): ?Marque          { return $this->idMarque;       }
    public function getQuantiteStock(): ?int        { return $this->quantite_stock; }
    public function getDescription(): ?string       { return $this->description;    }
    public function getNote(): ?int                 { return $this->note;           }
    public function getImages(): ?Collection        { return $this->images;         }

    public function setNomArticle(string $nomArticle): static
    {
        $this->nomArticle = $nomArticle;

        return $this;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function setTaille(string $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function setQuantiteStock(int $quantite_stock): static
    {
        $this->quantite_stock = $quantite_stock;

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function setIdCategorie(?Categorie $idCategorie): self
    {
        $this->idCategorie = $idCategorie;

        return $this;
    }

    public function setIdMarque(?Marque $idMarque): self
    {
        $this->idMarque = $idMarque;

        return $this;
    }

    public function addImage(Image $image): self
    {
        if(!$this->images->contains($image))
        {
            $this->images[] = $image;
            $image->setArticle($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) 
            if ($image->getArticles() === $this) 
                $image->setArticle(null);

        return $this;
    }
}
