<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategorieRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idCategorie = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 4, max:50)]
    private ?string $nomCategorie = null;

    /**
     * Getters et Setters
     */
    public function getIdCategorie(): ?int      {    return $this->idCategorie;  }
    public function getNomCategorie(): ?string  {    return $this->nomCategorie; }

    public function setNomCategorie(string $nomCategorie): static
    {
        $this->nomCategorie = $nomCategorie;

        return $this;
    }
}
