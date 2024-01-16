<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Entity;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use SlopeIt\RepositoryDemo\Repository\RentalRepository;
use Symfony\Component\Uid\UuidV6;

#[ORM\Table(name: 'rentals')]
#[ORM\Entity(repositoryClass: RentalRepository::class)]
class Rental
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column('id', type: 'guid')]
    public readonly string $id;

    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn('movie_id')]
    public readonly Movie $movie;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn('customer_id')]
    public readonly Customer $customer;

    #[ORM\Column('rent_date', type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $rentDate;

    #[ORM\Column('return_date', type: 'datetimetz_immutable', nullable: true)]
    private ?\DateTimeImmutable $returnDate = null;

    public function __construct(
        Movie $movie,
        Customer $customer,
        \DateTimeImmutable $rentDate = new \DateTimeImmutable(),
    ) {
        $this->id = UuidV6::generate();
        $this->movie = $movie;
        $this->customer = $customer;
        $this->rentDate = $rentDate;
    }

    public function return(\DateTimeImmutable $returnDate): void
    {
        Assertion::null($this->returnDate, 'Same rental cannot be returned more than once.');
        $this->returnDate = $returnDate;
    }

    public function returnDate(): ?\DateTimeImmutable
    {
        return $this->returnDate;
    }
}
