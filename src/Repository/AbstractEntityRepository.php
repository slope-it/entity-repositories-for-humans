<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Repository;

use Assert\Assertion;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use SlopeIt\RepositoryDemo\Query\AbstractEntityQuery;

/**
 * Base class for entity repositories.
 * @template E of object
 * @template Q of AbstractEntityQuery
 */
abstract class AbstractEntityRepository
{
    protected EntityManager $entityManager;

    /**
     * Returns the class name of the entity this repository refers to.
     *
     * @return class-string<E>
     */
    abstract public static function getEntityClassName(): string;

    /**
     * Returns the class name of the entity query this repository can handle.
     *
     * @return class-string<Q>
     */
    abstract public static function getEntityQueryClassName(): string;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns a count of all entities.
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder()
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Executes the entity query returning the count of matching results.
     *
     * @param Q $entityQuery
     */
    public function executeForCount(AbstractEntityQuery $query): int
    {
        Assertion::isInstanceOf($query, static::getEntityQueryClassName());
        Assertion::noContent($query->orderBy, 'Order by is useless when executing queries for counts.');

        $builder = $this->configureBuilderFromEntityQuery($this->createQueryBuilder(), $query);

        $rootAlias = $builder->getRootAliases()[0];

        return $builder->select("COUNT($rootAlias)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Executes the entity query returning only the first result (or null if no results found).
     *
     * @param Q $entityQuery
     * @return ?E
     */
    public function executeForFirstOrNullResult(AbstractEntityQuery $entityQuery): ?object
    {
        return $this->executeForManyResults($entityQuery, 1)[0] ?? null;
    }

    /**
     * Executes the entity query returning many results (optionally limited using $limit parameter).
     *
     * @param Q $entityQuery
     * @return E[]
     */
    public function executeForManyResults(AbstractEntityQuery $entityQuery, ?int $limit = null): array
    {
        Assertion::isInstanceOf($entityQuery, static::getEntityQueryClassName());
        Assertion::nullOrGreaterThan($limit, 0, 'If provided, limit must be greater than zero.');

        return $this->configureBuilderFromEntityQuery($this->createQueryBuilder(), $entityQuery)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Executes the entity query expecting zero or one result.
     *
     * @param Q $entityQuery
     * @return ?E
     * @throws NonUniqueResultException If more than one entity matches the query.
     */
    public function executeForOneOrNullResult(AbstractEntityQuery $entityQuery): ?object
    {
        Assertion::isInstanceOf($entityQuery, static::getEntityQueryClassName());

        return $this->configureBuilderFromEntityQuery($this->createQueryBuilder(), $entityQuery)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Executes the entity query expecting exactly one result.
     *
     * @param Q $entityQuery
     * @return E
     * @throws NonUniqueResultException If more than one entity matches the query.
     * @throws NoResultException If no entity matching the query is found.
     */
    public function executeForSingleResult(AbstractEntityQuery $entityQuery): object
    {
        Assertion::isInstanceOf($entityQuery, static::getEntityQueryClassName());

        return $this->configureBuilderFromEntityQuery($this->createQueryBuilder(), $entityQuery)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Returns all entities.
     *
     * @return E[]
     */
    public function findAll(): array
    {
        $entityQueryClass = static::getEntityQueryClassName();
        return $this->executeForManyResults(new $entityQueryClass);
    }

    /**
     * Returns a single entity by ID.
     *
     * @return E
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByID(string $id): object
    {
        return $this->createQueryBuilder()
            ->andWhere('entity.id = :id')->setParameter('id', $id)
            ->getQuery()->getSingleResult();
    }

    /**
     * Returns multiple entities by their IDs.
     *
     * @param string[] $ids
     * @return E[]
     */
    public function findByIDs(array $ids): array
    {
        return $this->createQueryBuilder()
            ->andWhere('entity.id IN (:ids)')->setParameter('ids', array_values($ids))
            ->getQuery()->getResult();
    }

    /**
     * Implement to produce a builder based on the entity query class.
     *
     * @param Q $query
     */
    abstract protected function configureBuilderFromEntityQuery(
        QueryBuilder $builder,
        AbstractEntityQuery $query
    ): QueryBuilder;

    private function createQueryBuilder(): QueryBuilder
    {
        $builder = new QueryBuilder($this->entityManager);
        return $builder->select('e')
            ->from(static::getEntityClassName(), 'e');
    }
}
