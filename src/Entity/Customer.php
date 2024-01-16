<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV6;

#[ORM\Table(name: 'customers')]
#[ORM\Entity]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column('id', type: 'guid')]
    public readonly string $id;

    #[ORM\Column('first_name', type: 'string')]
    public string $firstName;

    #[ORM\Column('last_name', type: 'string')]
    public string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->id = UuidV6::generate();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
