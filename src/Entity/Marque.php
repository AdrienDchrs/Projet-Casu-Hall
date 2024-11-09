<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MarqueRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $idMarque = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 4, max:50)]
    private ?string $nomMarque = null;

    #[Vich\UploadableField(mapping: 'articles_images', fileNameProperty: 'imageMarque')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $imageMarque = null;

    /**
     * Getters et Setters
     */
    public function getIdMarque(): ?int         {   return $this->idMarque; }
    public function getNomMarque(): ?string     {   return $this->nomMarque;}
    public function getImageFile(): ?File       {   return $this->imageFile; }
    public function getImageMarque(): ?string     {   return $this->imageMarque; }


    public function setNomMarque(string $nomMarque): static
    {
        $this->nomMarque = $nomMarque;

        return $this;
    }

    public function setImageFile(?File $imageFile)
    {
        return $this->imageFile = $imageFile;
    }

    public function setImageMarque(?string $imageName)
    {
        return $this->imageMarque = $imageName;
    }
}
