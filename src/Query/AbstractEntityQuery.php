<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Query;

abstract class AbstractEntityQuery
{
    /**
     * @param array<static::ORDER_BY_*> $orderBy
     */
    public function __construct(
        public array $orderBy = [],
    ) {}
}
