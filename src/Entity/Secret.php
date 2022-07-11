<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use DateTime;
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
        return openssl_decrypt($this->secretText, 'AES-128-CBC', $this->hash, 0, "1111111111111111");
    }

    public function setSecretText(string $secretText): self
    {
        $this->secretText = openssl_encrypt($secretText, 'AES-128-CBC', $this->hash, 0, "1111111111111111");

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

    public function isExpired(): bool
    {
        return ($this->expiresAt && $this->expiresAt < new DateTime("now"));
    }

    public function json()
    {
        return [
            "hash" => $this->hash,
            "secretText" => $this->getSecretText(),
            "createdAt" => $this->createdAt->format('c'),
            "expiresAt" => $this->expiresAt ? $this->expiresAt->format('c') : $this->expiresAt,
            "remainingViews" => $this->remainingViews
        ];
    }
}
