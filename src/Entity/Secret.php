<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Json;

#[ORM\Entity(repositoryClass: SecretRepository::class)]
class Secret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $hash;

    #[ORM\Column(type: 'string', length: 255)]
    private $secretText;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $expiresAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $remainingViews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getSecretText(): ?string
    {
        return $this->secretText;
    }

    public function setSecretText(string $secretText): self
    {
        $this->secretText = $secretText;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRemainingViews(): ?int
    {
        return $this->remainingViews;
    }

    public function setRemainingViews(?int $remainingViews): self
    {
        $this->remainingViews = $remainingViews;

        return $this;
    }

    /*public function json(): ?json
    {
        return $this->json([
            "hash" => $this->hash,
            "secretText" => $this->secretText,
            "createdAt" => $this->createdAt->format('c'),
            "expiresAt" => $this->expiresAt ? $this->expiresAt->format('c') : $this->expiresAt,
            "remainingViews" => $this->remainingViews
        ]);
    }*/
}
