<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Repository;

use Doctrine\ORM\EntityRepository;
use SlopeIt\RepositoryDemo\Entity\Customer;
use SlopeIt\RepositoryDemo\Entity\Movie;
use SlopeIt\RepositoryDemo\Entity\Rental;

class RentalRepository extends EntityRepository
{
    public function countByCustomer(Customer $customer): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->andWhere('e.customer = :customer')->setParameter('customer', $customer)
            ->getQuery()->getSingleScalarResult();
    }

    public function findMadeAfterDateAndUnreturned(\DateTimeImmutable $afterDate): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.rentDate > :rentDate')->setParameter('rentDate', $afterDate)
            ->andWhere('e.returnDate IS NULL')
            ->getQuery()->getResult();
    }

    public function findMostRecentOfMovie(Movie $movie): ?Rental
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.movie = :movie')->setParameter('movie', $movie)
            ->orderBy('e.rentDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getResult()[0] ?: null;
    }
}
