<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MarqueRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idMarque = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 4, max:50)]
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    private ?string $nomMarque = null;

    /**
     * Getters et Setters
     */
    public function getIdMarque(): ?int         {   return $this->idMarque; }
    public function getNomMarque(): ?string     {   return $this->nomMarque;}

    public function setNomMarque(string $nomMarque): static
    {
        $this->nomMarque = $nomMarque;

        return $this;
    }
}
