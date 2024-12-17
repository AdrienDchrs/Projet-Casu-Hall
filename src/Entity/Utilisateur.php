<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Validator\StrongPassword;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; 
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
// EntityListeners pour hasher les mots de passe
#[ORM\EntityListeners(['App\EntityListener\UserListener'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank()]
    #[Assert\Choice(choices: [0,1])]
    private ?int $civilite = 0;

    #[Assert\NotBlank()] 
    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 2, max:50)]
    private ?string $nomUtilisateur = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 3, max:50)]
    private ?string $prenomUtilisateur = null;

    #[ORM\Column()]
    #[Assert\NotBlank()]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[Assert\Email()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 6, max:100)]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotBlank()]
    #[Assert\Length(min:8)]
    private ?string $password = 'password';

    #[StrongPassword]
    private ?string $plainPassword = null;

    #[ORM\Column]
    #[Assert\NotBlank()]
    private array $roles = ['ROLE_USER'];

    /**
     * Getters et Setters
     */
    public function getId(): ?int                           { return $this->id;                 }
    public function getEmail(): ?string                     { return $this->email;              }
    public function getUserIdentifier(): string             { return (string) $this->email;     } 
    public function getPassword(): string                   { return $this->password;           }
    public function getPlainPassword(): ?string             { return $this->plainPassword;      }
    public function getNomUtilisateur(): ?string            { return $this->nomUtilisateur;     }
    public function getPrenomUtilisateur(): ?string         { return $this->prenomUtilisateur;  }
    public function getRoles(): array                       { return array_unique($this->roles);}
    public function getCivilite(): ?int                     { return $this->civilite;           }
    public function getDateNaissance(): ?\DateTimeImmutable { return $this->dateNaissance;      }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        //$this->plainPassword = null;
    }

    public function setNomUtilisateur(string $nomUtilisateur): static
    {
        $this->nomUtilisateur = $nomUtilisateur;

        return $this;
    }

    public function setPrenomUtilisateur(string $prenomUtilisateur): static
    {
        $this->prenomUtilisateur = $prenomUtilisateur;

        return $this;    
    }

    public function setCivilite(bool $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function setDateNaissance($dateNaissance): self
    {
        if ($dateNaissance instanceof \DateTime) 
        {
            $dateNaissance = \DateTimeImmutable::createFromMutable($dateNaissance);
        }
        
        $this->dateNaissance = $dateNaissance;

        return $this;
    }
}
