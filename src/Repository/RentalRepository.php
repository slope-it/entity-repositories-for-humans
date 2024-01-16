<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Repository;

use Assert\Assertion;
use Doctrine\ORM\QueryBuilder;
use SlopeIt\RepositoryDemo\Entity\Rental;
use SlopeIt\RepositoryDemo\Query\AbstractEntityQuery;
use SlopeIt\RepositoryDemo\Query\RentalQuery;

/**
 * @extends AbstractEntityRepository<Rental, RentalQuery>
 */
class RentalRepository extends AbstractEntityRepository
{
    public static function getEntityClassName(): string
    {
        return Rental::class;
    }

    public static function getEntityQueryClassName(): string
    {
        return RentalQuery::class;
    }

    protected function configureBuilderFromEntityQuery(QueryBuilder $builder, AbstractEntityQuery $query): QueryBuilder
    {
        /* Validation */

        if ($query->rentDateLessThanOrEqualTo !== null) {
            Assertion::lessThan(
                $query->rentDateGreaterThan,
                $query->rentDateLessThanOrEqualTo,
                'rentDateGreaterThan cannot be greater than rentDateLessThanOrEqualTo.'
            );
        }

        /* Filtering */

        if ($query->movie !== null) {
            $builder->andWhere('e.movie = :movie')->setParameter('movie', $query->movie);
        }

        if ($query->customer !== null) {
            $builder->andWhere('e.customer = :customer')->setParameter('customer', $query->customer);
        }

        if ($query->rentDateLessThanOrEqualTo !== null) {
            $builder->andWhere('e.rentDate <= :rentDateLessThanOrEqualTo')
                ->setParameter('rentDateLessThanOrEqualTo', $query->rentDateLessThanOrEqualTo);
        }

        if ($query->rentDateGreaterThan !== null) {
            $builder->andWhere('e.rentDate > :rentDateGreaterThan')
                ->setParameter('rentDateGreaterThan', $query->rentDateGreaterThan);
        }

        if ($query->returned === true) {
            $builder->andWhere('e.returnDate IS NOT NULL');
        } elseif ($query->returned === false) {
            $builder->andWhere('e.returnDate IS NULL');
        }

        /* Ordering */

        foreach ($query->orderBy as $orderBy) {
            switch ($orderBy) {
                case RentalQuery::ORDER_BY_RENT_DATE_DESC:
                    $builder->addOrderBy('e.rentDate', 'DESC');
                    break;
                default:
                    throw new \Exception("Unknown order by: $orderBy");
            }
        }


        return $builder;
    }
}
