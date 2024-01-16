<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Table(name: 'movies')]
#[ORM\Entity]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column('id', type: 'guid')]
    public readonly string $id;

    #[ORM\Column('title', type: 'string')]
    public readonly string $title;

    public function __construct(string $title)
    {
        $this->id = UuidV6::generate();
        $this->title = $title;
    }
}
