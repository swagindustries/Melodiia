<?php

declare(strict_types=1);

namespace TestApplication\Entity;

use Doctrine\ORM\Mapping as ORM;
use SwagIndustries\Melodiia\Crud\MelodiiaModel;

/**
 * @ORM\Entity()
 */
class Todo implements MelodiiaModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

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
}
