<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $idContact = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 10, max:100)]
    private ?string $emailContact = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 1, max:100)]
    private ?string $objet = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 1)]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    private ?\DateTimeImmutable $dateEnvoi = null;

    public function __construct()
    {
        $this->dateEnvoi = new \DateTimeImmutable();
    }

    /**
     * Getters et Setters
     *
     */
    public function getIdContact(): ?int                {   return $this->idContact;    }
    public function getEmailContact(): ?string          {   return $this->emailContact; }
    public function getObjet(): ?string                 {   return $this->objet;        }
    public function getCommentaire(): ?string           {   return $this->commentaire;  }
    public function getDateEnvoi(): ?\DateTimeImmutable {   return $this->dateEnvoi;    }

    public function setEmailContact(string $emailContact): self
    {
        $this->emailContact = $emailContact;
        return $this;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function setDateEnvoi(\DateTimeImmutable $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;
        return $this;
    }
}
