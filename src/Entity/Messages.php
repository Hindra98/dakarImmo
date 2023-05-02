<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_at = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Utilisateur $utilisateur_send = null;

    #[ORM\ManyToOne(inversedBy: 'message')]
    private ?Utilisateur $utilisateur_receive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at = null): self
    {
        $this->create_at = new \DateTimeImmutable;

        return $this;
    }

    public function getUtilisateurSend(): ?Utilisateur
    {
        return $this->utilisateur_send;
    }

    public function setUtilisateurSend(?Utilisateur $utilisateur_send): self
    {
        $this->utilisateur_send = $utilisateur_send;

        return $this;
    }

    public function getUtilisateurReceive(): ?Utilisateur
    {
        return $this->utilisateur_receive;
    }

    public function setUtilisateurReceive(?Utilisateur $utilisateur_receive): self
    {
        $this->utilisateur_receive = $utilisateur_receive;

        return $this;
    }
}
