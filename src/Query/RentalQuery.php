<?php
declare(strict_types=1);

namespace SlopeIt\RepositoryDemo\Query;

use SlopeIt\RepositoryDemo\Entity\Movie;
use SlopeIt\RepositoryDemo\Entity\Customer;

class RentalQuery extends AbstractEntityQuery
{
    public const ORDER_BY_RENT_DATE_DESC = 'RENT_DATE_DESC';

    public function __construct(
        public ?Movie $movie = null,
        public ?Customer $customer = null,
        public ?\DateTimeImmutable $rentDateGreaterThan = null,
        public ?\DateTimeImmutable $rentDateLessThanOrEqualTo = null,
        public ?bool $returned = null,
        array $orderBy = [],
    ) {
        parent::__construct($orderBy);
    }
}
