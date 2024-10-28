<?php

declare(strict_types=1);

namespace TestApplication\Entity;

use Doctrine\ORM\Mapping as ORM;
use SwagIndustries\Melodiia\Crud\MelodiiaModel;

#[ORM\Entity]
class Todo implements MelodiiaModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $content;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $publishDate;

    #[ORM\Column(type: 'boolean')]
    private bool $archived = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getPublishDate(): ?\DateTimeImmutable
    {
        return $this->publishDate;
    }

    public function setPublishDate(?\DateTimeImmutable $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function archive(): void
    {
        $this->archived = true;
    }
}
